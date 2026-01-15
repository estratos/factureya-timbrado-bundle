<?php

namespace App\FactureYaTimbradoBundle\Service\Exception;

class FactureYaSoapException extends FactureYaTimbradoException
{
    private ?string $soapRequest;
    private ?string $soapResponse;

    public function __construct(
        string $message = "", 
        int $code = 0, 
        ?\Throwable $previous = null,
        ?string $soapRequest = null,
        ?string $soapResponse = null,
        ?array $errorDetails = null
    ) {
        parent::__construct($message, $code, $previous, $errorDetails);
        $this->soapRequest = $soapRequest;
        $this->soapResponse = $soapResponse;
    }

    public function getSoapRequest(): ?string
    {
        return $this->soapRequest;
    }

    public function getSoapResponse(): ?string
    {
        return $this->soapResponse;
    }

    public function getSoapRequestPretty(): ?string
    {
        if (!$this->soapRequest) {
            return null;
        }

        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($this->soapRequest);
        return $dom->saveXML();
    }

    public function getSoapResponsePretty(): ?string
    {
        if (!$this->soapResponse) {
            return null;
        }

        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($this->soapResponse);
        return $dom->saveXML();
    }
}
