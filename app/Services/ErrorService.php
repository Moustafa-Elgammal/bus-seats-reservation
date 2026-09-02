<?php

namespace App\Services;

trait ErrorService
{
    /** @var list<string> */
    protected array $errors = [];

    /**
     * @return list<string>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Push a single error message, or merge in a list of them.
     *
     * @param  string|list<string>  $error
     */
    public function setError(string|array $error): void
    {
        if (is_array($error)) {
            $this->errors = array_merge($this->errors, $error);

            return;
        }

        $this->errors[] = $error;
    }
}
