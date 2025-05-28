<?php
/**
 * Clase Database para manejo de conexión a MySQL
 * Sistema de Ventas PC - Configuración de Base de Datos
 */

class Database {
    // Configuración de la base de datos
    private $host = "localhost";
    private $db_name = "sistemaventaspc";
    private $username = "root";
    private $password = "";
    private $charset = "utf8mb4";
    public $conn;

    /**
     * Obtener conexión a la base de datos
     * @return PDO|null
     */
    public function getConnection() {
        $this->conn = null;
        
        try {
            // DSN (Data Source Name) para MySQL
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=" . $this->charset;
            
            // Opciones para PDO
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            
            // Crear conexión PDO
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
            
        } catch(PDOException $exception) {
            echo "Error de conexión: " . $exception->getMessage();
            error_log("Database connection error: " . $exception->getMessage());
        }
        
        return $this->conn;
    }

    /**
     * Cerrar conexión
     */
    public function closeConnection() {
        $this->conn = null;
    }

    /**
     * Verificar si la conexión está activa
     * @return bool
     */
    public function isConnected() {
        return $this->conn !== null;
    }

    /**
     * Obtener el último ID insertado
     * @return string
     */
    public function getLastInsertId() {
        return $this->conn->lastInsertId();
    }

    /**
     * Iniciar transacción
     * @return bool
     */
    public function beginTransaction() {
        return $this->conn->beginTransaction();
    }

    /**
     * Confirmar transacción
     * @return bool
     */
    public function commit() {
        return $this->conn->commit();
    }

    /**
     * Revertir transacción
     * @return bool
     */
    public function rollback() {
        return $this->conn->rollback();
    }
}
?>