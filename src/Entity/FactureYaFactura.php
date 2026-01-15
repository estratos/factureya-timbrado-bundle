<?php

namespace App\FactureYaTimbradoBundle\Entity;

class FactureYaFactura
{
    private ?int $id = null;
    private ?string $uuid = null;
    private string $referencia;
    private string $xmlOriginal;
    private ?string $xmlTimbrado = null;
    private ?\DateTime $fechaTimbrado = null;
    private ?string $estatus = 'PENDIENTE';
    private ?string $qrCode = null;
    private ?string $cadenaOriginal = null;
    private ?string $selloSAT = null;
    private ?string $selloCFD = null;
    private ?string $noCertificadoSAT = null;
    private ?string $pdfBase64 = null;
    private ?string $emailCliente = null;
    private bool $enviarEmail = false;
    private ?string $rfcEmisor = null;
    private ?string $rfcReceptor = null;
    private ?float $total = null;
    private ?\DateTime $fechaCreacion = null;
    private ?\DateTime $fechaActualizacion = null;
    private array $metadata = [];
    private ?string $codigoError = null;
    private ?string $mensajeError = null;

    public function __construct(string $xmlOriginal, string $referencia = '')
    {
        $this->xmlOriginal = $xmlOriginal;
        $this->referencia = $referencia ?: 'FACTUREYA_' . date('YmdHis') . '_' . uniqid();
        $this->fechaCreacion = new \DateTime();
        $this->fechaActualizacion = new \DateTime();
    }

    // Métodos getters y setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;
        $this->fechaActualizacion = new \DateTime();
        return $this;
    }

    public function getReferencia(): string
    {
        return $this->referencia;
    }

    public function getXmlOriginal(): string
    {
        return $this->xmlOriginal;
    }

    public function getXmlTimbrado(): ?string
    {
        return $this->xmlTimbrado;
    }

    public function setXmlTimbrado(string $xmlTimbrado): self
    {
        $this->xmlTimbrado = $xmlTimbrado;
        $this->fechaActualizacion = new \DateTime();
        return $this;
    }

    public function getFechaTimbrado(): ?\DateTime
    {
        return $this->fechaTimbrado;
    }

    public function setFechaTimbrado(\DateTime $fechaTimbrado): self
    {
        $this->fechaTimbrado = $fechaTimbrado;
        $this->fechaActualizacion = new \DateTime();
        return $this;
    }

    public function getEstatus(): ?string
    {
        return $this->estatus;
    }

    public function setEstatus(string $estatus): self
    {
        $this->estatus = $estatus;
        $this->fechaActualizacion = new \DateTime();
        return $this;
    }

    public function getQrCode(): ?string
    {
        return $this->qrCode;
    }

    public function setQrCode(string $qrCode): self
    {
        $this->qrCode = $qrCode;
        return $this;
    }

    public function getCadenaOriginal(): ?string
    {
        return $this->cadenaOriginal;
    }

    public function setCadenaOriginal(string $cadenaOriginal): self
    {
        $this->cadenaOriginal = $cadenaOriginal;
        return $this;
    }

    public function getSelloSAT(): ?string
    {
        return $this->selloSAT;
    }

    public function setSelloSAT(string $selloSAT): self
    {
        $this->selloSAT = $selloSAT;
        return $this;
    }

    public function getSelloCFD(): ?string
    {
        return $this->selloCFD;
    }

    public function setSelloCFD(string $selloCFD): self
    {
        $this->selloCFD = $selloCFD;
        return $this;
    }

    public function getNoCertificadoSAT(): ?string
    {
        return $this->noCertificadoSAT;
    }

    public function setNoCertificadoSAT(string $noCertificadoSAT): self
    {
        $this->noCertificadoSAT = $noCertificadoSAT;
        return $this;
    }

    public function getPdfBase64(): ?string
    {
        return $this->pdfBase64;
    }

    public function setPdfBase64(string $pdfBase64): self
    {
        $this->pdfBase64 = $pdfBase64;
        return $this;
    }

    public function getEmailCliente(): ?string
    {
        return $this->emailCliente;
    }

    public function setEmailCliente(?string $emailCliente): self
    {
        $this->emailCliente = $emailCliente;
        return $this;
    }

    public function getEnviarEmail(): bool
    {
        return $this->enviarEmail;
    }

    public function setEnviarEmail(bool $enviarEmail): self
    {
        $this->enviarEmail = $enviarEmail;
        return $this;
    }

    public function getRfcEmisor(): ?string
    {
        return $this->rfcEmisor;
    }

    public function setRfcEmisor(string $rfcEmisor): self
    {
        $this->rfcEmisor = $rfcEmisor;
        return $this;
    }

    public function getRfcReceptor(): ?string
    {
        return $this->rfcReceptor;
    }

    public function setRfcReceptor(string $rfcReceptor): self
    {
        $this->rfcReceptor = $rfcReceptor;
        return $this;
    }

    public function getTotal(): ?float
    {
        return $this->total;
    }

    public function setTotal(float $total): self
    {
        $this->total = $total;
        return $this;
    }

    public function getFechaCreacion(): ?\DateTime
    {
        return $this->fechaCreacion;
    }

    public function getFechaActualizacion(): ?\DateTime
    {
        return $this->fechaActualizacion;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function setMetadata(array $metadata): self
    {
        $this->metadata = $metadata;
        return $this;
    }

    public function addMetadata(string $key, $value): self
    {
        $this->metadata[$key] = $value;
        return $this;
    }

    public function getCodigoError(): ?string
    {
        return $this->codigoError;
    }

    public function setCodigoError(?string $codigoError): self
    {
        $this->codigoError = $codigoError;
        return $this;
    }

    public function getMensajeError(): ?string
    {
        return $this->mensajeError;
    }

    public function setMensajeError(?string $mensajeError): self
    {
        $this->mensajeError = $mensajeError;
        return $this;
    }

    public function isTimbrada(): bool
    {
        return $this->uuid !== null && $this->xmlTimbrado !== null;
    }

    public function isCancelable(): bool
    {
        return $this->isTimbrada() && $this->estatus === 'VIGENTE';
    }

    public function isCancelada(): bool
    {
        return $this->estatus === 'CANCELADA';
    }

    public function marcarComoError(string $codigoError, string $mensajeError): self
    {
        $this->estatus = 'ERROR';
        $this->codigoError = $codigoError;
        $this->mensajeError = $mensajeError;
        $this->fechaActualizacion = new \DateTime();
        
        return $this;
    }

    public function extraerDatosDeXml(): self
    {
        try {
            $dom = new \DOMDocument();
            $dom->loadXML($this->xmlOriginal);
            
            // Extraer RFC Emisor
            $emisor = $dom->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Emisor')->item(0);
            if ($emisor && $emisor->hasAttribute('Rfc')) {
                $this->rfcEmisor = $emisor->getAttribute('Rfc');
            }
            
            // Extraer RFC Receptor
            $receptor = $dom->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Receptor')->item(0);
            if ($receptor && $receptor->hasAttribute('Rfc')) {
                $this->rfcReceptor = $receptor->getAttribute('Rfc');
            }
            
            // Extraer Total
            $comprobante = $dom->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Comprobante')->item(0);
            if ($comprobante && $comprobante->hasAttribute('Total')) {
                $this->total = (float) $comprobante->getAttribute('Total');
            }
            
        } catch (\Exception $e) {
            // Silenciar error de extracción
        }
        
        return $this;
    }
}
