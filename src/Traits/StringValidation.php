<?php

/**
 * StringValidation.php - A trait providing string validation methods for PHP applications.
 *
 * This file contains the StringValidation trait which offers a collection of methods to validate
 * various string patterns and characteristics. It's designed to be used within validation classes
 * to provide common string validation functionality with customizable error messages.
 *
 * @package Ilem\Validator\Traits
 */

namespace Ilem\Validator\Traits;

/**
 * Trait StringValidation
 *
 * Provides a set of string validation methods that can be used to check various string patterns
 * and characteristics. Each method performs a specific validation and adds an error message
 * to the errors array if the validation fails. Methods support custom error messages.
 *
 * The trait includes validations for:
 * - Alphabetic characters only
 * - Presence of uppercase letters
 * - Presence of numbers
 * - Exclusion of numbers
 * - Presence of symbols
 * - Exclusion of symbols
 * - Exclusion of spaces
 * - String slugification
 *
 * All validation methods follow a fluent interface pattern, allowing for method chaining.
 *
 * @package Ilem\Validator\Traits
 */
trait StringValidation
{
    /**
     * Validates that the string contains only alphabetic characters (a-z, A-Z).
     *
     * @param string $error Optional custom error message
     * @return self Returns the current instance for method chaining
     */
    public function isAlpha(string $error = ''): self
    {
        if ($this->checkError()) {
            return $this;
        }

        if (!preg_match("/^[a-zA-Z]+$/", $this->value)) {
            $this->errors[$this->name] = $error ?: $this->errorMessages['text_error'];
        }

        return $this;
    }

    /**
     * Validates that the string contains at least one uppercase letter.
     *
     * @param string $error Optional custom error message
     * @return self Returns the current instance for method chaining
     */
    public function hasCaps(string $error = ''): self
    {
        if ($this->checkError()) {
            return $this;
        }

        if (!preg_match("/[A-Z]/", $this->value)) {
            $this->errors[$this->name] = $error ?: $this->errorMessages['caps_error'];
        }

        return $this;
    }

    /**
     * Validates that the string contains at least one numeric character.
     *
     * @param string $error Optional custom error message
     * @return self Returns the current instance for method chaining
     */
    public function hasNumbers(string $error = ''): self
    {
        if ($this->checkError()) {
            return $this;
        }

        if (!preg_match("/[0-9]/", $this->value)) {
            $this->errors[$this->name] = $error ?: $this->errorMessages['number_required_error'];
        }

        return $this;
    }

    /**
     * Validates that the string contains no numeric characters.
     *
     * @param string $error Optional custom error message
     * @return self Returns the current instance for method chaining
     */
    public function noNumbers(string $error = ''): self
    {
        if ($this->checkError()) {
            return $this;
        }

        if (preg_match("/[0-9]/", $this->value)) {
            $this->errors[$this->name] = $error ?: $this->errorMessages['no_numbers_error'];
        }

        return $this;
    }

    /**
     * Validates that the string contains at least one symbol/special character.
     *
     * Note: Uses \W which matches any non-word character (equivalent to [^a-zA-Z0-9_])
     *
     * @param string $error Optional custom error message
     * @return self Returns the current instance for method chaining
     */
    public function hasSymbols(string $error = ''): self
    {
        if ($this->checkError()) {
            return $this;
        }

        if (!preg_match("/[\W]/", $this->value)) {
            $this->errors[$this->name] = $error ?: $this->errorMessages['symbol_required_error'];
        }

        return $this;
    }

    /**
     * Validates that the string contains no symbols/special characters.
     *
     * Note: Uses \W which matches any non-word character (equivalent to [^a-zA-Z0-9_])
     *
     * @param string $error Optional custom error message
     * @return self Returns the current instance for method chaining
     */
    public function noSymbols(string $error = ''): self
    {
        if ($this->checkError()) {
            return $this;
        }

        if (preg_match("/[\W]/", $this->value)) {
            $this->errors[$this->name] = $error ?: $this->errorMessages['no_symbols_error'];
        }

        return $this;
    }

    /**
     * Validates that the string contains no whitespace characters.
     *
     * @param string $error Optional custom error message
     * @return self Returns the current instance for method chaining
     */
    public function noSpace(string $error = ''): self
    {
        if ($this->checkError()) {
            return $this;
        }

        if (strpos($this->value, ' ') !== false) {
            $this->errors[$this->name] = $error ?: $this->errorMessages['no_space_error'];
        }

        return $this;
    }

    /**
     * Converts a string to a slug format by replacing special characters with underscores.
     *
     * This method replaces various special characters and spaces with underscores to create
     * a URL-friendly slug. The characters replaced include:
     * ~`@!#$%^&*()_-+={}[]'"<>.,/|\? and space
     *
     * @param string $value The string to be slugified
     * @return string The slugified string with special characters replaced by underscores
     */
    public function slugify(string $value): string
    {
        $clean_chars = [
            '~', '`', '@', '!', '#', '$', '%', '^', '&', '*', '(', ')', 
            '_', '-', '+', '=', '[', ']', '{', '}', '\'', ':', '"', 
            ';', '>', '<', '.', ',', '/', '|', '\\', '?', ' '
        ];
        return str_replace($clean_chars, '_', $value);
    }
}