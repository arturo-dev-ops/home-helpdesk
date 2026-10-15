<?php
declare(strict_types=1);

final class Database
{
    private static ?self $instance = null;
    private PDO $connection;

    private function __construct()
    {
	$host = 'db';                  // Nombre del servicio de base de datos en Docker
	$name = 'home_helpdesk';       // Nombre de tu base de datos
	$user = 'root';                // Usuario root
	$password = 'secretpassword';  // La contraseña que pusiste en el docker-compose

        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $host, $name);
        $this->connection = new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }
}
