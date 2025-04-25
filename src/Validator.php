<?php

/**
 * Validator Component
 * 
 * This file contains the main Validator class which provides comprehensive validation
 * functionality for form inputs and data. It combines various validation traits to
 * offer a complete validation solution including basic, type, string, and database validations.
 * 
 * @package Ilem\Validator
 * @author Your Name <your.email@example.com>
 * @version 1.0.0
 * @license MIT
 */

namespace Ilem\Validator;

use Ilem\Validator\Database\Database;
use Ilem\Validator\ValidatorInterface;
use Ilem\Validator\Traits\BasicValidation;
use Ilem\Validator\Traits\DatabaseValidation;
use Ilem\Validator\Traits\StringValidation;
use Ilem\Validator\Traits\TypeValidation;
use PDO;

/**
 * Main Validator class implementing ValidatorInterface
 * 
 * This class serves as the core validation component, combining multiple validation traits
 * to provide a comprehensive validation solution. It handles:
 * - Basic validations (required, email, etc.)
 * - Type validations (numbers, arrays, etc.)
 * - String validations (length, patterns, etc.)
 * - Database validations (unique checks, exists checks)
 * 
 * The validator automatically processes POST and FILES data upon instantiation and provides
 * methods to validate, sanitize, and store cleaned data.
 * 
 * @method self validate(string $name) Initialize validation for a specific field
 * @method bool isValid() Check if all validations passed
 * @method array cleanData() Get all cleaned/validated data
 * @method string get(string $data) Get specific cleaned value
 * @method self store() Store the raw validated value
 * @method self storeSafe() Store HTML-escaped validated value
 * @method bool checkError() Check if current field has errors
 */
class Validator implements ValidatorInterface
{
    use BasicValidation,
        DatabaseValidation,
        StringValidation,
        TypeValidation;

    /**
     * Array to store validation errors
     * 
     * @var array Format: ['field_name' => 'error_message']
     */
    public array $errors = [];

    /**
     * Array to store cleaned/validated data
     * 
     * @var array Format: ['field_name' => 'cleaned_value']
     */
    protected array $cleaned_data = [];

    /**
     * Name of the current field being validated
     * 
     * @var string
     */
    protected string $name = '';

    /**
     * Value of the current field being validated
     * 
     * @var mixed
     */
    protected $value = '';

    /**
     * Combined array of POST and FILES data
     * 
     * @var array|null
     */
    protected ?array $request;

    /**
     * PDO database connection instance
     * 
     * @var PDO
     */
    protected PDO $conn;

    /**
     * Default error messages for various validation types
     * 
     * @var array Format: ['error_type' => 'message']
     */
    protected array $errorMessages = [
        'text_error' => 'Only letters and numbers are allowed',
        'number_error' => 'Only numbers are allowed',
        'unique_error' => 'This value already exists',
        'required_error' => 'This field is required',
        'email_error' => 'Invalid email address',
        'length_error' => 'Invalid length',
        'file_type_error' => 'Invalid file type',
        'file_size_error' => 'File size too large',
        'file_upload_error' => 'File upload error',
        'similarity_error' => 'Values do not match',
        'caps_error' => 'Must contain at least one uppercase letter',
        'number_required_error' => 'Must contain at least one number',
        'symbol_required_error' => 'Must contain at least one symbol',
        'no_numbers_error' => 'Numbers are not allowed',
        'no_symbols_error' => 'Symbols are not allowed',
        'no_space_error' => 'Spaces are not allowed',
        'array_error' => 'Please select at least one option',
    ];

    /**
     * Validator constructor
     * 
     * Initializes the validator by:
     * 1. Setting up the database connection
     * 2. Combining POST and FILES data into the request property
     */
    public function __construct()
    {
        $this->conn = Database::getPDO();
        $this->request = array_merge($_POST, $_FILES);
    }

    /**
     * Initialize validation for a specific field
     * 
     * @param string $name The name of the field to validate (must exist in POST/FILES)
     * @return self
     * @throws \Exception If the field doesn't exist in the request
     */
    public function validate(string $name): self
    {
        if (array_key_exists($name, $this->request)) {
            $this->value = $this->request[$name];
            $this->name = $name;
        } else {
            throw new \Exception('Field "' . $name . '" does not exist in the request', 1);
        }
        return $this;
    }

    /**
     * Check if all validations passed (no errors)
     * 
     * @return bool True if no validation errors, false otherwise
     */
    public function isValid(): bool
    {
        return empty($this->errors);
    }

    /**
     * Get all cleaned/validated data
     * 
     * @return array Associative array of all successfully validated fields
     */
    public function cleanData(): array
    {
        return $this->cleaned_data;
    }

    /**
     * Get a specific cleaned value by field name
     * 
     * @param string $data Field name to retrieve
     * @return string The cleaned value or empty string if not found
     */
    public function get(string $data): string
    {
        return $this->cleaned_data[$data] ?? '';
    }

    /**
     * Store the raw validated value in cleaned_data
     * 
     * @return self
     */
    public function store(): self
    {
        $this->cleaned_data[$this->name] = $this->value;
        return $this;
    }

    /**
     * Store HTML-escaped validated value in cleaned_data
     * 
     * Uses htmlspecialchars with ENT_QUOTES flag and UTF-8 encoding
     * 
     * @return self
     */
    public function storeSafe(): self
    {
        $this->cleaned_data[$this->name] = htmlspecialchars($this->value, ENT_QUOTES, 'UTF-8');
        return $this;
    }

    /**
     * Check if the current field has validation errors
     * 
     * @return bool True if errors exist for current field, false otherwise
     */
    public function checkError(): bool
    {
        return isset($this->errors[$this->name]);
    }
}