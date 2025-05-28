<?php
/**
 * Archivo de prueba para verificar la configuración
 * Sistema de Ventas PC
 * 
 * IMPORTANTE: Eliminar este archivo en producción
 */

// Incluir archivos de configuración
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'config/session.php';
require_once 'includes/functions.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test de Configuración - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .test-item { margin-bottom: 1rem; }
        .status-ok { color: #28a745; }
        .status-error { color: #dc3545; }
        .status-warning { color: #ffc107; }
    </style>
</head>
<body>
    <div class="container my-5">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h2 class="mb-0">🔧 Test de Configuración del Sistema</h2>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning">
                            <strong>⚠️ Importante:</strong> Este archivo es solo para pruebas. 
                            Elimínalo antes de poner el sistema en producción.
                        </div>

                        <!-- Test 1: PHP Version -->
                        <div class="test-item">
                            <h5>1. Versión de PHP</h5>
                            <?php
                            $php_version = phpversion();
                            $php_ok = version_compare($php_version, '7.4.0', '>=');
                            $status_class = $php_ok ? 'status-ok' : 'status-error';
                            ?>
                            <p class="<?php echo $status_class; ?>">
                                <strong>Versión:</strong> <?php echo $php_version; ?>
                                <?php echo $php_ok ? '✅ OK' : '❌ ERROR (Se requiere PHP 7.4+)'; ?>
                            </p>
                        </div>

                        <!-- Test 2: Extensiones PHP -->
                        <div class="test-item">
                            <h5>2. Extensiones PHP Requeridas</h5>
                            <?php
                            $extensiones = ['pdo', 'pdo_mysql', 'gd', 'mbstring', 'json'];
                            foreach ($extensiones as $ext) {
                                $loaded = extension_loaded($ext);
                                $status_class = $loaded ? 'status-ok' : 'status-error';
                                echo "<p class='$status_class'><strong>$ext:</strong> " . 
                                     ($loaded ? '✅ Cargada' : '❌ No encontrada') . "</p>";
                            }
                            ?>
                        </div>

                        <!-- Test 3: Conexión a Base de Datos -->
                        <div class="test-item">
                            <h5>3. Conexión a Base de Datos</h5>
                            <?php
                            try {
                                $database = new Database();
                                $db = $database->getConnection();
                                
                                if ($db) {
                                    echo '<p class="status-ok"><strong>Conexión:</strong> ✅ Exitosa</p>';
                                    
                                    // Test de consulta
                                    $query = "SELECT COUNT(*) as total FROM categorias";
                                    $stmt = $db->prepare($query);
                                    $stmt->execute();
                                    $result = $stmt->fetch();
                                    
                                    echo '<p class="status-ok"><strong>Consulta Test:</strong> ✅ OK - ' . 
                                         $result['total'] . ' categorías encontradas</p>';
                                } else {
                                    echo '<p class="status-error"><strong>Conexión:</strong> ❌ Falló</p>';
                                }
                            } catch (Exception $e) {
                                echo '<p class="status-error"><strong>Error:</strong> ❌ ' . $e->getMessage() . '</p>';
                            }
                            ?>
                        </div>

                        <!-- Test 4: Configuración de Directorios -->
                        <div class="test-item">
                            <h5>4. Directorios y Permisos</h5>
                            <?php
                            $directorios = [
                                'uploads/' => UPLOAD_PATH,
                                'uploads/productos/' => UPLOAD_PATH . 'productos/',
                                'logs/' => ROOT_PATH . 'logs/'
                            ];
                            
                            foreach ($directorios as $nombre => $ruta) {
                                $existe = is_dir($ruta);
                                $escribible = $existe ? is_writable($ruta) : false;
                                
                                if ($existe && $escribible) {
                                    $status = 'status-ok';
                                    $mensaje = '✅ OK (Existe y escribible)';
                                } elseif ($existe && !$escribible) {
                                    $status = 'status-warning';
                                    $mensaje = '⚠️ Existe pero no es escribible';
                                } else {
                                    $status = 'status-error';
                                    $mensaje = '❌ No existe';
                                }
                                
                                echo "<p class='$status'><strong>$nombre:</strong> $mensaje</p>";
                            }
                            ?>
                        </div>

                        <!-- Test 5: Sesiones -->
                        <div class="test-item">
                            <h5>5. Sistema de Sesiones</h5>
                            <?php
                            $session_ok = session_status() === PHP_SESSION_ACTIVE;
                            $status_class = $session_ok ? 'status-ok' : 'status-error';
                            ?>
                            <p class="<?php echo $status_class; ?>">
                                <strong>Estado:</strong> <?php echo $session_ok ? '✅ Activa' : '❌ Inactiva'; ?>
                            </p>
                            <?php if ($session_ok): ?>
                                <p class="status-ok">
                                    <strong>ID de Sesión:</strong> <?php echo session_id(); ?>
                                </p>
                            <?php endif; ?>
                        </div>

                        <!-- Test 6: Funciones Personalizadas -->
                        <div class="test-item">
                            <h5>6. Funciones del Sistema</h5>
                            <?php
                            $funciones = [
                                'formatear_precio' => formatear_precio(1500.50),
                                'formatear_fecha' => formatear_fecha(date('Y-m-d H:i:s')),
                                'generar_codigo' => generar_codigo(6),
                                'validar_email' => validar_email('test@example.com') ? 'true' : 'false',
                                'calcular_iva' => formatear_precio(calcular_iva(100))
                            ];
                            
                            foreach ($funciones as $nombre => $resultado) {
                                echo "<p class='status-ok'><strong>$nombre():</strong> ✅ $resultado</p>";
                            }
                            ?>
                        </div>

                        <!-- Test 7: Constantes del Sistema -->
                        <div class="test-item">
                            <h5>7. Constantes Definidas</h5>
                            <?php
                            $constantes = [
                                'SITE_NAME' => SITE_NAME,
                                'SITE_URL' => SITE_URL,
                                'IVA_PORCENTAJE' => IVA_PORCENTAJE . '%',
                                'PRODUCTOS_POR_PAGINA' => PRODUCTOS_POR_PAGINA,
                                'MAX_FILE_SIZE' => formatear_bytes(MAX_FILE_SIZE)
                            ];
                            
                            foreach ($constantes as $nombre => $valor) {
                                echo "<p class='status-ok'><strong>$nombre:</strong> $valor</p>";
                            }
                            ?>
                        </div>

                        <!-- Test 8: Usuario Admin por Defecto -->
                        <div class="test-item">
                            <h5>8. Usuario Administrador</h5>
                            <?php
                            try {
                                $query = "SELECT username, nombre, rol FROM usuarios WHERE rol = 'admin' LIMIT 1";
                                $stmt = $db->prepare($query);
                                $stmt->execute();
                                $admin = $stmt->fetch();
                                
                                if ($admin) {
                                    echo '<p class="status-ok"><strong>Usuario Admin:</strong> ✅ Encontrado</p>';
                                    echo '<p class="status-ok"><strong>Username:</strong> ' . $admin['username'] . '</p>';
                                    echo '<p class="status-ok"><strong>Nombre:</strong> ' . $admin['nombre'] . '</p>';
                                } else {
                                    echo '<p class="status-error"><strong>Usuario Admin:</strong> ❌ No encontrado</p>';
                                }
                            } catch (Exception $e) {
                                echo '<p class="status-error"><strong>Error:</strong> ❌ ' . $e->getMessage() . '</p>';
                            }
                            ?>
                        </div>

                        <!-- Información del Sistema -->
                        <div class="test-item mt-4">
                            <h5>ℹ️ Información del Sistema</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <ul class="list-unstyled">
                                        <li><strong>Servidor:</strong> <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Desconocido'; ?></li>
                                        <li><strong>Sistema Operativo:</strong> <?php echo PHP_OS; ?></li>
                                        <li><strong>Zona Horaria:</strong> <?php echo date_default_timezone_get(); ?></li>
                                        <li><strong>Fecha/Hora:</strong> <?php echo formatear_fecha(date('Y-m-d H:i:s'), true); ?></li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <ul class="list-unstyled">
                                        <li><strong>Memoria PHP:</strong> <?php echo ini_get('memory_limit'); ?></li>
                                        <li><strong>Upload Max:</strong> <?php echo ini_get('upload_max_filesize'); ?></li>
                                        <li><strong>Max Execution:</strong> <?php echo ini_get('max_execution_time'); ?>s</li>
                                        <li><strong>Errores:</strong> <?php echo ini_get('display_errors') ? 'Mostrar' : 'Ocultar'; ?></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Próximos Pasos -->
                        <div class="alert alert-info mt-4">
                            <h5>🚀 Próximos Pasos</h5>
                            <ol>
                                <li>Si todos los tests son exitosos, puedes continuar con la <strong>Fase 2: Sistema de Autenticación</strong></li>
                                <li>Crea el archivo <code>login.php</code> en la raíz del proyecto</li>
                                <li>Implementa el modelo <code>models/Usuario.php</code></li>
                                <li>Configura las vistas básicas del sistema</li>
                            </ol>
                            <p class="mb-0">
                                <strong>⚠️ Recordatorio:</strong> 
                                <a href="#" onclick="return confirm('¿Estás seguro de que quieres eliminar este archivo de prueba?')">
                                    Elimina este archivo (test_conexion.php)
                                </a> antes de la producción.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>