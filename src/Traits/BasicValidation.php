<?php

/**
 * BasicValidation.php
 * 
 * This file contains the BasicValidation trait which provides fundamental validation methods
 * for form inputs or data fields. It includes common validation rules like required fields,
 * length constraints, and value similarity checks.
 * 
 * @package Ilem\Validator\Traits
 */

namespace Ilem\Validator\Traits;

/**
 * Trait BasicValidation
 * 
 * Provides basic validation methods that can be used by validator classes.
 * This trait includes common validation rules that can be chained together
 * to build complex validation scenarios.
 * 
 * The trait assumes the following properties are available in the using class:
 * - $value: The value to be validated
 * - $name: The name/identifier of the field being validated
 * - $errors: An array to store validation errors
 * - $request: The full request data array (for cross-field validation)
 * - $errorMessages: An array of default error messages
 * - checkError(): A method to check if field already has an error
 * 
 * @package Ilem\Validator\Traits
 */
trait BasicValidation
{
    /**
     * Validates that a field is required (not empty)
     * 
     * Checks if the field value exists and is not empty. For strings,
     * it trims whitespace before checking. Arrays are checked for empty.
     * 
     * @param string $error Optional custom error message
     * @return self Returns instance for method chaining
     */
    public function isRequired(string $error = ''): self
    {
        if ($this->checkError()) {
            return $this;
        }

        $value = is_array($this->value) ? $this->value : trim($this->value);
        
        if (empty($value)) {
            $this->errors[$this->name] = $error ?: $this->errorMessages['required_error'];
        }

        return $this;
    }

    /**
     * Validates maximum length of a string or array count
     * 
     * Ensures the field value does not exceed the specified maximum length.
     * For strings, checks character length. For arrays, checks element count.
     * 
     * @param int $max Maximum allowed length/count
     * @param string $error Optional custom error message
     * @return self Returns instance for method chaining
     */
    public function maxLength(int $max, string $error = ''): self
    {
        if ($this->checkError()) {
            return $this;
        }

        if (strlen($this->value) > $max) {
            $this->errors[$this->name] = $error ?: sprintf(
                '%s must be no more than %d characters',
                $this->name,
                $max
            );
        }

        return $this;
    }

    /**
     * Validates minimum length of a string or array count
     * 
     * Ensures the field value meets the specified minimum length.
     * For strings, checks character length. For arrays, checks element count.
     * 
     * @param int $min Minimum required length/count
     * @param string $error Optional custom error message
     * @return self Returns instance for method chaining
     */
    public function minLength(int $min, string $error = ''): self
    {
        if ($this->checkError()) {
            return $this;
        }

        $length = is_array($this->value) ? count($this->value) : strlen($this->value);
        
        if ($length < $min) {
            $this->errors[$this->name] = $error ?: sprintf(
                '%s must be at least %d characters',
                $this->name,
                $min
            );
        }

        return $this;
    }

    /**
     * Validates that a field matches another field's value
     * 
     * Compares the current field's value with another specified field's value
     * to ensure they match (e.g., password confirmation).
     * 
     * @param string $similar_name Name of the field to compare against
     * @param string $error Optional custom error message
     * @return self Returns instance for method chaining
     */
    public function isSimilar(string $similar_name, string $error = ''): self
    {
        if ($this->checkError()) {
            return $this;
        }

        if (!isset($this->errors[$similar_name]) && isset($this->request[$similar_name])) {
            if ($this->value !== $this->request[$similar_name]) {
                $this->errors[$this->name] = $error ?: $this->errorMessages['similarity_error'];
            }
        }

        return $this;
    }
}