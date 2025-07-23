<?php

namespace Faktore\FeJsonApiUtilities\Utility;

class JsonResponseUtility
{
    protected array $data = [];

    protected array $errors = [];

    protected bool $success = false;

    public function __construct()
    {
        $this->initialize();
    }

    public function initialize(): void
    {
        $this->data = [];
        $this->errors = [];
        $this->success = false;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function assignData(string $key, mixed $data): void
    {
        $this->data[$key] = $data;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function setErrors(array $errors): void
    {
        $this->errors = $errors;
    }

    public function addError(string $error): void {
        $this->errors[] = $error;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function setSuccess(bool $success): void
    {
        $this->success = $success;
    }

    public function getOutput(): array
    {
        return [
            'data' => $this->data ?? [],
            'errors' => $this->errors ?? [],
            'status' => $this->success ?? true
        ];
    }

    public function getEncodedOutput(): false|string
    {
        return json_encode($this->getOutput());
    }
}