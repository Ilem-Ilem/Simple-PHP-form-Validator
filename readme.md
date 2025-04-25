# Ilem Validator

A comprehensive PHP validation library with support for basic validation, string validation, type validation, database validation, and file upload validation.

## Features

- **Basic Validation**: Required fields, length constraints, value similarity
- **String Validation**: Alphabetic checks, uppercase, numbers, symbols, spaces
- **Type Validation**: Email, numeric, array validation
- **Database Validation**: Unique value checks
- **File Validation**: Type, size, dimensions, name patterns
- **Fluent Interface**: Method chaining for easy validation rules
- **Error Handling**: Customizable error messages

## Installation

download and move the src to your project folder
## Basic Usage

```php
use Ilem\Validator\Validator;

$validator = new Validator();
```

## Configuration
Database Configuration
Create a config/database.php file:

```php
//in your config/database.php file

return [
    'dbType' => 'mysql',
    'host' => 'localhost',
    'dbName' => 'your_database',
    'user' => 'username',
    'password' => 'password',
    'charset' => 'utf8mb4',
    'attributes' => [
        PDO::ATTR_PERSISTENT => true
    ]
];
```

## Basic Validation Examples
```php
// Validate a required field
$validator->validate('username')
    ->isRequired()
    ->minLength(5)
    ->maxLength(20)
    ->store();

// Validate email
$validator->validate('email')
    ->isRequired()
    ->isEmail()
    ->storeSafe();

// Check if validation passed
if ($validator->isValid()) {
    $cleanData = $validator->cleanData();
    // Process data
} else {
    $errors = $validator->errors;
    // Handle errors
}
```
## String Validation

```php
$validator->validate('password')
    ->isRequired()
    ->minLength(8)
    ->hasCaps()
    ->hasNumbers()
    ->hasSymbols()
    ->noSpace()
    ->store();


```
## File Validation

```php
// Validate an uploaded file
$validator->validateFile($_FILES['avatar'], 'avatar')
    ->allowedFor('images')
    ->maxFileSize(2 * 1024 * 1024) // 2MB
    ->imageDimensions(100, 100) // min width/height
    ->fileNamePattern('/^[a-z0-9_-]+$/i')
    ->moveTo('/path/to/uploads');
```
## Custom Error Messages
Most validation methods accept an optional custom error message as their last parameter. Here's how to use them:

## Basic Validation with Custom Errors

```php
$validator->validate('username')
    ->isRequired('Please provide a username') // Custom required message
    ->minLength(5, 'Username must be at least 5 characters')
    ->maxLength(20, 'Username cannot exceed 20 characters')
    ->store();

$validator->validate('email')
    ->isEmail('Please enter a valid email address') // Custom email error
    ->isUnique('users', 'email', 'This email is already registered') // Custom unique error
    ->storeSafe();

$validator->validate('password')
    ->hasCaps('Password must contain at least one uppercase letter')
    ->hasNumbers('Password must contain at least one number')
    ->hasSymbols('Password must contain at least one special character')
    ->noSpace('Password cannot contain spaces')
    ->store();

$validator->validateFile($_FILES['avatar'], 'avatar')
    ->allowedFor('images', 'Only image files are allowed')
    ->maxFileSize('images', 'Image must be smaller than 2MB') // Using category
    ->maxFileSize(3 * 1024 * 1024, 'File must be smaller than 3MB') // Using bytes
    ->imageDimensions(
        100, // min width
        100, // min height
        null, // max width (not set)
        null, // max height (not set)
        'Image must be at least 100x100 pixels' // Custom error
    )
    ->moveTo('/path/to/uploads');

$validator->validate('email')
    ->isUnique(
        'users', // table
        'email', // column (optional)
        'This email is already registered with us' // custom error
    );
```
## Important Notes:
- The custom error message is always the last parameter of the method
- If no custom error is provided, the default message from $errorMessages will be used
- For methods with multiple parameters (like imageDimensions), the error parameter comes after all validation parameters
- You can also override all default messages by extending the Validator

```php
class CustomValidator extends Validator
{
    protected array $errorMessages = [
        'required_error' => 'This field must be filled',
        'email_error' => 'Invalid email format',
        // ... other custom defaults
    ];
}
```
Then use your custom validator:

```php
$validator = new CustomValidator();
$validator->validate('email')->isEmail()->storeSafe(); // Will use your custom email error
```

## Other Functions
## BasicValidation
- **isRequired()**: - Field must not be empty
- **minLength(int $min)**: - Minimum length
- **maxLength(int $max)**: - Maximum length
- **isSimilar(string $similar_name)**: - Match another field's value

## StringValidation
- **isAlpha()**: - Only alphabetic characters
- **hasCaps()**: - Must contain uppercase
- **hasNumbers()**: - Must contain numbers
- **noNumbers()**: - No numbers allowed
- **hasSymbols()**: - Must contain symbols
- **noSymbols()**: - No symbols allowed
- **noSpace()**: - No spaces allowed

## TypeValidation
- **isEmail()**: - Valid email format
- **isNumeric()**: - Numeric value
- **isArray()**: - Value must be array

## DatabaseValidation
- **isUnique(string $table, string $column_name)** - Unique in database

#FileValidation
- **validateFile(array $file, string $name)** - Initialize file validation
- **allowedFor(string $category)** - Check against predefined categories
- **allowedTypes(array $extensions, array $mimeTypes)** - Custom types
- **maxFileSize(int|string $size)** - Maximum file size
- **imageDimensions()** - Validate image dimensions
- **fileNamePattern(string $pattern)** - Validate filename
- **moveTo(string $destination)** - Move validated file

## License
MIT

## Contributing
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.
