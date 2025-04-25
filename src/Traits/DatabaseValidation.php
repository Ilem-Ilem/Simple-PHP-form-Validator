<?php

namespace Ilem\Validator\Traits;

use PDOException;

/**
 * DatabaseValidation.php
 * 
 * This file contains the DatabaseValidation trait which provides database-related validation functionality,
 * specifically for checking uniqueness of values in database tables. It's part of the Ilem\Validator component.
 * 
 * The trait is designed to be used within validator classes that need to interact with a database connection
 * to perform validation checks. It handles PDO operations and error management for database uniqueness validation.
 */

/**
 * DatabaseValidation Trait
 * 
 * Provides methods for validating data against database constraints. Currently implements
 * a uniqueness validator that checks if a given value exists in a specified database table column.
 * 
 * This trait requires the following properties to be available in the using class:
 * - $conn: A PDO database connection instance
 * - $name: The name of the field being validated
 * - $value: The value being validated
 * - $errors: An array to store validation errors
 * - $errorMessages: An array of default error messages
 * - checkError(): A method to check if validation should continue
 * 
 * @package Ilem\Validator\Traits
 */
trait DatabaseValidation
{
    /**
     * Checks if the current value is unique in the specified database table column
     * 
     * Performs a database query to verify if the value doesn't already exist in the given table column.
     * If the value exists, adds an error to the errors array. Uses prepared statements for security.
     * 
     * @param string $table The database table name to check against
     * @param string $column_name The column name to check (defaults to current field name)
     * @param string $error Custom error message (falls back to default unique_error message)
     * 
     * @return self Returns the current instance for method chaining
     * 
     * @throws \Exception If a database error occurs during the query execution
     * 
     * @example
     * $validator->isUnique('users', 'email', 'Email already exists');
     */
    public function isUnique(string $table, string $column_name = '', string $error = ''): self
    {
        // Skip validation if there's already an error for this field
        if ($this->checkError()) {
            return $this;
        }

        // Use the current field name if no column name is provided
        $column_name = empty($column_name) ? $this->name : $column_name;

        try {
            // Prepare and execute the uniqueness check query
            $sql = 'SELECT COUNT(*) FROM ' . $table . ' WHERE ' . $column_name . ' = :value';
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':value', $this->value);
            $stmt->execute();
            
            // If count is greater than 0, the value is not unique
            if ($stmt->fetchColumn() > 0) {
                $this->errors[$this->name] = $error ?: $this->errorMessages['unique_error'];
            }
        } catch (PDOException $e) {
            // Convert PDOException to a generic Exception for consistent error handling
            throw new \Exception('Database error: ' . $e->getMessage(), 1);
        }

        return $this;
    }
}