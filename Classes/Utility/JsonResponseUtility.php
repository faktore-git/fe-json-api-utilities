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

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param string $key
     * @param mixed $data
     */
    public function assignData(string $key, mixed $data): void
    {
        $this->data[$key] = $data;
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @param array $errors
     */
    public function setErrors(array $errors): void
    {
        $this->errors = $errors;
    }

    public function addError(string $error): void {
        $this->errors[] = $error;
    }

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * @param bool $success
     */
    public function setSuccess(bool $success): void
    {
        $this->success = $success;
    }

    /**
     * @return false|string
     */
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