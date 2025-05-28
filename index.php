<?php
/**
 * Dashboard Administrativo
 * Sistema de Ventas PC
 */

require_once '../config/config.php';
require_once '../config/session.php';
require_once '../config/database.php';

// Verificar login
SessionManager::requireLogin('../login.php');

// Obtener estadísticas básicas
$database = new Database();
$db = $database->getConnection();

try {
    // Total de productos
    $query = "SELECT COUNT(*) as total FROM productos WHERE activo = 1";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $total_productos = $stmt->fetch()['total'];

    // Total de categorías
    $query = "SELECT COUNT(*) as total FROM categorias";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $total_categorias = $stmt->fetch()['total'];

    // Total de marcas
    $query = "SELECT COUNT(*) as total FROM marcas";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $total_marcas = $stmt->fetch()['total'];

    // Productos con stock bajo
    $query = "SELECT COUNT(*) as total FROM productos WHERE stock <= " . STOCK_MINIMO_ALERTA . " AND activo = 1";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $productos_stock_bajo = $stmt->fetch()['total'];

    // Productos recientes
    $query = "SELECT p.nombre, c.nombre as categoria, m.nombre as marca, p.stock, p.precio, p.fecha_creacion
              FROM productos p
              LEFT JOIN categorias c ON p.id_categoria = c.id_categoria
              LEFT JOIN marcas m ON p.id_marca = m.id_marca
              WHERE p.activo = 1
              ORDER BY p.fecha_creacion DESC
              LIMIT 5";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $productos_recientes = $stmt->fetchAll();

    // Stock bajo
    $query = "SELECT p.nombre, c.nombre as categoria, p.stock
              FROM productos p
              LEFT JOIN categorias c ON p.id_categoria = c.id_categoria
              WHERE p.stock <= " . STOCK_MINIMO_ALERTA . " AND p.activo = 1
              ORDER BY p.stock ASC
              LIMIT 5";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stock_bajo = $stmt->fetchAll();

} catch (PDOException $e) {
    $total_productos = $total_categorias = $total_marcas = $productos_stock_bajo = 0;
    $productos_recientes = $stock_bajo = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            z-index: 1000;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        .stat-card {
            border: none;
            border-radius: 15px;
            transition: transform 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            border-radius: 8px;
            margin: 5px 15px;
            transition: all 0.3s ease;
        }
        .sidebar .nav-link:hover {
            color: white;
            background-color: rgba(255,255,255,0.1);
        }
        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255,255,255,0.2);
        }
        .navbar-brand {
            color: white !important;
            font-weight: bold;
        }
        .chart-container {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="p-3">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <i class="fas fa-desktop me-2"></i>
                TiendaPC Admin
            </a>
        </div>
        <ul class="nav nav-pills flex-column">
            <li class="nav-item">
                <a class="nav-link active" href="index.php">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="productos/">
                    <i class="fas fa-box me-2"></i>
                    Productos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="categorias/">
                    <i class="fas fa-tags me-2"></i>
                    Categorías
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="marcas/">
                    <i class="fas fa-building me-2"></i>
                    Marcas
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="ventas/">
                    <i class="fas fa-shopping-cart me-2"></i>
                    Ventas
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="clientes/">
                    <i class="fas fa-users me-2"></i>
                    Clientes
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="usuarios/">
                    <i class="fas fa-user-cog me-2"></i>
                    Usuarios
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="reportes/">
                    <i class="fas fa-chart-bar me-2"></i>
                    Reportes
                </a>
            </li>
        </ul>
        <div class="mt-auto p-3">
            <div class="dropdown">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                    <i class="fas fa-user-circle me-2"></i>
                    <?php echo SessionManager::getUserName(); ?>
                </a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="perfil.php"><i class="fas fa-user me-2"></i>Mi Perfil</a></li>
                    <li><a class="dropdown-item" href="configuracion.php"><i class="fas fa-cog me-2"></i>Configuración</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Dashboard</h1>
                <p class="text-muted">Bienvenido al panel administrativo</p>
            </div>
            <div class="text-end">
                <small class="text-muted">
                    <i class="fas fa-clock me-1"></i>
                    <?php echo formatear_fecha(date('Y-m-d H:i:s'), true); ?>
                </small>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card bg-primary text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="h4 mb-0"><?php echo formatear_numero($total_productos); ?></div>
                                <div class="small">Productos Activos</div>
                            </div>
                            <div class="h1">
                                <i class="fas fa-box"></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="productos/" class="text-white text-decoration-none">
                            <small>Ver detalles <i class="fas fa-arrow-right ms-1"></i></small>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card bg-success text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="h4 mb-0"><?php echo formatear_numero($total_categorias); ?></div>
                                <div class="small">Categorías</div>
                            </div>
                            <div class="h1">
                                <i class="fas fa-tags"></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="categorias/" class="text-white text-decoration-none">
                            <small>Gestionar <i class="fas fa-arrow-right ms-1"></i></small>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card bg-info text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="h4 mb-0"><?php echo formatear_numero($total_marcas); ?></div>
                                <div class="small">Marcas</div>
                            </div>
                            <div class="h1">
                                <i class="fas fa-building"></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="marcas/" class="text-white text-decoration-none">
                            <small>Gestionar <i class="fas fa-arrow-right ms-1"></i></small>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card bg-warning text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="h4 mb-0"><?php echo formatear_numero($productos_stock_bajo); ?></div>
                                <div class="small">Stock Bajo</div>
                            </div>
                            <div class="h1">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="productos/?filtro=stock_bajo" class="text-white text-decoration-none">
                            <small>Ver productos <i class="fas fa-arrow-right ms-1"></i></small>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Productos Recientes -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-clock me-2"></i>
                            Productos Agregados Recientemente
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($productos_recientes)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Producto</th>
                                            <th>Categoría</th>
                                            <th>Marca</th>
                                            <th>Stock</th>
                                            <th>Precio</th>
                                            <th>Fecha</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($productos_recientes as $producto): ?>
                                            <tr>
                                                <td class="fw-semibold"><?php echo htmlspecialchars($producto['nombre']); ?></td>
                                                <td>
                                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($producto['categoria'] ?? 'Sin categoría'); ?></span>
                                                </td>
                                                <td><?php echo htmlspecialchars($producto['marca'] ?? 'Sin marca'); ?></td>
                                                <td>
                                                    <span class="<?php echo obtener_clase_stock($producto['stock']); ?>">
                                                        <?php echo formatear_numero($producto['stock']); ?>
                                                    </span>
                                                </td>
                                                <td class="fw-semibold"><?php echo formatear_precio($producto['precio']); ?></td>
                                                <td>
                                                    <small class="text-muted">
                                                        <?php echo formatear_fecha($producto['fecha_creacion']); ?>
                                                    </small>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-box fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No hay productos registrados</p>
                                <a href="productos/agregar.php" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Agregar Primer Producto
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Stock Bajo -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-exclamation-triangle me-2 text-warning"></i>
                            Productos con Stock Bajo
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($stock_bajo)): ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($stock_bajo as $producto): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-start">
                                        <div class="ms-2 me-auto">
                                            <div class="fw-bold"><?php echo htmlspecialchars($producto['nombre']); ?></div>
                                            <small class="text-muted"><?php echo htmlspecialchars($producto['categoria'] ?? 'Sin categoría'); ?></small>
                                        </div>
                                        <span class="badge bg-danger rounded-pill">
                                            <?php echo $producto['stock']; ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="text-center mt-3">
                                <a href="productos/?filtro=stock_bajo" class="btn btn-outline-warning btn-sm">
                                    Ver todos
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-3">
                                <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                                <p class="text-muted mb-0">Todos los productos tienen stock suficiente</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Acciones Rápidas -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-bolt me-2"></i>
                            Acciones Rápidas
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="productos/agregar.php" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Agregar Producto
                            </a>
                            <a href="categorias/agregar.php" class="btn btn-outline-secondary">
                                <i class="fas fa-tags me-2"></i>Nueva Categoría
                            </a>
                            <a href="marcas/agregar.php" class="btn btn-outline-secondary">
                                <i class="fas fa-building me-2"></i>Nueva Marca
                            </a>
                            <a href="ventas/nueva.php" class="btn btn-success">
                                <i class="fas fa-shopping-cart me-2"></i>Nueva Venta
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>