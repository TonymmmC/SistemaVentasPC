<?php
/**
 * Configuración General del Sistema
 * Sistema de Ventas PC
 */

// Prevenir acceso directo al archivo
if (!defined('SISTEMA_VENTAS_PC')) {
    define('SISTEMA_VENTAS_PC', true);
}

// ==================== CONFIGURACIÓN DE ENTORNO ====================
// Cambiar a 'production' en servidor de producción
if (!defined('ENVIRONMENT')) {
    define('ENVIRONMENT', 'development'); // 'development' o 'production'
}

// ==================== CONFIGURACIÓN DEL SITIO ====================
define('SITE_URL', 'http://localhost/SistemaVentasPc/');
define('SITE_NAME', 'TiendaPC - Sistema de Ventas');
define('SITE_DESCRIPTION', 'Sistema de ventas de computadoras y componentes');
define('SITE_KEYWORDS', 'computadoras, componentes, PC, ventas, hardware');

// ==================== CONFIGURACIÓN DE RUTAS ====================
define('ROOT_PATH', $_SERVER['DOCUMENT_ROOT'] . '/SistemaVentasPc/');
define('UPLOAD_PATH', ROOT_PATH . 'uploads/');
define('ASSETS_PATH', ROOT_PATH . 'assets/');
define('INCLUDES_PATH', ROOT_PATH . 'includes/');
define('CONFIG_PATH', ROOT_PATH . 'config/');

// URLs públicas
define('UPLOAD_URL', SITE_URL . 'uploads/');
define('ASSETS_URL', SITE_URL . 'assets/');

// ==================== CONFIGURACIÓN DE IMÁGENES ====================
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('THUMBNAIL_WIDTH', 300);
define('THUMBNAIL_HEIGHT', 300);
define('IMAGE_QUALITY', 85);

// ==================== CONFIGURACIÓN DE PAGINACIÓN ====================
define('PRODUCTOS_POR_PAGINA', 12);
define('VENTAS_POR_PAGINA', 20);
define('CLIENTES_POR_PAGINA', 25);

// ==================== CONFIGURACIÓN DE SESIÓN ====================
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Cambiar a 1 en HTTPS
ini_set('session.cookie_lifetime', 3600); // 1 hora

// ==================== CONFIGURACIÓN DE ERRORES ====================
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
    ini_set('error_log', ROOT_PATH . 'logs/error.log');
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', ROOT_PATH . 'logs/error.log');
}

// ==================== ZONA HORARIA ====================
date_default_timezone_set('America/La_Paz');

// ==================== CONFIGURACIÓN DE EMAIL ====================
define('SMTP_HOST', 'localhost');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', '');
define('SMTP_PASSWORD', '');
define('FROM_EMAIL', 'noreply@tiendapc.com');
define('FROM_NAME', 'TiendaPC');

// ==================== CONFIGURACIÓN DE IMPUESTOS ====================
define('IVA_PORCENTAJE', 13); // 13% en Bolivia
define('DESCUENTO_MAXIMO', 50); // 50% descuento máximo

// ==================== CONFIGURACIÓN DE STOCK ====================
define('STOCK_MINIMO_ALERTA', 5);
define('MOSTRAR_AGOTADOS', false);

// ==================== ROLES DE USUARIO ====================
define('ROL_ADMIN', 'admin');
define('ROL_VENDEDOR', 'vendedor');
define('ROL_ALMACEN', 'almacen');

// ==================== ESTADOS DE VENTA ====================
define('VENTA_PENDIENTE', 'pendiente');
define('VENTA_PROCESANDO', 'procesando');
define('VENTA_COMPLETADA', 'completada');
define('VENTA_CANCELADA', 'cancelada');

// ==================== CONFIGURACIÓN DE CACHE ====================
define('CACHE_ENABLED', true);
define('CACHE_TIME', 3600); // 1 hora

// ==================== FUNCIÓN DE AUTOLOAD ====================
spl_autoload_register(function ($class_name) {
    $directories = [
        ROOT_PATH . 'models/',
        ROOT_PATH . 'controllers/',
        ROOT_PATH . 'includes/',
        CONFIG_PATH
    ];
    
    foreach ($directories as $directory) {
        $file = $directory . $class_name . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// ==================== INICIAR SESIÓN ====================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==================== VARIABLES GLOBALES DE SESIÓN ====================
$usuario_logueado = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : null;
$usuario_rol = isset($_SESSION['usuario_rol']) ? $_SESSION['usuario_rol'] : null;
$usuario_nombre = isset($_SESSION['usuario_nombre']) ? $_SESSION['usuario_nombre'] : null;

// ==================== FUNCIONES DE UTILIDAD ====================

/**
 * Función para debug - solo en desarrollo
 */
function debug($data, $die = false) {
    if (ENVIRONMENT === 'development') {
        echo '<pre>';
        print_r($data);
        echo '</pre>';
        if ($die) die();
    }
}

/**
 * Función para logging personalizado
 */
function log_message($message, $level = 'INFO') {
    $log_file = ROOT_PATH . 'logs/app.log';
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[$timestamp] [$level] $message" . PHP_EOL;
    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
}

/**
 * Crear directorio si no existe
 */
function create_directory_if_not_exists($path) {
    if (!is_dir($path)) {
        mkdir($path, 0755, true);
    }
}

// Crear directorios necesarios
create_directory_if_not_exists(UPLOAD_PATH);
create_directory_if_not_exists(UPLOAD_PATH . 'productos/');
create_directory_if_not_exists(ROOT_PATH . 'logs/');

?>