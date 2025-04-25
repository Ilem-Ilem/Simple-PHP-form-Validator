<?php

/**
 * ValidatorInterface
 *
 * This interface defines the contract for a validator class.
 *
 * @package Ilem\Validator
 * @author Ilem Ilem <therealteckyguys@gmail.com>
 * @license MIT
 */

namespace Ilem\Validator;

/**
 * Interface ValidatorInterface
 *
 * This interface defines the contract for a validator that can validate data,
 * check its validity, clean the data, retrieve specific data, and store the data
 * in a safe or unsafe manner.
 */
interface ValidatorInterface
{
    /**
     * Validates the given data by name.
     *
     * @param string $name The name of the data to validate.
     * @return self Returns the current instance for method chaining.
     */
    public function validate(string $name): self;

    /**
     * Checks if the data is valid.
     *
     * @return bool Returns true if the data is valid, false otherwise.
     */
    public function isValid(): bool;

    /**
     * Cleans the data and returns it as an array.
     *
     * @return array The cleaned data.
     */
    public function cleanData(): array;

    /**
     * Retrieves a specific piece of data by key.
     *
     * @param string $data The key of the data to retrieve.
     * @return string The value of the requested data.
     */
    public function get(string $data): string;

    /**
     * Stores the data in an unsafe manner.
     *
     * @return self Returns the current instance for method chaining.
     */
    public function store(): self;

    /**
     * Stores the data in a safe manner.
     *
     * @return self Returns the current instance for method chaining.
     */
    public function storeSafe(): self;
}