<?php

namespace App\FactureYaTimbradoBundle\Service\Exception;

class FactureYaValidationException extends FactureYaTimbradoException
{
    private array $validationErrors;

    public function __construct(string $message = "", array $validationErrors = [])
    {
        parent::__construct($message);
        $this->validationErrors = $validationErrors;
    }

    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }

    public function getValidationErrorsAsString(): string
    {
        return implode('; ', $this->validationErrors);
    }
}
