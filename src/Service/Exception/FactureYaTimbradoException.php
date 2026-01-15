<?php

namespace App\FactureYaTimbradoBundle\Service\Exception;

class FactureYaTimbradoException extends \Exception
{
    private ?array $errorDetails;
    private ?string $codigoError;

    public function __construct(
        string $message = "", 
        int $code = 0, 
        ?\Throwable $previous = null, 
        ?array $errorDetails = null,
        ?string $codigoError = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->errorDetails = $errorDetails;
        $this->codigoError = $codigoError;
    }

    public function getErrorDetails(): ?array
    {
        return $this->errorDetails;
    }

    public function getCodigoError(): ?string
    {
        return $this->codigoError;
    }
}
