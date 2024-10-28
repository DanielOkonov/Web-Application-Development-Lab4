<?php

namespace src\Repositories;

use PDO;
use PDOException;

/**
 * An example of a base class to reduce database connectivity configuration for each repository subclass.
 */
class Repository
{

    protected PDO $pdo;
    private string $hostname;
    private string $username;
    private string $databaseName;
    private string $databasePassword;
    private string $charset;
    private string $port;

    // Function to load and parse the .env file
    private function loadEnvFile($filePath)
    {
        if (!file_exists($filePath)) {
            throw new Exception('.env file not found');
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            list($name, $value) = explode('=', $line, 2);

            $value = trim($value, '"');

            putenv("$name=$value");
        }
    }

    public function __construct()
    {
        // Load the .env file (adjust the path to point to your project's root)
        $this->loadEnvFile(__DIR__ . '/../../.env');

        // Get environment variables
        $this->hostname = getenv('DB_HOST');
        $this->username = getenv('DB_USERNAME');
        $this->databaseName = getenv('DB_DATABASE');
        $this->databasePassword = getenv('DB_PASSWORD');
        $this->charset = 'utf8mb4';
        $this->port = getenv('DB_PORT');

        $dsn = "mysql:host=$this->hostname;port=$this->port;dbname=$this->databaseName;charset=$this->charset";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        try {
            $this->pdo = new PDO($dsn, $this->username, $this->databasePassword, $options);
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage(), (int) $e->getCode());
        }
    }
}
