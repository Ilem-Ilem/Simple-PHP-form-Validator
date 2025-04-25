<?php

namespace Ilem\Validator\Traits;

/**
 * File: TypeValidation.php
 * 
 * This file contains the TypeValidation trait which provides type-specific validation methods
 * for common data types including email, numeric values, and arrays. These validation methods
 * are designed to be chainable and integrate with a larger validation system.
 * 
 * The trait checks the type of a given value and records validation errors if the check fails,
 * allowing for custom error messages or falling back to default messages.
 */

/**
 * Trait TypeValidation
 * 
 * Provides type validation methods for common PHP data types. This trait is intended to be used
 * within a validator class that has the following properties:
 * - $value: The value to be validated
 * - $name: The name/identifier of the field being validated
 * - $errors: An array to store validation errors
 * - $errorMessages: An array of default error messages
 * - checkError(): A method to check if there's already an error for the field
 * 
 * The methods in this trait are chainable and will skip validation if an error already exists
 * for the current field.
 */
trait TypeValidation
{
    /**
     * Validate that the value is a properly formatted email address
     * 
     * Uses PHP's filter_var with FILTER_VALIDATE_EMAIL to check email format.
     * If validation fails, stores either the provided error message or a default
     * email error message from $this->errorMessages.
     * 
     * @param string $error Optional custom error message
     * @return self Returns the current instance for method chaining
     */
    public function isEmail(string $error = ''): self
    {
        // Skip validation if there's already an error for this field
        if ($this->checkError()) {
            return $this;
        }

        // Validate email format using PHP's built-in filter
        if (!filter_var($this->value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$this->name] = $error ?: $this->errorMessages['email_error'];
        }

        return $this;
    }

    /**
     * Validate that the value is numeric
     * 
     * Checks if the value is numeric using is_numeric() which validates both
     * numbers and numeric strings. If validation fails, stores either the
     * provided error message or a default number error message.
     * 
     * @param string $error Optional custom error message
     * @return self Returns the current instance for method chaining
     */
    public function isNumeric(string $error = ''): self
    {
        // Skip validation if there's already an error for this field
        if ($this->checkError()) {
            return $this;
        }

        // Check if the value is numeric
        if (!is_numeric($this->value)) {
            $this->errors[$this->name] = $error ?: $this->errorMessages['number_error'];
        }

        return $this;
    }

    /**
     * Validate that the value is an array
     * 
     * Checks if the value is an array using is_array(). If validation fails,
     * stores either the provided error message or a default array error message.
     * 
     * @param string $error Optional custom error message
     * @return self Returns the current instance for method chaining
     */
    public function isArray(string $error = ''): self
    {
        // Skip validation if there's already an error for this field
        if ($this->checkError()) {
            return $this;
        }

        // Check if the value is an array
        if (!is_array($this->value)) {
            $this->errors[$this->name] = $error ?: $this->errorMessages['array_error'];
        }

        return $this;
    }
}