<?php
/**
 * Gestión de Sesiones y Autenticación
 * Sistema de Ventas PC
 */

// Incluir configuración
require_once 'config.php';

/**
 * Clase para manejo de sesiones
 */
class SessionManager {
    
    /**
     * Inicializar sesión si no está iniciada
     */
    public static function init() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Verificar si el usuario está logueado
     * @return bool
     */
    public static function isLoggedIn() {
        return isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']);
    }
    
    /**
     * Obtener ID del usuario logueado
     * @return int|null
     */
    public static function getUserId() {
        return self::isLoggedIn() ? $_SESSION['usuario_id'] : null;
    }
    
    /**
     * Obtener rol del usuario logueado
     * @return string|null
     */
    public static function getUserRole() {
        return self::isLoggedIn() ? $_SESSION['usuario_rol'] : null;
    }
    
    /**
     * Obtener nombre del usuario logueado
     * @return string|null
     */
    public static function getUserName() {
        return self::isLoggedIn() ? $_SESSION['usuario_nombre'] : null;
    }
    
    /**
     * Obtener username del usuario logueado
     * @return string|null
     */
    public static function getUsername() {
        return self::isLoggedIn() ? $_SESSION['username'] : null;
    }
    
    /**
     * Crear sesión de usuario
     * @param array $user_data Datos del usuario
     */
    public static function createSession($user_data) {
        $_SESSION['usuario_id'] = $user_data['id_usuario'];
        $_SESSION['username'] = $user_data['username'];
        $_SESSION['usuario_nombre'] = $user_data['nombre'];
        $_SESSION['usuario_email'] = $user_data['email'];
        $_SESSION['usuario_rol'] = $user_data['rol'];
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        
        // Regenerar ID de sesión por seguridad
        session_regenerate_id(true);
        
        // Log del login
        log_message("Usuario logueado: " . $user_data['username'] . " (ID: " . $user_data['id_usuario'] . ")");
    }
    
    /**
     * Destruir sesión
     */
    public static function destroySession() {
        if (self::isLoggedIn()) {
            $username = self::getUsername();
            log_message("Usuario deslogueado: " . $username);
        }
        
        // Limpiar variables de sesión
        $_SESSION = array();
        
        // Destruir cookie de sesión
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        // Destruir sesión
        session_destroy();
    }
    
    /**
     * Verificar si la sesión ha expirado
     * @return bool
     */
    public static function isSessionExpired() {
        if (!self::isLoggedIn()) {
            return true;
        }
        
        $timeout = 3600; // 1 hora
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
            return true;
        }
        
        // Actualizar tiempo de última actividad
        $_SESSION['last_activity'] = time();
        return false;
    }
    
    /**
     * Verificar permisos de rol
     * @param string $required_role Rol requerido
     * @return bool
     */
    public static function hasRole($required_role) {
        if (!self::isLoggedIn()) {
            return false;
        }
        
        $user_role = self::getUserRole();
        
        // Admin tiene acceso a todo
        if ($user_role === ROL_ADMIN) {
            return true;
        }
        
        // Verificar rol específico
        return $user_role === $required_role;
    }
    
    /**
     * Verificar múltiples roles
     * @param array $roles Array de roles permitidos
     * @return bool
     */
    public static function hasAnyRole($roles) {
        if (!self::isLoggedIn()) {
            return false;
        }
        
        $user_role = self::getUserRole();
        
        // Admin tiene acceso a todo
        if ($user_role === ROL_ADMIN) {
            return true;
        }
        
        return in_array($user_role, $roles);
    }
    
    /**
     * Verificar si es administrador
     * @return bool
     */
    public static function isAdmin() {
        return self::hasRole(ROL_ADMIN);
    }
    
    /**
     * Verificar si es vendedor
     * @return bool
     */
    public static function isVendedor() {
        return self::hasRole(ROL_VENDEDOR);
    }
    
    /**
     * Verificar si es almacén
     * @return bool
     */
    public static function isAlmacen() {
        return self::hasRole(ROL_ALMACEN);
    }
    
    /**
     * Redireccionar si no está logueado
     * @param string $redirect_url URL de redirección
     */
    public static function requireLogin($redirect_url = 'login.php') {
        if (!self::isLoggedIn() || self::isSessionExpired()) {
            if (self::isSessionExpired()) {
                self::destroySession();
                $_SESSION['mensaje'] = 'Su sesión ha expirado. Por favor, inicie sesión nuevamente.';
            }
            header("Location: " . $redirect_url);
            exit();
        }
    }
    
    /**
     * Redireccionar si no tiene el rol requerido
     * @param string $required_role Rol requerido
     * @param string $redirect_url URL de redirección
     */
    public static function requireRole($required_role, $redirect_url = 'index.php') {
        self::requireLogin();
        
        if (!self::hasRole($required_role)) {
            $_SESSION['error'] = 'No tiene permisos para acceder a esta sección.';
            header("Location: " . $redirect_url);
            exit();
        }
    }
    
    /**
     * Redireccionar si no tiene alguno de los roles requeridos
     * @param array $roles Array de roles permitidos
     * @param string $redirect_url URL de redirección
     */
    public static function requireAnyRole($roles, $redirect_url = 'index.php') {
        self::requireLogin();
        
        if (!self::hasAnyRole($roles)) {
            $_SESSION['error'] = 'No tiene permisos para acceder a esta sección.';
            header("Location: " . $redirect_url);
            exit();
        }
    }
    
    /**
     * Establecer mensaje flash
     * @param string $message Mensaje
     * @param string $type Tipo de mensaje (success, error, warning, info)
     */
    public static function setFlashMessage($message, $type = 'info') {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
    }
    
    /**
     * Obtener y limpiar mensaje flash
     * @return array|null
     */
    public static function getFlashMessage() {
        if (isset($_SESSION['flash_message']) && isset($_SESSION['flash_type'])) {
            $message = [
                'message' => $_SESSION['flash_message'],
                'type' => $_SESSION['flash_type']
            ];
            
            unset($_SESSION['flash_message']);
            unset($_SESSION['flash_type']);
            
            return $message;
        }
        
        return null;
    }
    
    /**
     * Generar token CSRF
     * @return string
     */
    public static function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Verificar token CSRF
     * @param string $token Token a verificar
     * @return bool
     */
    public static function verifyCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}

// Inicializar sesión automáticamente
SessionManager::init();

?>