<?php

namespace App\Validation;

use App\Exceptions\FormValidationException;

class ArticleFormValidator {

    private array $data;
    private array $rules;
    private array $errors = [];

    public function __construct(array $data, array $rules=[]) {
        $this->data = $data;
        $this->rules = $rules;
    }

    public function passes() {
        foreach ($this->rules as $key => $rules) {
            foreach ($rules as $rule) {
                [$name, $attribute] = explode(":", $rule);

                $ruleName = 'validate' . ucfirst($name); //validateRequired;
                $this->{$ruleName}($key, $attribute);
            }
        }

        if (count($this->errors) > 0) {
            throw new FormValidationException();
        }
    }

    private function validateRequired(string $key): void {
        if (empty(trim($this->data[$key]))) {
            $this->errors[$key][] = "{$key} field is required";
        }
    }

    private function validateMin(string $key, int $attribute) {
        if (strlen($this->data[$key]) < $attribute) {
            $this->errors[$key][] = "{$key} must be at least {$attribute} characters long";
        }
    }

    public function getErrors(): array {
        return $this->errors;
    }
}