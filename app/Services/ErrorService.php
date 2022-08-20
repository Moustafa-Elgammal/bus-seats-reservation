<?php

namespace App\Services;

trait ErrorService
{
    protected $errors = [];

    /** all errors
     * @return array
     */
    public function getErrors() : array{
        return $this->errors;
    }

    /** push error or list of error
     * @param $error
     * @return void
     */
    public function setError($error): void{
        if (is_array($error))
            $this->errors = array_merge($this->errors, $error);

        $this->errors [] = $error;
    }

}
