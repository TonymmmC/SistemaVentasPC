<?php
/**
 * SessionManager - Gestión de Sesiones
 * Sistema de Ventas PC
 */

class SessionManager {
    
    public static function init() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    public static function isLoggedIn() {
        return isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']);
    }
    
    public static function getUserId() {
        return self::isLoggedIn() ? $_SESSION['usuario_id'] : null;
    }
    
    public static function getUserRole() {
        return self::isLoggedIn() ? $_SESSION['usuario_rol'] : null;
    }
    
    public static function getUserName() {
        return self::isLoggedIn() ? $_SESSION['usuario_nombre'] : null;
    }
    
    public static function createSession($user_data) {
        $_SESSION['usuario_id'] = $user_data['id_usuario'];
        $_SESSION['username'] = $user_data['username'];
        $_SESSION['usuario_nombre'] = $user_data['nombre'];
        $_SESSION['usuario_email'] = $user_data['email'];
        $_SESSION['usuario_rol'] = $user_data['rol'];
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        
        session_regenerate_id(true);
    }
    
    public static function destroySession() {
        $_SESSION = array();
        
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        session_destroy();
    }
    
    public static function isSessionExpired() {
        if (!self::isLoggedIn()) {
            return true;
        }
        
        $timeout = 3600;
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
            return true;
        }
        
        $_SESSION['last_activity'] = time();
        return false;
    }
    
    public static function hasRole($required_role) {
        if (!self::isLoggedIn()) {
            return false;
        }
        
        $user_role = self::getUserRole();
        
        if ($user_role === 'admin') {
            return true;
        }
        
        return $user_role === $required_role;
    }
    
    public static function isAdmin() {
        return self::hasRole('admin');
    }
    
    public static function requireLogin($redirect_url = 'login.php') {
        if (!self::isLoggedIn() || self::isSessionExpired()) {
            if (self::isSessionExpired()) {
                self::destroySession();
            }
            header("Location: " . $redirect_url);
            exit();
        }
    }
    
    public static function requireRole($required_role, $redirect_url = 'index.php') {
        self::requireLogin();
        
        if (!self::hasRole($required_role)) {
            header("Location: " . $redirect_url);
            exit();
        }
    }
    
    public static function setFlashMessage($message, $type = 'info') {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
    }
    
    public static function getFlashMessage() {
        if (isset($_SESSION['flash_message'])) {
            $message = [
                'message' => $_SESSION['flash_message'],
                'type' => $_SESSION['flash_type'] ?? 'info'
            ];
            
            unset($_SESSION['flash_message']);
            unset($_SESSION['flash_type']);
            
            return $message;
        }
        
        return null;
    }
    
    public static function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    public static function verifyCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}

// Inicializar sesión
SessionManager::init();
?>