<?php

namespace Ilem\Validator\Traits;

use Ilem\Validator\Utilities\FileUpload;

/**
 * FileValidation trait
 * 
 * Provides comprehensive file validation and handling functionality for uploaded files.
 * This trait includes methods for validating file types, sizes, dimensions, names,
 * and handling file uploads with proper error reporting.
 * 
 * Key Features:
 * - Predefined file type categories (images, documents, archives) with configurable restrictions
 * - Custom file type validation
 * - File size validation (both category-based and custom)
 * - Image dimension validation (width/height constraints)
 * - File name pattern validation using regex
 * - File movement/upload handling
 * - Human-readable error messages and byte formatting
 * 
 * @package Ilem\Validator\Traits
 */
trait FileValidation
{
    /**
     * Instance of FileUpload utility for handling file operations
     * 
     * @var FileUpload|null
     */
    protected ?FileUpload $fileUpload = null;

    /**
     * Predefined file type categories with their allowed extensions, MIME types, and maximum sizes
     * 
     * Structure:
     * [
     *     'category_name' => [
     *         'extensions' => [allowed file extensions],
     *         'mime_types' => [allowed MIME types],
     *         'max_size' => maximum size in bytes
     *     ]
     * ]
     * 
     * @var array
     */
    protected array $allowedFileTypes = [
        'images' => [
            'extensions' => ['png', 'gif', 'jpg', 'jpeg', 'webp'],
            'mime_types' => ['image/png', 'image/gif', 'image/jpeg', 'image/webp'],
            'max_size' => 2097152, // 2MB
        ],
        'documents' => [
            'extensions' => ['pdf', 'doc', 'docx', 'txt'],
            'mime_types' => ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain'],
            'max_size' => 5242880, // 5MB
        ],
        'archives' => [
            'extensions' => ['zip', 'rar'],
            'mime_types' => ['application/zip', 'application/x-rar-compressed'],
            'max_size' => 10485760, // 10MB
        ]
    ];

    /**
     * Validate a file upload
     * 
     * Initializes the file validation process by creating a FileUpload instance
     * and checking for basic upload errors.
     * 
     * @param array $file The $_FILES array element for the uploaded file
     * @param string $name The field name used for error messages
     * @return self Returns the current instance for method chaining
     */
    public function validateFile(array $file, string $name): self
    {
        $this->fileUpload = new FileUpload($file);
        $this->name = $name;

        if ($this->fileUpload->hasError()) {
            $this->addFileError($this->fileUpload->getErrorMessage());
        }

        return $this;
    }

    /**
     * Check if file type is allowed based on predefined categories
     * 
     * Validates the file against one of the predefined categories (images, documents, archives)
     * checking both extension and MIME type.
     * 
     * @param string $category The predefined category to validate against
     * @param string|null $error Custom error message (optional)
     * @return self Returns the current instance for method chaining
     * @throws \Exception If the specified category doesn't exist
     */
    public function allowedFor(string $category, ?string $error = null): self
    {
        if ($this->checkError() || !$this->fileUpload) {
            return $this;
        }

        if (!isset($this->allowedFileTypes[$category])) {
            throw new \Exception("Invalid file category: {$category}");
        }

        $allowedTypes = $this->allowedFileTypes[$category];
        
        if (!$this->fileUpload->isTypeAllowed($allowedTypes['extensions'], $allowedTypes['mime_types'])) {
            $this->addFileError($error ?? sprintf(
                '%s must be one of these types: %s', 
                $this->name, 
                implode(', ', $allowedTypes['extensions'])
            ));
        }

        return $this;
    }

    /**
     * Check custom allowed file types
     * 
     * Validates the file against custom extensions and MIME types.
     * 
     * @param array $extensions Array of allowed file extensions
     * @param array $mimeTypes Array of allowed MIME types (optional)
     * @param string|null $error Custom error message (optional)
     * @return self Returns the current instance for method chaining
     */
    public function allowedTypes(array $extensions, array $mimeTypes = [], ?string $error = null): self
    {
        if ($this->checkError() || !$this->fileUpload) {
            return $this;
        }

        if (!$this->fileUpload->isTypeAllowed($extensions, $mimeTypes)) {
            $this->addFileError($error ?? sprintf(
                '%s must be one of these types: %s', 
                $this->name, 
                implode(', ', $extensions)
            ));
        }

        return $this;
    }

    /**
     * Check file size against category or custom size
     * 
     * Validates the file size either against a predefined category's max size
     * or a custom size in bytes.
     * 
     * @param int|string $size Either a category name (string) or max size in bytes (int)
     * @param string|null $error Custom error message (optional)
     * @return self Returns the current instance for method chaining
     */
    public function maxFileSize(int|string $size, ?string $error = null): self
    {
        if ($this->checkError() || !$this->fileUpload) {
            return $this;
        }

        $maxSize = is_string($size) && isset($this->allowedFileTypes[$size]['max_size']) 
            ? $this->allowedFileTypes[$size]['max_size'] 
            : $size;

        if ($this->fileUpload->isSizeExceeded($maxSize)) {
            $this->addFileError($error ?? sprintf(
                '%s exceeds maximum size of %s', 
                $this->name, 
                $this->formatBytes($maxSize)
            ));
        }

        return $this;
    }

    /**
     * Validate image dimensions
     * 
     * Checks if the image meets specified width and height constraints.
     * All parameters are optional - only specified dimensions will be validated.
     * 
     * @param int|null $minWidth Minimum allowed width in pixels (optional)
     * @param int|null $minHeight Minimum allowed height in pixels (optional)
     * @param int|null $maxWidth Maximum allowed width in pixels (optional)
     * @param int|null $maxHeight Maximum allowed height in pixels (optional)
     * @param string|null $error Custom error message (optional)
     * @return self Returns the current instance for method chaining
     */
    public function imageDimensions(
        ?int $minWidth = null,
        ?int $minHeight = null,
        ?int $maxWidth = null,
        ?int $maxHeight = null,
        ?string $error = null
    ): self {
        if ($this->checkError() || !$this->fileUpload) {
            return $this;
        }

        try {
            $dimensions = $this->fileUpload->getImageDimensions();
            
            if ($minWidth !== null && $dimensions['width'] < $minWidth) {
                $this->addFileError($error ?? sprintf(
                    '%s width must be at least %d pixels', 
                    $this->name, 
                    $minWidth
                ));
            }
            
            if ($minHeight !== null && $dimensions['height'] < $minHeight) {
                $this->addFileError($error ?? sprintf(
                    '%s height must be at least %d pixels', 
                    $this->name, 
                    $minHeight
                ));
            }
            
            if ($maxWidth !== null && $dimensions['width'] > $maxWidth) {
                $this->addFileError($error ?? sprintf(
                    '%s width must be no more than %d pixels', 
                    $this->name, 
                    $maxWidth
                ));
            }
            
            if ($maxHeight !== null && $dimensions['height'] > $maxHeight) {
                $this->addFileError($error ?? sprintf(
                    '%s height must be no more than %d pixels', 
                    $this->name, 
                    $maxHeight
                ));
            }
        } catch (\Exception $e) {
            $this->addFileError($error ?? $e->getMessage());
        }

        return $this;
    }

    /**
     * Validate file name pattern
     * 
     * Checks if the file name matches a specified regular expression pattern.
     * 
     * @param string $pattern Regular expression pattern to match against
     * @param string|null $error Custom error message (optional)
     * @return self Returns the current instance for method chaining
     */
    public function fileNamePattern(string $pattern, ?string $error = null): self
    {
        if ($this->checkError() || !$this->fileUpload) {
            return $this;
        }

        if (!preg_match($pattern, $this->fileUpload->getName())) {
            $this->addFileError($error ?? sprintf(
                '%s file name does not match required pattern', 
                $this->name
            ));
        }

        return $this;
    }

    /**
     * Move uploaded file to destination
     * 
     * Moves the validated file to the specified destination directory.
     * Optionally allows renaming the file during the move.
     * 
     * @param string $destination Target directory path
     * @param string|null $newName New file name (optional)
     * @return self Returns the current instance for method chaining
     */
    public function moveTo(string $destination, ?string $newName = null): self
    {
        if ($this->checkError() || !$this->fileUpload) {
            return $this;
        }

        try {
            $this->fileUpload->move($destination, $newName);
            $this->cleaned_data[$this->name] = $this->fileUpload->getFinalPath();
        } catch (\Exception $e) {
            $this->addFileError($e->getMessage());
        }

        return $this;
    }

    /**
     * Add file error with proper formatting
     * 
     * Stores an error message for the current file field.
     * 
     * @param string $message The error message to store
     * @return void
     */
    protected function addFileError(string $message): void
    {
        $this->errors[$this->name] = $message;
    }

    /**
     * Format bytes to human-readable string
     * 
     * Converts a size in bytes to a formatted string with appropriate unit (KB, MB, GB, etc.)
     * 
     * @param int $bytes Size in bytes
     * @param int $precision Number of decimal places to round to
     * @return string Formatted size string with unit
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}