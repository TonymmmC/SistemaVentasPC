<?php
/**
 * Gestión de Marcas
 * Sistema de Ventas PC
 */

require_once '../../config/config.php';
require_once '../../config/session.php';
require_once '../../config/database.php';

SessionManager::requireLogin('../../login.php');

$database = new Database();
$db = $database->getConnection();

// Procesamiento de acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accion'])) {
        switch ($_POST['accion']) {
            case 'agregar':
                $nombre = trim($_POST['nombre']);
                $descripcion = trim($_POST['descripcion']);
                
                if (!empty($nombre)) {
                    $query = "INSERT INTO marcas (nombre, descripcion) VALUES (:nombre, :descripcion)";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':nombre', $nombre);
                    $stmt->bindParam(':descripcion', $descripcion);
                    
                    if ($stmt->execute()) {
                        SessionManager::setFlashMessage('Marca agregada correctamente', 'success');
                    } else {
                        SessionManager::setFlashMessage('Error al agregar la marca', 'error');
                    }
                }
                break;
                
            case 'editar':
                $id = $_POST['id_marca'];
                $nombre = trim($_POST['nombre']);
                $descripcion = trim($_POST['descripcion']);
                
                if (!empty($nombre) && !empty($id)) {
                    $query = "UPDATE marcas SET nombre = :nombre, descripcion = :descripcion WHERE id_marca = :id";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':nombre', $nombre);
                    $stmt->bindParam(':descripcion', $descripcion);
                    $stmt->bindParam(':id', $id);
                    
                    if ($stmt->execute()) {
                        SessionManager::setFlashMessage('Marca actualizada correctamente', 'success');
                    } else {
                        SessionManager::setFlashMessage('Error al actualizar la marca', 'error');
                    }
                }
                break;
                
            case 'eliminar':
                $id = $_POST['id_marca'];
                
                // Verificar si tiene productos asociados
                $query = "SELECT COUNT(*) as total FROM productos WHERE id_marca = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                $total_productos = $stmt->fetch()['total'];
                
                if ($total_productos > 0) {
                    SessionManager::setFlashMessage('No se puede eliminar la marca porque tiene productos asociados', 'error');
                } else {
                    $query = "DELETE FROM marcas WHERE id_marca = :id";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':id', $id);
                    
                    if ($stmt->execute()) {
                        SessionManager::setFlashMessage('Marca eliminada correctamente', 'success');
                    } else {
                        SessionManager::setFlashMessage('Error al eliminar la marca', 'error');
                    }
                }
                break;
        }
        
        header("Location: index.php");
        exit();
    }
}

// Obtener marcas
$query = "SELECT m.*, COUNT(p.id_producto) as total_productos 
          FROM marcas m 
          LEFT JOIN productos p ON m.id_marca = p.id_marca 
          GROUP BY m.id_marca 
          ORDER BY m.nombre";
$stmt = $db->prepare($query);
$stmt->execute();
$marcas = $stmt->fetchAll();

$flash = SessionManager::getFlashMessage();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marcas - <?php echo SITE_NAME; ?></title>
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
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="p-3">
            <a class="navbar-brand d-flex align-items-center" href="../index.php">
                <i class="fas fa-desktop me-2"></i>
                TiendaPC Admin
            </a>
        </div>
        <ul class="nav nav-pills flex-column">
            <li class="nav-item">
                <a class="nav-link" href="../index.php">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../productos/">
                    <i class="fas fa-box me-2"></i>
                    Productos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../categorias/">
                    <i class="fas fa-tags me-2"></i>
                    Categorías
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="index.php">
                    <i class="fas fa-building me-2"></i>
                    Marcas
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../ventas/">
                    <i class="fas fa-shopping-cart me-2"></i>
                    Ventas
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../clientes/">
                    <i class="fas fa-users me-2"></i>
                    Clientes
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../usuarios/">
                    <i class="fas fa-user-cog me-2"></i>
                    Usuarios
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
                    <li><a class="dropdown-item" href="../../logout.php"><i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Gestión de Marcas</h1>
                <p class="text-muted">Administra las marcas de productos</p>
            </div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAgregar">
                <i class="fas fa-plus me-2"></i>Nueva Marca
            </button>
        </div>

        <?php if ($flash): ?>
            <div class="alert alert-<?php echo $flash['type'] === 'error' ? 'danger' : $flash['type']; ?> alert-dismissible fade show">
                <?php echo htmlspecialchars($flash['message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th>Productos</th>
                                <th>Fecha Creación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($marcas as $marca): ?>
                                <tr>
                                    <td><?php echo $marca['id_marca']; ?></td>
                                    <td class="fw-semibold"><?php echo htmlspecialchars($marca['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($marca['descripcion'] ?? ''); ?></td>
                                    <td>
                                        <span class="badge bg-primary"><?php echo $marca['total_productos']; ?></span>
                                    </td>
                                    <td><?php echo formatear_fecha($marca['fecha_creacion']); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" 
                                                onclick="editarMarca(<?php echo htmlspecialchars(json_encode($marca)); ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <?php if ($marca['total_productos'] == 0): ?>
                                            <button class="btn btn-sm btn-outline-danger" 
                                                    onclick="eliminarMarca(<?php echo $marca['id_marca']; ?>, '<?php echo htmlspecialchars($marca['nombre']); ?>')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal Agregar Marca -->
    <div class="modal fade" id="modalAgregar" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Nueva Marca</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="accion" value="agregar">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre *</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                        </div>
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Marca -->
    <div class="modal fade" id="modalEditar" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Editar Marca</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="accion" value="editar">
                        <input type="hidden" name="id_marca" id="edit_id">
                        <div class="mb-3">
                            <label for="edit_nombre" class="form-label">Nombre *</label>
                            <input type="text" class="form-control" id="edit_nombre" name="nombre" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="edit_descripcion" name="descripcion" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Eliminar -->
    <div class="modal fade" id="modalEliminar" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Eliminar Marca</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="accion" value="eliminar">
                        <input type="hidden" name="id_marca" id="delete_id">
                        <p>¿Está seguro que desea eliminar la marca <strong id="delete_nombre"></strong>?</p>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Esta acción no se puede deshacer.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Eliminar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editarMarca(marca) {
            document.getElementById('edit_id').value = marca.id_marca;
            document.getElementById('edit_nombre').value = marca.nombre;
            document.getElementById('edit_descripcion').value = marca.descripcion || '';
            
            const modal = new bootstrap.Modal(document.getElementById('modalEditar'));
            modal.show();
        }

        function eliminarMarca(id, nombre) {
            document.getElementById('delete_id').value = id;
            document.getElementById('delete_nombre').textContent = nombre;
            
            const modal = new bootstrap.Modal(document.getElementById('modalEliminar'));
            modal.show();
        }
    </script>
</body>
</html>