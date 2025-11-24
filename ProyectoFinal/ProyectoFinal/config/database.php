<?php
// config/database.php

/**
 * Configuración de conexión a la base de datos
 * Implementación robusta con variables de entorno
 */

class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        // Cargar configuración desde variables de entorno o usar valores por defecto seguros
        $host = getenv('DB_HOST') ?: 'localhost';
        $db   = getenv('DB_NAME') ?: 'Zoologico';
        $user = getenv('DB_USER') ?: 'root';
        $pass = getenv('DB_PASS') ?: '';
        $charset = 'utf8mb4';

        try {
            $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES $charset"
            ];

            $this->conn = new PDO($dsn, $user, $pass, $options);
        } catch(PDOException $e) {
            // En producción, no mostrar el mensaje exacto de error de conexión al usuario
            error_log("Error de conexión BD: " . $e->getMessage());
            throw new Exception("Error de conexión al sistema de datos.");
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->conn;
    }

    private function __clone() {}
    public function __wakeup() {
        throw new Exception("No se puede deserializar un singleton");
    }
}
?>