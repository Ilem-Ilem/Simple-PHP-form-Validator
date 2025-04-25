<?php

namespace Ilem\Validator\Utilities;


use finfo;

/**
 * Class FileUpload
 *
 * This class provides utility methods for handling file uploads in a secure and efficient manner.
 * It includes functionality for validating file types, sizes, and managing file storage locations.
 *
 * Properties:
 * - private string|null $uploadDir: The directory where uploaded files will be stored.
 * - private array $allowedFileTypes: An array of allowed MIME types for uploaded files.
 * - private int $maxFileSize: The maximum allowed file size for uploads (in bytes).
 *
 * Methods:
 * - public function __construct(string $uploadDir, array $allowedFileTypes, int $maxFileSize): Initializes the file upload utility with the specified configuration.
 * - public function uploadFile(array $file): string: Handles the file upload process, validates the file, and moves it to the upload directory. Returns the file path of the uploaded file.
 * - private function validateFile(array $file): void: Validates the uploaded file's type and size against the allowed configuration.
 * - private function generateUniqueFileName(string $originalName): string: Generates a unique file name to prevent overwriting existing files.
 * - public function deleteFile(string $filePath): bool: Deletes a file from the upload directory. Returns true if successful, false otherwise.
 *
 * Usage:
 * To use this class, create an instance with the desired configuration and call the `uploadFile()` method to handle file uploads.
 * Example:
 * ```php
 * $uploadDir = '/path/to/uploads';
 * $allowedFileTypes = ['image/jpeg', 'image/png', 'application/pdf'];
 * $maxFileSize = 2 * 1024 * 1024; // 2 MB
 * 
 * $fileUpload = new FileUpload($uploadDir, $allowedFileTypes, $maxFileSize);
 * 
 * if ($_FILES['file']) {
 *     try {
 *         $uploadedFilePath = $fileUpload->uploadFile($_FILES['file']);
 *         echo "File uploaded successfully: " . $uploadedFilePath;
 *     } catch (Exception $e) {
 *         echo "Error uploading file: " . $e->getMessage();
 *     }
 * }
 * ```
 */
class FileUpload
{
    private string $name;
    private string $tmpName;
    private string $type;
    private int $error;
    private int $size;
    private ?string $finalPath = null;
    private array $imageDimensions = [];

    public function __construct(array $file)
    {
        $this->name = $file['name'];
        $this->tmpName = $file['tmp_name'];
        $this->type = $file['type'];
        $this->error = $file['error'];
        $this->size = $file['size'];
    }

    public function hasError(): bool
    {
        return $this->error !== UPLOAD_ERR_OK;
    }

    public function getErrorMessage(): string
    {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
            UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
            UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload',
        ];

        return $errors[$this->error] ?? 'Unknown upload error';
    }

    public function isTypeAllowed(array $extensions, array $mimeTypes = []): bool
    {
        $extension = strtolower(pathinfo($this->name, PATHINFO_EXTENSION));
        $detectedMime = $this->getMimeType();

        // Check extension
        $extensionValid = in_array($extension, $extensions);

        // Check mime type if provided
        $mimeValid = empty($mimeTypes) || in_array($detectedMime, $mimeTypes);

        return $extensionValid && $mimeValid;
    }

    public function getMimeType(): string
    {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        return $finfo->file($this->tmpName);
    }

    public function isSizeExceeded(int $maxSize): bool
    {
        return $this->size > $maxSize;
    }

    public function getImageDimensions(): array
    {
        if (!empty($this->imageDimensions)) {
            return $this->imageDimensions;
        }

        if (!str_starts_with($this->getMimeType(), 'image/')) {
            throw new \Exception('File is not an image');
        }

        $dimensions = getimagesize($this->tmpName);
        if ($dimensions === false) {
            throw new \Exception('Could not determine image dimensions');
        }

        $this->imageDimensions = [
            'width' => $dimensions[0],
            'height' => $dimensions[1],
            'mime' => $dimensions['mime']
        ];

        return $this->imageDimensions;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function move(string $destination, ?string $newName = null): void
    {
        if (!is_dir($destination)) {
            throw new \Exception("Destination directory does not exist");
        }

        if (!is_writable($destination)) {
            throw new \Exception("Destination directory is not writable");
        }

        $filename = $newName ?? $this->name;
        $targetPath = rtrim($destination, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;

        if (!move_uploaded_file($this->tmpName, $targetPath)) {
            throw new \Exception("Failed to move uploaded file");
        }

        $this->finalPath = $targetPath;
    }

    public function getFinalPath(): ?string
    {
        return $this->finalPath;
    }
}