<?php

namespace Faktore\FeJsonApiUtilities\Utility;

class JsonRequestUtility
{

    protected array $errorMessages = [];

    protected array $decodedData = [];

    protected bool $dataValid = false;

    public function __construct()
    {
        $this->initialize();
    }

    public function initialize(): void
    {
        $this->errorMessages = [];
        $this->dataValid = false;
        $this->decodedData = [];
    }

    /**
     * Gets the JSON payload of a request
     */
    public function parseJsonBody(): static
    {
        $rawRequest = @file_get_contents('php://input');

        if ($rawRequest === FALSE || $rawRequest === '') {
            $this->errorMessages[] = 'Error getting JSON Request Payload';
            return $this;
        }

        try {
            $decodedData = json_decode($rawRequest, true);
            if (!$decodedData) {
                $this->errorMessages[] = 'Could not parse JSON: Json Error ' . json_last_error();
                return $this;
            }
            $this->decodedData = $decodedData;
        } catch (\Exception $e) {
            $this->errorMessages[] = $e->getMessage();
        }

        $this->dataValid = true;
        return $this;
    }

    public function getErrorMessages(): array
    {
        return $this->errorMessages;
    }

    public function setErrorMessages(array $errorMessages): void
    {
        $this->errorMessages = $errorMessages;
    }

    public function getDecodedData(): array
    {
        return $this->decodedData;
    }

    public function setDecodedData(array $decodedData): void
    {
        $this->decodedData = $decodedData;
    }

    public function isDataValid(): bool
    {
        return $this->dataValid;
    }

    public function setDataValid(bool $dataValid): void
    {
        $this->dataValid = $dataValid;
    }
}