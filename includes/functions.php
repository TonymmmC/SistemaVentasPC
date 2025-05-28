<?php
/**
 * Funciones Básicas del Sistema
 * Sistema de Ventas PC
 */

// Incluir configuración
require_once __DIR__ . '/../config/config.php';

// ==================== FUNCIONES DE VALIDACIÓN ====================

/**
 * Validar email
 * @param string $email
 * @return bool
 */
function validar_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validar teléfono (formato boliviano)
 * @param string $telefono
 * @return bool
 */
function validar_telefono($telefono) {
    // Formato: 7XXXXXXX o 6XXXXXXX (celular) o 2XXXXXXX (fijo La Paz)
    $patron = '/^[267]\d{7}$/';
    return preg_match($patron, $telefono);
}

/**
 * Validar precio
 * @param mixed $precio
 * @return bool
 */
function validar_precio($precio) {
    return is_numeric($precio) && $precio >= 0;
}

/**
 * Validar stock
 * @param mixed $stock
 * @return bool
 */
function validar_stock($stock) {
    return is_numeric($stock) && $stock >= 0 && $stock == intval($stock);
}

/**
 * Validar imagen
 * @param array $archivo Array de $_FILES
 * @return bool
 */
function validar_imagen($archivo) {
    if ($archivo['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, ALLOWED_IMAGE_TYPES)) {
        return false;
    }
    
    if ($archivo['size'] > MAX_FILE_SIZE) {
        return false;
    }
    
    return true;
}

// ==================== FUNCIONES DE SANITIZACIÓN ====================

/**
 * Limpiar string
 * @param string $string
 * @return string
 */
function limpiar_string($string) {
    return trim(strip_tags($string));
}

/**
 * Sanitizar HTML
 * @param string $html
 * @return string
 */
function sanitizar_html($html) {
    return htmlspecialchars($html, ENT_QUOTES, 'UTF-8');
}

/**
 * Limpiar número
 * @param mixed $numero
 * @return float
 */
function limpiar_numero($numero) {
    return floatval(str_replace(',', '', $numero));
}

/**
 * Limpiar entero
 * @param mixed $entero
 * @return int
 */
function limpiar_entero($entero) {
    return intval($entero);
}

// ==================== FUNCIONES DE FORMATO ====================

/**
 * Formatear precio en bolivianos
 * @param float $precio
 * @return string
 */
function formatear_precio($precio) {
    return 'Bs. ' . number_format($precio, 2, ',', '.');
}

/**
 * Formatear fecha en español
 * @param string $fecha
 * @param bool $incluir_hora
 * @return string
 */
function formatear_fecha($fecha, $incluir_hora = false) {
    $meses = [
        '01' => 'enero', '02' => 'febrero', '03' => 'marzo', '04' => 'abril',
        '05' => 'mayo', '06' => 'junio', '07' => 'julio', '08' => 'agosto',
        '09' => 'septiembre', '10' => 'octubre', '11' => 'noviembre', '12' => 'diciembre'
    ];
    
    $timestamp = strtotime($fecha);
    $dia = date('d', $timestamp);
    $mes = $meses[date('m', $timestamp)];
    $anio = date('Y', $timestamp);
    
    $fecha_formateada = "$dia de $mes de $anio";
    
    if ($incluir_hora) {
        $hora = date('H:i', $timestamp);
        $fecha_formateada .= " a las $hora";
    }
    
    return $fecha_formateada;
}

/**
 * Formatear número con separadores
 * @param int $numero
 * @return string
 */
function formatear_numero($numero) {
    return number_format($numero, 0, ',', '.');
}

/**
 * Formatear bytes a unidades legibles
 * @param int $bytes
 * @return string
 */
function formatear_bytes($bytes) {
    $unidades = ['B', 'KB', 'MB', 'GB'];
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.2f %s", $bytes / pow(1024, $factor), $unidades[$factor]);
}

// ==================== FUNCIONES DE ARCHIVO/IMAGEN ====================

/**
 * Subir imagen de producto
 * @param array $archivo Array de $_FILES
 * @param string $directorio Directorio de destino
 * @return array Resultado de la operación
 */
function subir_imagen($archivo, $directorio = 'productos/') {
    $resultado = ['success' => false, 'mensaje' => '', 'archivo' => ''];
    
    if (!validar_imagen($archivo)) {
        $resultado['mensaje'] = 'Archivo de imagen no válido';
        return $resultado;
    }
    
    $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    $nombre_archivo = uniqid('img_') . '.' . $extension;
    $ruta_completa = UPLOAD_PATH . $directorio . $nombre_archivo;
    
    // Crear directorio si no existe
    create_directory_if_not_exists(UPLOAD_PATH . $directorio);
    
    if (move_uploaded_file($archivo['tmp_name'], $ruta_completa)) {
        // Redimensionar imagen si es necesario
        redimensionar_imagen($ruta_completa, THUMBNAIL_WIDTH, THUMBNAIL_HEIGHT);
        
        $resultado['success'] = true;
        $resultado['archivo'] = $directorio . $nombre_archivo;
        $resultado['mensaje'] = 'Imagen subida correctamente';
    } else {
        $resultado['mensaje'] = 'Error al subir la imagen';
    }
    
    return $resultado;
}

/**
 * Redimensionar imagen manteniendo proporción
 * @param string $ruta_imagen
 * @param int $ancho_max
 * @param int $alto_max
 * @return bool
 */
function redimensionar_imagen($ruta_imagen, $ancho_max, $alto_max) {
    $info = getimagesize($ruta_imagen);
    if (!$info) return false;
    
    $ancho_original = $info[0];
    $alto_original = $info[1];
    $tipo = $info[2];
    
    // Calcular nuevas dimensiones manteniendo proporción
    $ratio = min($ancho_max / $ancho_original, $alto_max / $alto_original);
    $nuevo_ancho = round($ancho_original * $ratio);
    $nuevo_alto = round($alto_original * $ratio);
    
    // Crear imagen desde archivo
    switch ($tipo) {
        case IMAGETYPE_JPEG:
            $imagen_original = imagecreatefromjpeg($ruta_imagen);
            break;
        case IMAGETYPE_PNG:
            $imagen_original = imagecreatefrompng($ruta_imagen);
            break;
        case IMAGETYPE_GIF:
            $imagen_original = imagecreatefromgif($ruta_imagen);
            break;
        default:
            return false;
    }
    
    // Crear nueva imagen redimensionada
    $imagen_nueva = imagecreatetruecolor($nuevo_ancho, $nuevo_alto);
    
    // Preservar transparencia para PNG y GIF
    if ($tipo == IMAGETYPE_PNG || $tipo == IMAGETYPE_GIF) {
        imagealphablending($imagen_nueva, false);
        imagesavealpha($imagen_nueva, true);
        $transparent = imagecolorallocatealpha($imagen_nueva, 255, 255, 255, 127);
        imagefilledrectangle($imagen_nueva, 0, 0, $nuevo_ancho, $nuevo_alto, $transparent);
    }
    
    imagecopyresampled($imagen_nueva, $imagen_original, 0, 0, 0, 0, 
                      $nuevo_ancho, $nuevo_alto, $ancho_original, $alto_original);
    
    // Guardar imagen redimensionada
    switch ($tipo) {
        case IMAGETYPE_JPEG:
            imagejpeg($imagen_nueva, $ruta_imagen, IMAGE_QUALITY);
            break;
        case IMAGETYPE_PNG:
            imagepng($imagen_nueva, $ruta_imagen);
            break;
        case IMAGETYPE_GIF:
            imagegif($imagen_nueva, $ruta_imagen);
            break;
    }
    
    imagedestroy($imagen_original);
    imagedestroy($imagen_nueva);
    
    return true;
}

/**
 * Eliminar archivo
 * @param string $ruta_archivo
 * @return bool
 */
function eliminar_archivo($ruta_archivo) {
    $ruta_completa = UPLOAD_PATH . $ruta_archivo;
    if (file_exists($ruta_completa)) {
        return unlink($ruta_completa);
    }
    return false;
}

// ==================== FUNCIONES DE UTILIDAD ====================

/**
 * Generar slug desde texto
 * @param string $texto
 * @return string
 */
function generar_slug($texto) {
    $texto = strtolower($texto);
    $texto = preg_replace('/[áàäâ]/u', 'a', $texto);
    $texto = preg_replace('/[éèëê]/u', 'e', $texto);
    $texto = preg_replace('/[íìïî]/u', 'i', $texto);
    $texto = preg_replace('/[óòöô]/u', 'o', $texto);
    $texto = preg_replace('/[úùüû]/u', 'u', $texto);
    $texto = preg_replace('/[ñ]/u', 'n', $texto);
    $texto = preg_replace('/[^a-z0-9\s-]/', '', $texto);
    $texto = preg_replace('/[\s-]+/', '-', $texto);
    return trim($texto, '-');
}

/**
 * Generar código aleatorio
 * @param int $longitud
 * @return string
 */
function generar_codigo($longitud = 8) {
    $caracteres = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $codigo = '';
    for ($i = 0; $i < $longitud; $i++) {
        $codigo .= $caracteres[rand(0, strlen($caracteres) - 1)];
    }
    return $codigo;
}

/**
 * Calcular IVA
 * @param float $monto
 * @return float
 */
function calcular_iva($monto) {
    return $monto * (IVA_PORCENTAJE / 100);
}

/**
 * Calcular subtotal con IVA incluido
 * @param float $precio_con_iva
 * @return float
 */
function calcular_subtotal($precio_con_iva) {
    return $precio_con_iva / (1 + (IVA_PORCENTAJE / 100));
}

/**
 * Calcular total con IVA
 * @param float $subtotal
 * @return float
 */
function calcular_total_con_iva($subtotal) {
    return $subtotal + calcular_iva($subtotal);
}

/**
 * Aplicar descuento
 * @param float $precio
 * @param float $descuento_porcentaje
 * @return float
 */
function aplicar_descuento($precio, $descuento_porcentaje) {
    if ($descuento_porcentaje > DESCUENTO_MAXIMO) {
        $descuento_porcentaje = DESCUENTO_MAXIMO;
    }
    return $precio * (1 - ($descuento_porcentaje / 100));
}

// ==================== FUNCIONES DE PAGINACIÓN ====================

/**
 * Calcular offset para paginación
 * @param int $pagina_actual
 * @param int $elementos_por_pagina
 * @return int
 */
function calcular_offset($pagina_actual, $elementos_por_pagina) {
    return ($pagina_actual - 1) * $elementos_por_pagina;
}

/**
 * Calcular total de páginas
 * @param int $total_elementos
 * @param int $elementos_por_pagina
 * @return int
 */
function calcular_total_paginas($total_elementos, $elementos_por_pagina) {
    return ceil($total_elementos / $elementos_por_pagina);
}

/**
 * Generar HTML de paginación
 * @param int $pagina_actual
 * @param int $total_paginas
 * @param string $url_base
 * @return string
 */
function generar_paginacion($pagina_actual, $total_paginas, $url_base) {
    if ($total_paginas <= 1) {
        return '';
    }
    
    $html = '<nav aria-label="Navegación de páginas">';
    $html .= '<ul class="pagination justify-content-center">';
    
    // Botón anterior
    if ($pagina_actual > 1) {
        $html .= '<li class="page-item">';
        $html .= '<a class="page-link" href="' . $url_base . '&pagina=' . ($pagina_actual - 1) . '">Anterior</a>';
        $html .= '</li>';
    }
    
    // Números de página
    $inicio = max(1, $pagina_actual - 2);
    $fin = min($total_paginas, $pagina_actual + 2);
    
    if ($inicio > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $url_base . '&pagina=1">1</a></li>';
        if ($inicio > 2) {
            $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }
    
    for ($i = $inicio; $i <= $fin; $i++) {
        $activo = ($i == $pagina_actual) ? ' active' : '';
        $html .= '<li class="page-item' . $activo . '">';
        $html .= '<a class="page-link" href="' . $url_base . '&pagina=' . $i . '">' . $i . '</a>';
        $html .= '</li>';
    }
    
    if ($fin < $total_paginas) {
        if ($fin < $total_paginas - 1) {
            $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
        $html .= '<li class="page-item"><a class="page-link" href="' . $url_base . '&pagina=' . $total_paginas . '">' . $total_paginas . '</a></li>';
    }
    
    // Botón siguiente
    if ($pagina_actual < $total_paginas) {
        $html .= '<li class="page-item">';
        $html .= '<a class="page-link" href="' . $url_base . '&pagina=' . ($pagina_actual + 1) . '">Siguiente</a>';
        $html .= '</li>';
    }
    
    $html .= '</ul>';
    $html .= '</nav>';
    
    return $html;
}

// ==================== FUNCIONES DE ESTADO ====================

/**
 * Obtener clase CSS para estado de venta
 * @param string $estado
 * @return string
 */
function obtener_clase_estado($estado) {
    switch ($estado) {
        case VENTA_PENDIENTE:
            return 'badge bg-warning text-dark';
        case VENTA_PROCESANDO:
            return 'badge bg-info';
        case VENTA_COMPLETADA:
            return 'badge bg-success';
        case VENTA_CANCELADA:
            return 'badge bg-danger';
        default:
            return 'badge bg-secondary';
    }
}

/**
 * Obtener texto amigable para estado de venta
 * @param string $estado
 * @return string
 */
function obtener_texto_estado($estado) {
    switch ($estado) {
        case VENTA_PENDIENTE:
            return 'Pendiente';
        case VENTA_PROCESANDO:
            return 'Procesando';
        case VENTA_COMPLETADA:
            return 'Completada';
        case VENTA_CANCELADA:
            return 'Cancelada';
        default:
            return 'Desconocido';
    }
}

/**
 * Verificar si el stock está bajo
 * @param int $stock_actual
 * @return bool
 */
function stock_bajo($stock_actual) {
    return $stock_actual <= STOCK_MINIMO_ALERTA;
}

/**
 * Obtener clase CSS para stock
 * @param int $stock_actual
 * @return string
 */
function obtener_clase_stock($stock_actual) {
    if ($stock_actual == 0) {
        return 'text-danger fw-bold';
    } elseif (stock_bajo($stock_actual)) {
        return 'text-warning fw-bold';
    } else {
        return 'text-success';
    }
}

// ==================== FUNCIONES DE COMPATIBILIDAD ====================

/**
 * Verificar compatibilidad entre productos
 * @param int $id_producto1
 * @param int $id_producto2
 * @param string $tipo_compatibilidad
 * @return bool
 */
function verificar_compatibilidad($id_producto1, $id_producto2, $tipo_compatibilidad) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "SELECT COUNT(*) as count FROM compatibilidades 
                  WHERE (id_producto_base = :id1 AND id_producto_compatible = :id2)
                     OR (id_producto_base = :id2 AND id_producto_compatible = :id1)
                  AND tipo_compatibilidad = :tipo";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id1', $id_producto1);
        $stmt->bindParam(':id2', $id_producto2);
        $stmt->bindParam(':tipo', $tipo_compatibilidad);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return $result['count'] > 0;
        
    } catch (PDOException $e) {
        log_message("Error verificando compatibilidad: " . $e->getMessage(), 'ERROR');
        return false;
    }
}

// ==================== FUNCIONES DE SEGURIDAD ====================

/**
 * Prevenir ataques XSS
 * @param string $data
 * @return string
 */
function prevenir_xss($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * Generar hash de contraseña
 * @param string $password
 * @return string
 */
function hash_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verificar contraseña
 * @param string $password
 * @param string $hash
 * @return bool
 */
function verificar_password($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Validar fuerza de contraseña
 * @param string $password
 * @return array
 */
function validar_fuerza_password($password) {
    $resultado = ['valida' => true, 'errores' => []];
    
    if (strlen($password) < 8) {
        $resultado['valida'] = false;
        $resultado['errores'][] = 'La contraseña debe tener al menos 8 caracteres';
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        $resultado['valida'] = false;
        $resultado['errores'][] = 'La contraseña debe contener al menos una letra mayúscula';
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        $resultado['valida'] = false;
        $resultado['errores'][] = 'La contraseña debe contener al menos una letra minúscula';
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        $resultado['valida'] = false;
        $resultado['errores'][] = 'La contraseña debe contener al menos un número';
    }
    
    return $resultado;
}

// ==================== FUNCIONES DE URL ====================

/**
 * Construir URL con parámetros
 * @param string $base_url
 * @param array $parametros
 * @return string
 */
function construir_url($base_url, $parametros = []) {
    if (empty($parametros)) {
        return $base_url;
    }
    
    $query_string = http_build_query($parametros);
    $separador = (strpos($base_url, '?') !== false) ? '&' : '?';
    
    return $base_url . $separador . $query_string;
}

/**
 * Redireccionar con mensaje
 * @param string $url
 * @param string $mensaje
 * @param string $tipo
 */
function redireccionar_con_mensaje($url, $mensaje, $tipo = 'info') {
    SessionManager::setFlashMessage($mensaje, $tipo);
    header("Location: $url");
    exit();
}

/**
 * Obtener URL actual
 * @return string
 */
function obtener_url_actual() {
    $protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    return $protocolo . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

?>