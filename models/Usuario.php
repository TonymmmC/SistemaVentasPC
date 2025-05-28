<?php
/**
 * Modelo Usuario
 * Sistema de Ventas PC
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

class Usuario {
    private $conn;
    private $table_name = "usuarios";

    // Propiedades del usuario
    public $id_usuario;
    public $username;
    public $password;
    public $nombre;
    public $email;
    public $rol;
    public $activo;
    public $fecha_creacion;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Autenticar usuario
     * @param string $username
     * @param string $password
     * @return array|false
     */
    public function login($username, $password) {
        $query = "SELECT id_usuario, username, password, nombre, email, rol, activo 
                  FROM " . $this->table_name . " 
                  WHERE username = :username AND activo = 1 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (password_verify($password, $row['password'])) {
                // No incluir password en el retorno
                unset($row['password']);
                return $row;
            }
        }
        
        return false;
    }

    /**
     * Crear nuevo usuario
     * @return bool
     */
    public function crear() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (username, password, nombre, email, rol, activo) 
                  VALUES (:username, :password, :nombre, :email, :rol, :activo)";

        $stmt = $this->conn->prepare($query);

        // Sanitizar datos
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->rol = htmlspecialchars(strip_tags($this->rol));

        // Hash de la contraseña
        $password_hash = password_hash($this->password, PASSWORD_DEFAULT);

        // Bind de parámetros
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':password', $password_hash);
        $stmt->bindParam(':nombre', $this->nombre);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':rol', $this->rol);
        $stmt->bindParam(':activo', $this->activo);

        if ($stmt->execute()) {
            $this->id_usuario = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }

    /**
     * Obtener usuario por ID
     * @param int $id
     * @return array|false
     */
    public function obtenerPorId($id) {
        $query = "SELECT id_usuario, username, nombre, email, rol, activo, fecha_creacion 
                  FROM " . $this->table_name . " 
                  WHERE id_usuario = :id 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        return false;
    }

    /**
     * Obtener todos los usuarios
     * @return array
     */
    public function obtenerTodos() {
        $query = "SELECT id_usuario, username, nombre, email, rol, activo, fecha_creacion 
                  FROM " . $this->table_name . " 
                  ORDER BY fecha_creacion DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Actualizar usuario
     * @return bool
     */
    public function actualizar() {
        $query = "UPDATE " . $this->table_name . " 
                  SET username = :username, nombre = :nombre, email = :email, 
                      rol = :rol, activo = :activo 
                  WHERE id_usuario = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitizar datos
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->rol = htmlspecialchars(strip_tags($this->rol));

        // Bind de parámetros
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':nombre', $this->nombre);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':rol', $this->rol);
        $stmt->bindParam(':activo', $this->activo);
        $stmt->bindParam(':id', $this->id_usuario);

        return $stmt->execute();
    }

    /**
     * Cambiar contraseña
     * @param string $nueva_password
     * @return bool
     */
    public function cambiarPassword($nueva_password) {
        $query = "UPDATE " . $this->table_name . " 
                  SET password = :password 
                  WHERE id_usuario = :id";

        $stmt = $this->conn->prepare($query);
        $password_hash = password_hash($nueva_password, PASSWORD_DEFAULT);

        $stmt->bindParam(':password', $password_hash);
        $stmt->bindParam(':id', $this->id_usuario);

        return $stmt->execute();
    }

    /**
     * Eliminar usuario (desactivar)
     * @param int $id
     * @return bool
     */
    public function eliminar($id) {
        $query = "UPDATE " . $this->table_name . " 
                  SET activo = 0 
                  WHERE id_usuario = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);

        return $stmt->execute();
    }

    /**
     * Verificar si username existe
     * @param string $username
     * @param int $excluir_id
     * @return bool
     */
    public function usernameExiste($username, $excluir_id = null) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " 
                  WHERE username = :username";
        
        if ($excluir_id) {
            $query .= " AND id_usuario != :excluir_id";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        
        if ($excluir_id) {
            $stmt->bindParam(':excluir_id', $excluir_id);
        }
        
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['total'] > 0;
    }

    /**
     * Verificar si email existe
     * @param string $email
     * @param int $excluir_id
     * @return bool
     */
    public function emailExiste($email, $excluir_id = null) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " 
                  WHERE email = :email";
        
        if ($excluir_id) {
            $query .= " AND id_usuario != :excluir_id";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        
        if ($excluir_id) {
            $stmt->bindParam(':excluir_id', $excluir_id);
        }
        
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['total'] > 0;
    }

    /**
     * Validar datos del usuario
     * @return array
     */
    public function validar() {
        $errores = [];

        // Validar username
        if (empty($this->username)) {
            $errores[] = "El nombre de usuario es requerido";
        } elseif (strlen($this->username) < 3) {
            $errores[] = "El nombre de usuario debe tener al menos 3 caracteres";
        } elseif ($this->usernameExiste($this->username, $this->id_usuario)) {
            $errores[] = "El nombre de usuario ya existe";
        }

        // Validar nombre
        if (empty($this->nombre)) {
            $errores[] = "El nombre es requerido";
        }

        // Validar email
        if (empty($this->email)) {
            $errores[] = "El email es requerido";
        } elseif (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $errores[] = "El email no es válido";
        } elseif ($this->emailExiste($this->email, $this->id_usuario)) {
            $errores[] = "El email ya está registrado";
        }

        // Validar rol
        $roles_validos = [ROL_ADMIN, ROL_VENDEDOR, ROL_ALMACEN];
        if (empty($this->rol) || !in_array($this->rol, $roles_validos)) {
            $errores[] = "El rol debe ser uno de: " . implode(', ', $roles_validos);
        }

        return $errores;
    }
}
?>