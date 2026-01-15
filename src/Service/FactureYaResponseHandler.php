<?php

namespace App\FactureYaTimbradoBundle\Service;

use App\FactureYaTimbradoBundle\Service\Exception\FactureYaTimbradoException;

class FactureYaResponseHandler
{
    public function handleTimbrado(object $response): array
    {
        if (!isset($response->TimbrarCFDIResult)) {
            throw new FactureYaTimbradoException('Respuesta SOAP inválida de FactureYa');
        }

        $result = $response->TimbrarCFDIResult;
        
        // Verificar errores específicos de FactureYa
        if (isset($result->CodigoError) && $result->CodigoError != 0) {
            throw new FactureYaTimbradoException(
                'FactureYa Error ' . $result->CodigoError . ': ' . ($result->MensajeError ?? 'Error desconocido')
            );
        }

        if (isset($result->error) && !empty($result->error)) {
            throw new FactureYaTimbradoException('Error FactureYa: ' . $result->error);
        }

        // Estructura de respuesta específica de FactureYa
        return [
            'success' => true,
            'uuid' => $result->UUID ?? $result->uuid ?? null,
            'xmlTimbrado' => $result->XMLTimbrado ?? $result->xml ?? null,
            'fechaTimbrado' => $result->FechaTimbrado ?? $result->fechaTimbrado ?? null,
            'qrCode' => $result->QRCode ?? $result->qrCode ?? null,
            'cadenaOriginal' => $result->CadenaOriginal ?? $result->cadenaOriginal ?? null,
            'selloSAT' => $result->SelloSAT ?? $result->selloSAT ?? null,
            'selloCFD' => $result->SelloCFD ?? $result->selloCFD ?? null,
            'noCertificadoSAT' => $result->NoCertificadoSAT ?? $result->noCertificadoSAT ?? null,
            'noCertificadoCFD' => $result->NoCertificadoCFD ?? $result->noCertificadoCFD ?? null,
            'referencia' => $result->Referencia ?? $result->referencia ?? null,
            'folioFiscal' => $result->FolioFiscal ?? $result->folioFiscal ?? null,
            'serie' => $result->Serie ?? $result->serie ?? null,
            'folio' => $result->Folio ?? $result->folio ?? null,
            'mensaje' => $result->Mensaje ?? $result->mensaje ?? null,
            'codigoError' => $result->CodigoError ?? 0,
            'mensajeError' => $result->MensajeError ?? null,
            'rawResponse' => $result,
        ];
    }

    public function handleCancelacion(object $response): array
    {
        if (!isset($response->CancelarCFDIResult)) {
            throw new FactureYaTimbradoException('Respuesta SOAP inválida para cancelación');
        }

        $result = $response->CancelarCFDIResult;

        if (isset($result->CodigoError) && $result->CodigoError != 0) {
            throw new FactureYaTimbradoException(
                'FactureYa Cancelación Error ' . $result->CodigoError . ': ' . 
                ($result->MensajeError ?? 'Error desconocido')
            );
        }

        return [
            'success' => true,
            'acuse' => $result->Acuse ?? $result->acuse ?? null,
            'fechaCancelacion' => $result->FechaCancelacion ?? $result->fechaCancelacion ?? null,
            'estatus' => $result->Estatus ?? $result->estatus ?? null,
            'uuid' => $result->UUID ?? $result->uuid ?? null,
            'mensaje' => $result->Mensaje ?? $result->mensaje ?? null,
            'codigoError' => $result->CodigoError ?? 0,
            'mensajeError' => $result->MensajeError ?? null,
            'rawResponse' => $result,
        ];
    }

    public function handleConsulta(object $response): array
    {
        if (!isset($response->ConsultarCFDIResult)) {
            throw new FactureYaTimbradoException('Respuesta SOAP inválida para consulta');
        }

        $result = $response->ConsultarCFDIResult;

        if (isset($result->CodigoError) && $result->CodigoError != 0) {
            throw new FactureYaTimbradoException(
                'FactureYa Consulta Error ' . $result->CodigoError . ': ' . 
                ($result->MensajeError ?? 'Error desconocido')
            );
        }

        return [
            'success' => true,
            'uuid' => $result->UUID ?? $result->uuid ?? null,
            'estatus' => $result->Estatus ?? $result->estatus ?? null,
            'esCancelable' => $result->EsCancelable ?? $result->esCancelable ?? null,
            'estatusCancelacion' => $result->EstatusCancelacion ?? $result->estatusCancelacion ?? null,
            'fechaTimbrado' => $result->FechaTimbrado ?? $result->fechaTimbrado ?? null,
            'fechaCancelacion' => $result->FechaCancelacion ?? $result->fechaCancelacion ?? null,
            'rfcEmisor' => $result->RFCEmisor ?? $result->rfcEmisor ?? null,
            'rfcReceptor' => $result->RFCReceptor ?? $result->rfcReceptor ?? null,
            'total' => $result->Total ?? $result->total ?? null,
            'mensaje' => $result->Mensaje ?? $result->mensaje ?? null,
            'codigoError' => $result->CodigoError ?? 0,
            'mensajeError' => $result->MensajeError ?? null,
            'rawResponse' => $result,
        ];
    }

    public function handlePdf(object $response): array
    {
        if (!isset($response->ObtenerPDFResult)) {
            throw new FactureYaTimbradoException('Respuesta SOAP inválida para PDF');
        }

        $result = $response->ObtenerPDFResult;

        if (isset($result->CodigoError) && $result->CodigoError != 0) {
            throw new FactureYaTimbradoException(
                'FactureYa PDF Error ' . $result->CodigoError . ': ' . 
                ($result->MensajeError ?? 'Error desconocido')
            );
        }

        return [
            'success' => true,
            'pdfBase64' => $result->PDF ?? $result->pdf ?? null,
            'uuid' => $result->UUID ?? $result->uuid ?? null,
            'nombreArchivo' => $result->NombreArchivo ?? $result->nombreArchivo ?? null,
            'tipo' => $result->Tipo ?? $result->tipo ?? null,
            'tamano' => $result->Tamano ?? $result->tamano ?? null,
            'mensaje' => $result->Mensaje ?? $result->mensaje ?? null,
            'codigoError' => $result->CodigoError ?? 0,
            'mensajeError' => $result->MensajeError ?? null,
            'rawResponse' => $result,
        ];
    }

    public function handleSaldo(object $response): array
    {
        if (!isset($response->ConsultarSaldoResult)) {
            throw new FactureYaTimbradoException('Respuesta SOAP inválida para saldo');
        }

        $result = $response->ConsultarSaldoResult;

        if (isset($result->CodigoError) && $result->CodigoError != 0) {
            throw new FactureYaTimbradoException(
                'FactureYa Saldo Error ' . $result->CodigoError . ': ' . 
                ($result->MensajeError ?? 'Error desconocido')
            );
        }

        return [
            'success' => true,
            'saldo' => $result->Saldo ?? $result->saldo ?? 0,
            'limite' => $result->Limite ?? $result->limite ?? 0,
            'creditosUsados' => $result->CreditosUsados ?? $result->creditosUsados ?? 0,
            'creditosDisponibles' => $result->CreditosDisponibles ?? $result->creditosDisponibles ?? 0,
            'fechaConsulta' => $result->FechaConsulta ?? $result->fechaConsulta ?? null,
            'mensaje' => $result->Mensaje ?? $result->mensaje ?? null,
            'codigoError' => $result->CodigoError ?? 0,
            'mensajeError' => $result->MensajeError ?? null,
            'rawResponse' => $result,
        ];
    }

    public function extractUuidFromXml(string $xml): ?string
    {
        try {
            $dom = new \DOMDocument();
            $dom->loadXML($xml);
            
            $xpath = new \DOMXPath($dom);
            $xpath->registerNamespace('tfd', 'http://www.sat.gob.mx/TimbreFiscalDigital');
            
            $nodes = $xpath->query('//tfd:TimbreFiscalDigital/@UUID');
            
            return $nodes->length > 0 ? $nodes->item(0)->nodeValue : null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
