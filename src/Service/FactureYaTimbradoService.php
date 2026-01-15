<?php

namespace App\FactureYaTimbradoBundle\Service;

use App\FactureYaTimbradoBundle\Service\Exception\FactureYaTimbradoException;
use App\FactureYaTimbradoBundle\Service\Exception\FactureYaSoapException;
use App\FactureYaTimbradoBundle\Entity\FactureYaFactura;

class FactureYaTimbradoService
{
    private FactureYaSoapClient $soapClient;
    private FactureYaXmlValidator $xmlValidator;
    private FactureYaResponseHandler $responseHandler;
    private string $usuario;
    private string $password;
    private string $endpoint;
    private bool $modoPruebas;
    private array $configuracion;

    public function __construct(
        FactureYaSoapClient $soapClient,
        FactureYaXmlValidator $xmlValidator,
        FactureYaResponseHandler $responseHandler,
        array $configuracion
    ) {
        $this->soapClient = $soapClient;
        $this->xmlValidator = $xmlValidator;
        $this->responseHandler = $responseHandler;
        $this->usuario = $configuracion['usuario'];
        $this->password = $configuracion['password'];
        $this->endpoint = $configuracion['endpoint'];
        $this->modoPruebas = $configuracion['modo_pruebas'];
        $this->configuracion = $configuracion;
    }

    /**
     * Timbra una factura CFDI usando FactureYa
     */
    public function timbrar(string $xml, string $referencia = '', array $opciones = []): array
    {
        try {
            // Validar XML
            $this->xmlValidator->validateForFactureYa($xml);

            // Preparar parámetros específicos de FactureYa
            $params = [
                'usuario' => $this->usuario,
                'password' => $this->password,
                'cadenaXML' => $this->prepararXmlParaFactureYa($xml, $opciones),
                'referencia' => $referencia ?: $this->generarReferencia()
            ];

            // Opciones adicionales de FactureYa
            if (isset($opciones['email'])) {
                $params['email'] = $opciones['email'];
            }
            
            if (isset($opciones['enviar_email'])) {
                $params['enviarEmail'] = $opciones['enviar_email'];
            }

            // Realizar llamada SOAP
            $response = $this->soapClient->timbrarCFDI($params);

            // Procesar respuesta específica de FactureYa
            return $this->responseHandler->handleTimbrado($response);

        } catch (\SoapFault $e) {
            throw new FactureYaSoapException(
                'Error SOAP FactureYa: ' . $e->getMessage(),
                0,
                $e,
                $this->soapClient->getLastRequest(),
                $this->soapClient->getLastResponse()
            );
        } catch (\Exception $e) {
            throw new FactureYaTimbradoException('Error en timbrado FactureYa: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Timbra una factura desde entidad FactureYaFactura
     */
    public function timbrarFactura(FactureYaFactura $factura): array
    {
        $xml = $factura->getXmlOriginal();
        $referencia = $factura->getReferencia();
        $opciones = [
            'email' => $factura->getEmailCliente(),
            'enviar_email' => $factura->getEnviarEmail()
        ];

        $resultado = $this->timbrar($xml, $referencia, $opciones);
        
        // Actualizar entidad con resultado
        $factura->setUuid($resultado['uuid']);
        $factura->setXmlTimbrado($resultado['xmlTimbrado']);
        $factura->setFechaTimbrado(new \DateTime($resultado['fechaTimbrado']));
        $factura->setQrCode($resultado['qrCode']);
        $factura->setCadenaOriginal($resultado['cadenaOriginal']);
        $factura->setEstatus('TIMBRADO');

        return $resultado;
    }

    /**
     * Cancela un timbre usando FactureYa
     */
    public function cancelar(string $uuid, string $motivo = '02', string $folioSustituto = '', string $rfc = ''): array
    {
        try {
            $params = [
                'usuario' => $this->usuario,
                'password' => $this->password,
                'uuid' => $uuid,
                'motivo' => $motivo,
                'folioSustituto' => $folioSustituto,
                'rfcEmisor' => $rfc ?: $this->configuracion['rfc_emisor']
            ];

            $response = $this->soapClient->cancelarCFDI($params);

            return $this->responseHandler->handleCancelacion($response);

        } catch (\SoapFault $e) {
            throw new FactureYaSoapException(
                'Error SOAP en cancelación FactureYa: ' . $e->getMessage(),
                0,
                $e,
                $this->soapClient->getLastRequest(),
                $this->soapClient->getLastResponse()
            );
        }
    }

    /**
     * Consulta el estado de un timbre en FactureYa
     */
    public function consultar(string $uuid, string $rfc = ''): array
    {
        try {
            $params = [
                'usuario' => $this->usuario,
                'password' => $this->password,
                'uuid' => $uuid,
                'rfcEmisor' => $rfc ?: $this->configuracion['rfc_emisor']
            ];

            $response = $this->soapClient->consultarCFDI($params);

            return $this->responseHandler->handleConsulta($response);

        } catch (\SoapFault $e) {
            throw new FactureYaSoapException(
                'Error SOAP en consulta FactureYa: ' . $e->getMessage(),
                0,
                $e,
                $this->soapClient->getLastRequest(),
                $this->soapClient->getLastResponse()
            );
        }
    }

    /**
     * Obtiene el PDF de una factura timbrada
     */
    public function obtenerPdf(string $uuid, string $formato = 'A4'): array
    {
        try {
            $params = [
                'usuario' => $this->usuario,
                'password' => $this->password,
                'uuid' => $uuid,
                'formato' => $formato
            ];

            $response = $this->soapClient->obtenerPDF($params);

            return $this->responseHandler->handlePdf($response);

        } catch (\SoapFault $e) {
            throw new FactureYaSoapException(
                'Error SOAP al obtener PDF FactureYa: ' . $e->getMessage(),
                0,
                $e,
                $this->soapClient->getLastRequest(),
                $this->soapClient->getLastResponse()
            );
        }
    }

    /**
     * Consulta el saldo disponible en FactureYa
     */
    public function consultarSaldo(): array
    {
        try {
            $params = [
                'usuario' => $this->usuario,
                'password' => $this->password
            ];

            $response = $this->soapClient->consultarSaldo($params);

            return $this->responseHandler->handleSaldo($response);

        } catch (\SoapFault $e) {
            throw new FactureYaSoapException(
                'Error SOAP al consultar saldo FactureYa: ' . $e->getMessage(),
                0,
                $e,
                $this->soapClient->getLastRequest(),
                $this->soapClient->getLastResponse()
            );
        }
    }

    private function prepararXmlParaFactureYa(string $xml, array $opciones = []): string
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;
        $dom->loadXML($xml);

        // Asegurar encoding correcto
        $dom->encoding = 'UTF-8';

        // Agregar atributos específicos si es necesario
        $comprobante = $dom->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Comprobante')->item(0);
        
        if ($comprobante) {
            // Agregar namespace para complementos si no existe
            if (!$comprobante->hasAttribute('xmlns:cfdi')) {
                $comprobante->setAttribute('xmlns:cfdi', 'http://www.sat.gob.mx/cfd/3');
            }
            
            // Agregar schema location
            $comprobante->setAttribute('xsi:schemaLocation', 
                'http://www.sat.gob.mx/cfd/3 http://www.sat.gob.mx/sitio_internet/cfd/3/cfdv33.xsd');
        }

        return $dom->saveXML();
    }

    private function generarReferencia(): string
    {
        return 'FACTUREYA_' . date('YmdHis') . '_' . uniqid();
    }

    public function isModoPruebas(): bool
    {
        return $this->modoPruebas;
    }

    public function getConfiguracion(): array
    {
        return $this->configuracion;
    }
}
