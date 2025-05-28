<?php
/**
 * Cerrar Sesión
 * Sistema de Ventas PC
 */

require_once 'config/config.php';
require_once 'config/session.php';

// Destruir sesión
SessionManager::destroySession();

// Redireccionar al login con mensaje
SessionManager::setFlashMessage('Sesión cerrada correctamente', 'success');
header("Location: login.php");
exit();
?>