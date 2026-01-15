<?php

namespace App\FactureYaTimbradoBundle\Service;

use App\FactureYaTimbradoBundle\Service\Exception\FactureYaValidationException;

class FactureYaXmlValidator
{
    private array $errores = [];

    public function validateForFactureYa(string $xml): bool
    {
        $this->errores = [];
        
        libxml_use_internal_errors(true);
        
        $dom = new \DOMDocument();
        
        if (!$dom->loadXML($xml)) {
            $errors = libxml_get_errors();
            libxml_clear_errors();
            $this->errores = array_map(fn($e) => $this->formatLibXmlError($e), $errors);
            throw new FactureYaValidationException('XML inválido', $this->errores);
        }

        // Validaciones específicas para FactureYa
        $this->validateComprobante($dom);
        $this->validateEmisor($dom);
        $this->validateReceptor($dom);
        $this->validateConceptos($dom);
        $this->validateImpuestos($dom);

        if (!empty($this->errores)) {
            throw new FactureYaValidationException('Validación XML falló', $this->errores);
        }

        return true;
    }

    private function validateComprobante(\DOMDocument $dom): void
    {
        $comprobante = $dom->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Comprobante')->item(0);
        
        if (!$comprobante) {
            $this->errores[] = 'No se encontró el nodo Comprobante';
            return;
        }

        // Atributos requeridos por SAT
        $requiredAttrs = [
            'Version' => '3.3',
            'Serie' => null,
            'Folio' => null,
            'Fecha' => null,
            'FormaPago' => null,
            'NoCertificado' => null,
            'SubTotal' => null,
            'Moneda' => null,
            'Total' => null,
            'TipoDeComprobante' => null,
            'LugarExpedicion' => null,
        ];

        foreach ($requiredAttrs as $attr => $expectedValue) {
            if (!$comprobante->hasAttribute($attr)) {
                $this->errores[] = "Falta atributo requerido: $attr";
            } elseif ($expectedValue && $comprobante->getAttribute($attr) != $expectedValue) {
                $this->errores[] = "Atributo $attr debe ser '$expectedValue'";
            }
        }

        // Validar fecha formato SAT
        $fecha = $comprobante->getAttribute('Fecha');
        if ($fecha && !preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}$/', $fecha)) {
            $this->errores[] = 'Formato de fecha inválido. Debe ser YYYY-MM-DDThh:mm:ss';
        }

        // Validar montos
        $subTotal = (float) $comprobante->getAttribute('SubTotal');
        $total = (float) $comprobante->getAttribute('Total');
        
        if ($subTotal <= 0) {
            $this->errores[] = 'SubTotal debe ser mayor a 0';
        }
        
        if ($total <= 0) {
            $this->errores[] = 'Total debe ser mayor a 0';
        }
    }

    private function validateEmisor(\DOMDocument $dom): void
    {
        $emisor = $dom->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Emisor')->item(0);
        
        if (!$emisor) {
            $this->errores[] = 'No se encontró el nodo Emisor';
            return;
        }

        $requiredAttrs = ['Rfc', 'Nombre', 'RegimenFiscal'];
        
        foreach ($requiredAttrs as $attr) {
            if (!$emisor->hasAttribute($attr) || empty($emisor->getAttribute($attr))) {
                $this->errores[] = "Emisor: Falta atributo $attr";
            }
        }

        // Validar RFC
        $rfc = $emisor->getAttribute('Rfc');
        if (!preg_match('/^[A-Z&Ñ]{3,4}[0-9]{6}[A-Z0-9]{3}$/', $rfc)) {
            $this->errores[] = 'RFC del emisor inválido';
        }

        // Validar régimen fiscal
        $regimen = $emisor->getAttribute('RegimenFiscal');
        $regimenesValidos = ['601', '603', '605', '606', '607', '608', '609', '610', '611', '612', '614', '615', '616', '620', '621', '622', '623', '624', '625', '626'];
        
        if (!in_array($regimen, $regimenesValidos)) {
            $this->errores[] = 'Régimen fiscal del emisor inválido';
        }
    }

    private function validateReceptor(\DOMDocument $dom): void
    {
        $receptor = $dom->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Receptor')->item(0);
        
        if (!$receptor) {
            $this->errores[] = 'No se encontró el nodo Receptor';
            return;
        }

        $requiredAttrs = ['Rfc', 'Nombre', 'UsoCFDI'];
        
        foreach ($requiredAttrs as $attr) {
            if (!$receptor->hasAttribute($attr) || empty($receptor->getAttribute($attr))) {
                $this->errores[] = "Receptor: Falta atributo $attr";
            }
        }

        // Validar RFC (puede ser XAXX010101000 para extranjeros)
        $rfc = $receptor->getAttribute('Rfc');
        if (!preg_match('/^[A-Z&Ñ]{3,4}[0-9]{6}[A-Z0-9]{3}$/', $rfc) && $rfc !== 'XAXX010101000' && $rfc !== 'XEXX010101000') {
            $this->errores[] = 'RFC del receptor inválido';
        }

        // Validar uso CFDI
        $usoCFDI = $receptor->getAttribute('UsoCFDI');
        $usosValidos = ['G01', 'G02', 'G03', 'I01', 'I02', 'I03', 'I04', 'I05', 'I06', 'I07', 'I08', 'D01', 'D02', 'D03', 'D04', 'D05', 'D06', 'D07', 'D08', 'D09', 'D10', 'P01', 'S01'];
        
        if (!in_array($usoCFDI, $usosValidos)) {
            $this->errores[] = 'Uso CFDI inválido';
        }
    }

    private function validateConceptos(\DOMDocument $dom): void
    {
        $conceptos = $dom->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Conceptos')->item(0);
        
        if (!$conceptos) {
            $this->errores[] = 'No se encontró el nodo Conceptos';
            return;
        }

        $conceptosList = $conceptos->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Concepto');
        
        if ($conceptosList->length === 0) {
            $this->errores[] = 'Debe haber al menos un concepto';
            return;
        }

        foreach ($conceptosList as $index => $concepto) {
            $this->validateConcepto($concepto, $index + 1);
        }
    }

    private function validateConcepto(\DOMElement $concepto, int $numero): void
    {
        $requiredAttrs = ['ClaveProdServ', 'Cantidad', 'ClaveUnidad', 'Descripcion', 'ValorUnitario', 'Importe'];
        
        foreach ($requiredAttrs as $attr) {
            if (!$concepto->hasAttribute($attr) || empty($concepto->getAttribute($attr))) {
                $this->errores[] = "Concepto $numero: Falta atributo $attr";
            }
        }

        // Validar valores numéricos
        $cantidad = (float) $concepto->getAttribute('Cantidad');
        $valorUnitario = (float) $concepto->getAttribute('ValorUnitario');
        $importe = (float) $concepto->getAttribute('Importe');
        
        if ($cantidad <= 0) {
            $this->errores[] = "Concepto $numero: Cantidad debe ser mayor a 0";
        }
        
        if ($valorUnitario <= 0) {
            $this->errores[] = "Concepto $numero: ValorUnitario debe ser mayor a 0";
        }
        
        // Validar cálculo de importe
        $importeCalculado = round($cantidad * $valorUnitario, 2);
        if (abs($importe - $importeCalculado) > 0.01) {
            $this->errores[] = "Concepto $numero: Importe no coincide con Cantidad * ValorUnitario";
        }
    }

    private function validateImpuestos(\DOMDocument $dom): void
    {
        $impuestos = $dom->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Impuestos')->item(0);
        
        if (!$impuestos) {
            // No hay impuestos, es válido
            return;
        }

        // Validar totales
        if ($impuestos->hasAttribute('TotalImpuestosRetenidos')) {
            $totalRetenidos = (float) $impuestos->getAttribute('TotalImpuestosRetenidos');
            if ($totalRetenidos < 0) {
                $this->errores[] = 'TotalImpuestosRetenidos no puede ser negativo';
            }
        }

        if ($impuestos->hasAttribute('TotalImpuestosTrasladados')) {
            $totalTrasladados = (float) $impuestos->getAttribute('TotalImpuestosTrasladados');
            if ($totalTrasladados < 0) {
                $this->errores[] = 'TotalImpuestosTrasladados no puede ser negativo';
            }
        }
    }

    private function formatLibXmlError(\LibXMLError $error): string
    {
        $level = match($error->level) {
            LIBXML_ERR_WARNING => 'Warning',
            LIBXML_ERR_ERROR => 'Error',
            LIBXML_ERR_FATAL => 'Fatal Error',
            default => 'Unknown',
        };

        return sprintf('%s %d: %s (Line: %d, Column: %d)',
            $level,
            $error->code,
            trim($error->message),
            $error->line,
            $error->column
        );
    }

    public function getErrores(): array
    {
        return $this->errores;
    }

    public function prettifyXml(string $xml): string
    {
        $dom = new \DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml);
        return $dom->saveXML();
    }

    public function minifyXml(string $xml): string
    {
        $dom = new \DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;
        $dom->loadXML($xml);
        return $dom->saveXML();
    }
}
