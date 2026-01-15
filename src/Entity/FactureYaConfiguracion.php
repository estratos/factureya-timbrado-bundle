<?php

namespace App\FactureYaTimbradoBundle\Entity;

class FactureYaConfiguracion
{
    private ?int $id = null;
    private string $nombre;
    private string $usuario;
    private string $password;
    private string $endpoint;
    private bool $modoPruebas = true;
    private ?string $rfcEmisor = null;
    private ?string $certificadoBase64 = null;
    private ?string $llavePrivadaBase64 = null;
    private ?string $contrasenaLlave = null;
    private ?string $emailNotificaciones = null;
    private ?string $webhookUrl = null;
    private array $configuracionExtra = [];
    private ?\DateTime $fechaCreacion = null;
    private ?\DateTime $fechaActualizacion = null;
    private bool $activo = true;

    public function __construct(string $nombre, string $usuario, string $password, string $endpoint)
    {
        $this->nombre = $nombre;
        $this->usuario = $usuario;
        $this->password = $password;
        $this->endpoint = $endpoint;
        $this->fechaCreacion = new \DateTime();
        $this->fechaActualizacion = new \DateTime();
    }

    // Getters y Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): self
    {
        $this->nombre = $nombre;
        $this->fechaActualizacion = new \DateTime();
        return $this;
    }

    public function getUsuario(): string
    {
        return $this->usuario;
    }

    public function setUsuario(string $usuario): self
    {
        $this->usuario = $usuario;
        $this->fechaActualizacion = new \DateTime();
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        $this->fechaActualizacion = new \DateTime();
        return $this;
    }

    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    public function setEndpoint(string $endpoint): self
    {
        $this->endpoint = $endpoint;
        $this->fechaActualizacion = new \DateTime();
        return $this;
    }

    public function isModoPruebas(): bool
    {
        return $this->modoPruebas;
    }

    public function setModoPruebas(bool $modoPruebas): self
    {
        $this->modoPruebas = $modoPruebas;
        $this->fechaActualizacion = new \DateTime();
        return $this;
    }

    public function getRfcEmisor(): ?string
    {
        return $this->rfcEmisor;
    }

    public function setRfcEmisor(?string $rfcEmisor): self
    {
        $this->rfcEmisor = $rfcEmisor;
        $this->fechaActualizacion = new \DateTime();
        return $this;
    }

    public function getCertificadoBase64(): ?string
    {
        return $this->certificadoBase64;
    }

    public function setCertificadoBase64(?string $certificadoBase64): self
    {
        $this->certificadoBase64 = $certificadoBase64;
        $this->fechaActualizacion = new \DateTime();
        return $this;
    }

    public function getLlavePrivadaBase64(): ?string
    {
        return $this->llavePrivadaBase64;
    }

    public function setLlavePrivadaBase64(?string $llavePrivadaBase64): self
    {
        $this->llavePrivadaBase64 = $llavePrivadaBase64;
        $this->fechaActualizacion = new \DateTime();
        return $this;
    }

    public function getContrasenaLlave(): ?string
    {
        return $this->contrasenaLlave;
    }

    public function setContrasenaLlave(?string $contrasenaLlave): self
    {
        $this->contrasenaLlave = $contrasenaLlave;
        $this->fechaActualizacion = new \DateTime();
        return $this;
    }

    public function getEmailNotificaciones(): ?string
    {
        return $this->emailNotificaciones;
    }

    public function setEmailNotificaciones(?string $emailNotificaciones): self
    {
        $this->emailNotificaciones = $emailNotificaciones;
        $this->fechaActualizacion = new \DateTime();
        return $this;
    }

    public function getWebhookUrl(): ?string
    {
        return $this->webhookUrl;
    }

    public function setWebhookUrl(?string $webhookUrl): self
    {
        $this->webhookUrl = $webhookUrl;
        $this->fechaActualizacion = new \DateTime();
        return $this;
    }

    public function getConfiguracionExtra(): array
    {
        return $this->configuracionExtra;
    }

    public function setConfiguracionExtra(array $configuracionExtra): self
    {
        $this->configuracionExtra = $configuracionExtra;
        $this->fechaActualizacion = new \DateTime();
        return $this;
    }

    public function addConfiguracionExtra(string $key, $value): self
    {
        $this->configuracionExtra[$key] = $value;
        $this->fechaActualizacion = new \DateTime();
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

    public function isActivo(): bool
    {
        return $this->activo;
    }

    public function setActivo(bool $activo): self
    {
        $this->activo = $activo;
        $this->fechaActualizacion = new \DateTime();
        return $this;
    }

    public function getEndpointCompleto(): string
    {
        return $this->endpoint . ($this->modoPruebas ? '?wsdl' : '');
    }

    public function toArray(): array
    {
        return [
            'nombre' => $this->nombre,
            'usuario' => $this->usuario,
            'endpoint' => $this->endpoint,
            'modo_pruebas' => $this->modoPruebas,
            'rfc_emisor' => $this->rfcEmisor,
            'email_notificaciones' => $this->emailNotificaciones,
            'webhook_url' => $this->webhookUrl,
            'activo' => $this->activo,
        ];
    }
}
