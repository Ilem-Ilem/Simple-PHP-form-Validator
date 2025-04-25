<?php


namespace Ilem\Validator\Database;

/**
 * This file is part of the Ilem Validator package.
 *
 * (c) Ilem <https://github.com/Ilem>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

use Exception;
use PDO;
/**
 * Class Database
 *
 * This class provides a singleton implementation for managing database connections using PDO.
 * It supports multiple database types such as MySQL, SQLite, PostgreSQL, SQL Server, Oracle, and MariaDB.
 * The class ensures that only one instance of the database connection is created and reused throughout the application.
 *
 * Properties:
 * - private static ?PDO $pdo: The static PDO instance used for database interactions.
 * - private static ?self $instance: The singleton instance of the Database class.
 * - private string|null $dbType: The type of the database (e.g., mysql, sqlite).
 * - private string|null $host: The host address of the database server.
 * - private string|null $dbName: The name of the database.
 * - private string|null $user: The username for the database connection.
 * - private string|null $password: The password for the database connection.
 * - private string|null $charset: The character set used for the database connection.
 * - private array $config: The configuration array containing database connection details.
 *
 * Methods:
 * - private function __construct(array $config): Initializes the database connection using the provided configuration.
 * - public static function getInstance(array $config = []): self: Returns the singleton instance of the Database class.
 * - private function connect(): void: Establishes the database connection using the provided configuration.
 * - private function buildDsn(): string: Constructs the DSN (Data Source Name) string based on the database type.
 * - private function buildMysqlDsn(): string: Builds the DSN for MySQL and MariaDB.
 * - private function buildSqliteDsn(): string: Builds the DSN for SQLite.
 * - private function buildPgsqlDsn(): string: Builds the DSN for PostgreSQL.
 * - private function buildSqlsrvDsn(): string: Builds the DSN for SQL Server (SQLSRV).
 * - private function buildOciDsn(): string: Builds the DSN for Oracle (OCI).
 * - public static function getPDO(): PDO: Returns the PDO instance for interacting with the database.
 * - public function close(): void: Closes the database connection by setting the PDO instance to null.
 *
 * Usage:
 * To use this class, call the `getInstance()` method with the required configuration to initialize the database connection.
 * Once initialized, you can retrieve the PDO instance using the `getPDO()` method for executing queries.
 * Example:
 * ```php
 * $config = [
 *     'dbType' => 'mysql',
 *     'host' => 'localhost',
 *     'dbName' => 'example_db',
 *     'user' => 'root',
 *     'password' => 'password',
 *     'charset' => 'utf8mb4'
 * ];
 * $db = Database::getInstance($config);
 * $pdo = $db->getPDO();
 * ```
 */
class Database {
    private static ?PDO $pdo = null; // Static PDO instance
    private static ?self $instance = null; // Singleton instance
    private string|null $dbType; // Type of the database (e.g., mysql, sqlite)
    private string|null  $host; // Host address of the database server
    private string|null  $dbName; // Database name
    private string|null  $user; // Database username
    private string|null  $password; // Database password
    private string|null  $charset; // Character set to use for the connection

    private array $config;
    /**
     * Database constructor 😁 that initializes the database connection.
     *
     * @param array $config Configuration array containing database connection details.
     *                       Expected keys are 'dbType', 'host', 'dbName', 'user', 'password', 'charset'.
     *                       Example: ['dbType' => 'mysql', 'host' => 'localhost', ...]
     */
    
    private function __construct(array $config) {
        $this->dbType = $config['dbType'];
        $this->host = $config['host'] ?? null;
        $this->dbName = $config['dbName'] ?? null;
        $this->user = $config['user'] ?? null;
        $this->password = $config['password'] ?? null;
        $this->charset = $config['charset'] ?? 'utf8mb4';

        $this->config = $config;

        $this->connect();
    }

    /**
     * Get the singleton instance
     */
    public static function getInstance(array $config = []): self
    {
        if (self::$instance === null) {
            if (empty($config)) {
                throw new Exception('Database configuration must be provided for first initialization');
            }
            self::$instance = new self($config);
        }
        return self::$instance;
    }


    /**
     * Establishes the database connection based on the provided configuration.
     *
     * This method constructs the appropriate DSN (Data Source Name) based on the database type
     * and creates a PDO instance to interact with the database.
     *
     * @throws PDOException If the connection fails.
     */
    private function connect(): void
    {
        try {
            $dsn = $this->buildDsn();
            self::$pdo = new PDO($dsn, $this->user, $this->password);
            
            // Set default attributes
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            // Set additional attributes if provided
            if (isset($this->config['attributes'])) {
                foreach ($this->config['attributes'] as $attribute => $value) {
                    self::$pdo->setAttribute($attribute, $value);
                }
            }
        } catch (\PDOException $e) {
            throw new Exception("Connection failed: " . $e->getMessage());
        }
    }
    /**
     * Builds the DSN (Data Source Name) string based on the configured database type.
     *
     * @return string The DSN string for the PDO connection.
     */
    private function buildDsn(): string {
        switch ($this->dbType) {
            case 'mysql':
                return $this->buildMysqlDsn();
            case 'sqlite':
                return $this->buildSqliteDsn();
            case 'pgsql':
                return $this->buildPgsqlDsn();
            case 'sqlsrv':
                return $this->buildSqlsrvDsn();
            case 'oci':
                return $this->buildOciDsn();
            case 'mariadb':
                return $this->buildMysqlDsn(); // MariaDB uses the same DSN as MySQL
            default:
                throw new Exception('Unsupported database type');
        }
    }

    /**
     * Builds the DSN for MySQL and MariaDB.
     *
     * @return string The MySQL/MariaDB DSN string.
     */
    private function buildMysqlDsn(): string {
        return "mysql:host={$this->host};dbname={$this->dbName};charset={$this->charset}";
    }

    /**
     * Builds the DSN for SQLite.
     *
     * @return string The SQLite DSN string.
     */
    private function buildSqliteDsn(): string {
        return "sqlite:{$this->dbName}"; // SQLite uses the database file path as the DSN
    }

    /**
     * Builds the DSN for PostgreSQL.
     *
     * @return string The PostgreSQL DSN string.
     */
    private function buildPgsqlDsn(): string {
        return "pgsql:host={$this->host};dbname={$this->dbName}";
    }

    /**
     * Builds the DSN for SQL Server (SQLSRV).
     *
     * @return string The SQL Server DSN string.
     */
    private function buildSqlsrvDsn(): string {
        return "sqlsrv:Server={$this->host};Database={$this->dbName}";
    }

    /**
     * Builds the DSN for Oracle (OCI).
     *
     * @return string The Oracle DSN string.
     */
    private function buildOciDsn(): string {
        return "oci:dbname={$this->host}/{$this->dbName}";
    }

    /**
     * Returns the PDO instance for interacting with the database.
     *
     * @return PDO The PDO instance representing the database connection.
     */
    public static function getPDO(): PDO
    {
        if (self::$pdo === null) {
            throw new Exception('Database connection not initialized. Call getInstance() first.');
        }
        return self::$pdo;
    }

    /**
     * Closes the database connection.
     *
     * This method sets the PDO instance to null, effectively closing the connection.
     */     
    public function close() {
       self::$pdo = null; // Close the connection by setting PDO instance to null
    }

}
