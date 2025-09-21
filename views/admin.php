<?php
session_start();

// Verificar si el usuario está logueado y es administrador
if (!isset($_SESSION['usuario_id']) || !$_SESSION['es_admin']) {
    header('Location: ../index.php');
    exit();
}

// Solo incluir el controlador - él maneja su configuración
require_once __DIR__ . '/../controllers/AdminController.php';

// Inicializar controlador
$adminController = new AdminController();

// Procesar logout
if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: ../index.php');
    exit();
}

// Routing GET
$action = $_GET['action'] ?? 'dashboard';
$id = $_GET['id'] ?? null;

// Procesar acciones POST
$mensaje = '';
$tipoMensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accion'])) {
        switch ($_POST['accion']) {
            case 'crear':
                if ($adminController->procesarCreacion()) {
                    $mensaje = 'Cupón creado exitosamente';
                    $tipoMensaje = 'success';
                    header('Location: ?action=list');
                    exit();
                } else {
                    $mensaje = 'Error al crear el cupón. Verifique los datos.';
                    $tipoMensaje = 'error';
                }
                break;
                
            case 'editar':
                if (isset($_POST['id']) && $adminController->procesarEdicion($_POST['id'])) {
                    $mensaje = 'Cupón actualizado exitosamente';
                    $tipoMensaje = 'success';
                    header('Location: ?action=list');
                    exit();
                } else {
                    $mensaje = 'Error al actualizar el cupón. Verifique los datos.';
                    $tipoMensaje = 'error';
                }
                break;
                
            case 'eliminar':
                if (isset($_POST['id']) && $adminController->procesarEliminacion($_POST['id'])) {
                    $mensaje = 'Cupón eliminado exitosamente';
                    $tipoMensaje = 'success';
                    header('Location: ?action=list');
                    exit();
                } else {
                    $mensaje = 'Error al eliminar el cupón.';
                    $tipoMensaje = 'error';
                }
                break;
        }
    }
}

// Variables para el contenido
$cupones = [];
$cupon = null;

// Procesar según la acción
switch ($action) {
    case 'list':
        $cupones = $adminController->obtenerCuponesFiltrados() ?? [];
        break;
        
    case 'create':
        // Formulario de creación
        break;
        
    case 'edit':
        if ($id) {
            $cupon = $adminController->obtenerCuponPorId($id);
            if (!$cupon) {
                header('Location: ?action=list');
                exit();
            }
        }
        break;
        
    case 'delete':
        if ($id && $adminController->procesarEliminacion($id)) {
            header('Location: ?action=list&mensaje=eliminado');
            exit();
        }
        break;
        
    default:
        $action = 'dashboard';
        break;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>25Watts - Panel Administrativo</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <!-- Mobile Overlay -->
        <div class="mobile-overlay" id="mobileOverlay"></div>
        
        <nav class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo">25Watts</div>
            </div>
            
            <div class="sidebar-nav">
                <a href="?action=dashboard" class="nav-item <?php echo $action === 'dashboard' ? 'active' : ''; ?>">
                    <i class="bi bi-house"></i>
                    <span>Inicio</span>
                </a>
                
                <a href="#" class="nav-item">
                    <i class="bi bi-people"></i>
                    <span>Usuarios</span>
                </a>
                
                <a href="#" class="nav-item">
                    <i class="bi bi-shield-check"></i>
                    <span>Roles</span>
                </a>
                
                <a href="?action=list" class="nav-item <?php echo in_array($action, ['list', 'create', 'edit']) ? 'active' : ''; ?>">
                    <i class="bi bi-gift"></i>
                    <span>Beneficios</span>
                </a>
                
                <form method="POST" style="margin: 0;">
                    <button type="submit" name="logout" class="nav-item" style="background: none; border: none; width: 100%; text-align: left; cursor: pointer;">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Cerrar Sesión</span>
                    </button>
                </form>
            </div>
        </nav>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <header class="header">
                <div class="header-left">
                    <button class="mobile-menu-toggle" id="mobileMenuToggle">
                        <i class="bi bi-list"></i>
                    </button>
                    <div class="header-logo">
                        <h1 class="logo-text">25Watts</h1>
                    </div>
                </div>
                <div class="header-actions">
                    <div class="dropdown">
                        <button class="notification-btn" id="notificationBtn">
                            <i class="bi bi-bell"></i>
                        </button>
                        <div class="dropdown-content" id="notificationsDropdown">
                            <div class="dropdown-header">
                                <h3 style="margin: 0; font-size: 18px; font-weight: 600; color: var(--text-primary);">Notificaciones</h3>
                            </div>
                            <div class="dropdown-body">
                                <p style="color: var(--text-secondary); text-align: center; padding: 20px;">No hay notificaciones nuevas</p>
                            </div>
                        </div>
                    </div>
                    <button class="theme-toggle-btn" id="themeToggleBtn">
                        <i class="bi bi-moon-fill"></i>
                    </button>
                </div>
            </header>

            <!-- Content -->
            <div class="content">
                <?php if (!empty($mensaje)): ?>
                    <div class="mensaje <?php echo $tipoMensaje; ?>">
                        <?php echo htmlspecialchars($mensaje); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($action === 'dashboard'): ?>
                    <!-- Vista Dashboard -->
                    <div class="dashboard-welcome">
                        <div class="dashboard-header">
                            <h2 class="dashboard-title">Te damos la bienvenida a Beneficios 25Watts</h2>
                            <div class="dashboard-version">ÚLTIMA VERSIÓN: 0.1.0</div>
                        </div>
                        <p class="dashboard-subtitle">Desde aquí podrás gestionar fácilmente los usuarios, configuraciones y más.</p>
                        
                        <div class="access-section">
                            <h3 class="access-title">Accesos</h3>
                            
                            <div class="access-grid">
                                <div class="access-card">
                                    <div class="access-icon"><i class="bi bi-people"></i></div>
                                    <h4 class="access-card-title">Roles</h4>
                                    <p class="access-card-description">Añadir y gestionar los roles de los usuarios</p>
                                    <a href="#" class="access-card-button">IR A ROLES <i class="bi bi-arrow-right"></i></a>
                                </div>
                                
                                <div class="access-card">
                                    <div class="access-icon"><i class="bi bi-person-gear"></i></div>
                                    <h4 class="access-card-title">Usuarios</h4>
                                    <p class="access-card-description">Añadir y gestionar los roles de los usuarios</p>
                                    <a href="#" class="access-card-button">IR A ROLES <i class="bi bi-arrow-right"></i></a>
                                </div>
                                
                                <div class="access-card">
                                    <div class="access-icon"><i class="bi bi-people"></i></div>
                                    <h4 class="access-card-title">Roles</h4>
                                    <p class="access-card-description">Añadir y gestionar los roles de los usuarios</p>
                                    <a href="#" class="access-card-button">IR A ROLES <i class="bi bi-arrow-right"></i></a>
                                </div>
                                
                                <div class="access-card">
                                    <div class="access-icon"><i class="bi bi-person-gear"></i></div>
                                    <h4 class="access-card-title">Usuarios</h4>
                                    <p class="access-card-description">Añadir y gestionar los roles de los usuarios</p>
                                    <a href="#" class="access-card-button">IR A ROLES <i class="bi bi-arrow-right"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                <?php elseif ($action === 'list'): ?>
                    <!-- Vista Lista de Beneficios -->
                    <div class="breadcrumb">
                        <a href="?action=dashboard">Inicio</a> • <span>Beneficios</span>
                    </div>

                    <!-- Buscador y botón crear -->
                    <div class="benefits-section-wrapper">
                        <div class="benefits-search-header">
                            <div class="search-container">
                                <div class="search-input-wrapper">
                                    <i class="bi bi-search search-icon"></i>
                                    <input type="text" placeholder="Buscador" class="search-input" id="searchField">
                                </div>
                            </div>
                            <a href="?action=create" class="btn create-btn">
                                CREAR NUEVO BENEFICIO
                            </a>
                        </div>

                        <!-- Filtros y botones de distribución -->
                        <div class="benefits-filters-header">
                            <div class="filters-container">
                                <div class="custom-dropdown">
                                    <button class="dropdown-toggle" onclick="toggleDropdown('estadoDropdown')">
                                        ESTADO
                                        <i class="bi bi-chevron-up dropdown-arrow"></i>
                                    </button>
                                    <div class="dropdown-menu" id="estadoDropdown">
                                        <div class="dropdown-header">
                                            <span>Filtra por estados</span>
                                        </div>
                                        <div class="dropdown-options">
                                            <label class="checkbox-option active">
                                                <input type="checkbox" checked>
                                                <span class="checkmark"></span>
                                                <span class="option-text">Todos</span>
                                            </label>
                                            <label class="checkbox-option">
                                                <input type="checkbox">
                                                <span class="checkmark"></span>
                                                <span class="option-text">Activos</span>
                                            </label>
                                            <label class="checkbox-option">
                                                <input type="checkbox">
                                                <span class="checkmark"></span>
                                                <span class="option-text">Inactivos</span>
                                            </label>
                                            <label class="checkbox-option">
                                                <input type="checkbox">
                                                <span class="checkmark"></span>
                                                <span class="option-text">Canjeados</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="custom-dropdown">
                                    <button class="dropdown-toggle" onclick="toggleDropdown('ordenDropdown')">
                                        ORDENAR POR
                                        <i class="bi bi-chevron-down dropdown-arrow"></i>
                                    </button>
                                    <div class="dropdown-menu" id="ordenDropdown">
                                        <div class="dropdown-header">
                                            <span>Ordenar por</span>
                                        </div>
                                        <div class="dropdown-options">
                                            <label class="checkbox-option">
                                                <input type="checkbox">
                                                <span class="checkmark"></span>
                                                <span class="option-text">Fecha</span>
                                            </label>
                                            <label class="checkbox-option">
                                                <input type="checkbox">
                                                <span class="checkmark"></span>
                                                <span class="option-text">Nombre</span>
                                            </label>
                                            <label class="checkbox-option">
                                                <input type="checkbox">
                                                <span class="checkmark"></span>
                                                <span class="option-text">Valor</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="view-options">
                                <button class="view-btn active" onclick="cambiarVista('grid')" title="Vista de cuadrícula">
                                    <i class="bi bi-grid-3x3-gap"></i>
                                </button>
                                <button class="view-btn" onclick="cambiarVista('list')" title="Vista de lista">
                                    <i class="bi bi-list"></i>
                                </button>
                            </div>
                        </div>

                        <div class="benefits-table-container">
                            <table class="benefits-table">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Descripción</th>
                                        <th>Valor</th>
                                        <th>Fecha de Expiración</th>
                                        <th>Estado</th>
                                        <th>Usuario que Canjeó</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($cupones)): ?>
                                        <?php foreach ($cupones as $cupon_item): ?>
                                            <tr data-estado="<?php echo $cupon_item['estado']; ?>">
                                                <td>
                                                    <div class="coupon-title"><?php echo htmlspecialchars($cupon_item['codigo']); ?></div>
                                                </td>
                                                <td>
                                                    <div class="coupon-description"><?php echo htmlspecialchars($cupon_item['descripcion']); ?></div>
                                                </td>
                                                <td>
                                                    <div class="coupon-points"><?php echo $cupon_item['valor']; ?></div>
                                                </td>
                                                <td>
                                                    <div class="coupon-expiration"><?php echo date('d/m/Y', strtotime($cupon_item['fecha_expiracion'])); ?></div>
                                                </td>
                                                <td>
                                                    <span class="status-badge <?php 
                                                        if ($cupon_item['estado'] === 'activo') {
                                                            echo 'active';
                                                        } elseif ($cupon_item['estado'] === 'canjeado') {
                                                            echo 'redeemed';
                                                        } else {
                                                            echo 'inactive';
                                                        }
                                                    ?>">
                                                        <?php echo strtoupper($cupon_item['estado']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="user-canje"><?php echo htmlspecialchars($cupon_item['usuario_canje']); ?></div>
                                                </td>
                                                <td>
                                                    <div class="actions-cell">
                                                        <button class="action-btn edit" onclick="editarCupon(<?php echo $cupon_item['id']; ?>)" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="action-btn delete" onclick="eliminarCupon(<?php echo $cupon_item['id']; ?>, '<?php echo htmlspecialchars($cupon_item['codigo']); ?>')" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center">No hay cupones disponibles</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile Card View -->
                        <div class="benefits-mobile-cards">
                            <?php if (!empty($cupones)): ?>
                                <?php foreach ($cupones as $cupon_item): ?>
                                    <div class="benefit-card" data-estado="<?php echo $cupon_item['estado']; ?>">
                                        <div class="benefit-card-header">
                                            <div class="benefit-card-code"><?php echo htmlspecialchars($cupon_item['codigo']); ?></div>
                                            <div class="benefit-card-status">
                                                <span class="status-badge <?php 
                                                    if ($cupon_item['estado'] === 'activo') {
                                                        echo 'active';
                                                    } elseif ($cupon_item['estado'] === 'canjeado') {
                                                        echo 'redeemed';
                                                    } else {
                                                        echo 'inactive';
                                                    }
                                                ?>">
                                                    <?php echo strtoupper($cupon_item['estado']); ?>
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <div class="benefit-card-body">
                                            <div class="benefit-card-row">
                                                <div class="benefit-card-label">Descripción:</div>
                                                <div class="benefit-card-value"><?php echo htmlspecialchars($cupon_item['descripcion']); ?></div>
                                            </div>
                                            <div class="benefit-card-row">
                                                <div class="benefit-card-label">Valor:</div>
                                                <div class="benefit-card-value"><?php echo $cupon_item['valor']; ?></div>
                                            </div>
                                            <div class="benefit-card-row">
                                                <div class="benefit-card-label">Fecha de Expiración:</div>
                                                <div class="benefit-card-value"><?php echo date('d/m/Y', strtotime($cupon_item['fecha_expiracion'])); ?></div>
                                            </div>
                                            <?php if (!empty($cupon_item['usuario_canje'])): ?>
                                            <div class="benefit-card-row">
                                                <div class="benefit-card-label">Usuario que Canjeó:</div>
                                                <div class="benefit-card-value"><?php echo htmlspecialchars($cupon_item['usuario_canje']); ?></div>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="benefit-card-actions">
                                            <button class="action-btn edit" onclick="editarCupon(<?php echo $cupon_item['id']; ?>)" title="Editar">
                                                <i class="bi bi-pencil"></i>
                                                <span>Editar</span>
                                            </button>
                                            <button class="action-btn delete" onclick="eliminarCupon(<?php echo $cupon_item['id']; ?>, '<?php echo htmlspecialchars($cupon_item['codigo']); ?>')" title="Eliminar">
                                                <i class="bi bi-trash"></i>
                                                <span>Eliminar</span>
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="benefit-card">
                                    <div class="benefit-card-body">
                                        <div class="text-center">No hay cupones disponibles</div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="pagination">
                            <button class="pagination-btn" disabled><i class="bi bi-chevron-left"></i></button>
                            <button class="pagination-btn active">1</button>
                            <button class="pagination-btn">2</button>
                            <button class="pagination-btn">3</button>
                            <button class="pagination-btn">...</button>
                            <button class="pagination-btn"><i class="bi bi-chevron-right"></i></button>
                        </div>
                    </div>
                    
                <?php elseif ($action === 'create' || $action === 'edit'): ?>
                    <!-- Formulario de Beneficios -->
                    <div class="breadcrumb">
                        <a href="?action=dashboard">Inicio</a> • <a href="?action=list">Beneficios</a> • <?php echo $action === 'edit' ? 'Editar' : 'Crear'; ?> beneficio
                    </div>

                    <div class="benefits-section-wrapper">
                        <button class="btn-back" onclick="history.back()" title="Atrás">
                            <span class="back-arrow">←</span>
                            <span class="back-text">ATRÁS</span>
                        </button>
                        
                        <div class="page-header">
                            <div class="header-content">
                                <h2 class="page-title-section"><?php echo $action === 'edit' ? 'Beneficio: ' . ($cupon ? $cupon['valor'] . '%' : '') : 'Nuevo Beneficio'; ?></h2>
                                <p class="page-subtitle"><?php echo $action === 'edit' && $cupon ? htmlspecialchars($cupon['descripcion']) : 'Crear nuevo cupón de beneficio'; ?></p>
                            </div>
                            <?php if ($action === 'edit' && $cupon): ?>
                            <div class="header-right">
                                <div class="toggle-switch">
                                    <input type="checkbox" id="status-toggle" class="toggle-input" <?php echo $cupon['estado'] === 'activo' ? 'checked' : ''; ?> onchange="toggleStatus()">
                                    <label for="status-toggle" class="toggle-label">
                                        <span class="toggle-slider"></span>
                                        <span class="toggle-text-on">ON</span>
                                        <span class="toggle-text-off">OFF</span>
                                    </label>
                                </div>
                                <button class="btn-delete-benefit" onclick="confirmarEliminacion(<?php echo $cupon['id']; ?>, '<?php echo htmlspecialchars($cupon['codigo']); ?>')" title="Eliminar beneficio">
                                    ELIMINAR BENEFICIO
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>

                        <h4 class="form-section-title">Datos del cupón</h4>

                        <div class="form-section">
                            <form method="POST" class="coupon-form" id="coupon-form" enctype="multipart/form-data">
                                <input type="hidden" name="accion" value="<?php echo $action === 'edit' ? 'editar' : 'crear'; ?>">
                                <?php if ($action === 'edit' && $cupon): ?>
                                    <input type="hidden" name="id" value="<?php echo $cupon['id']; ?>">
                                <?php endif; ?>
                                
                                <div class="form-group">
                                    <h3 class="form-section-title-blue">Imagen del cupón</h3>
                                    <div class="image-upload-container">
                                        <div class="upload-area">
                                            <i class="bi bi-cloud-upload upload-icon"></i>
                                            <span class="upload-text">Seleccionar imagen</span>
                                        </div>
                                        <input type="file" name="imagen" accept="image/*" style="display: none;">
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="codigo" class="form-label">Nombre*</label>
                                        <input type="text" id="codigo" name="codigo" class="form-input" 
                                            placeholder="Nombre del cupón" 
                                            value="<?php echo $action === 'edit' && $cupon ? htmlspecialchars($cupon['codigo']) : ''; ?>" 
                                            required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="descripcion" class="form-label">Descripción*</label>
                                        <input id="descripcion" name="descripcion" class="form-input" 
                                                placeholder="Descripción detallada del beneficio" 
                                                required value="<?php echo $action === 'edit' && $cupon ? htmlspecialchars($cupon['descripcion']) : ''; ?>">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="estado" class="form-label">Estado*</label>
                                        <select id="estado" name="estado" class="form-select" required>
                                            <option value="">ORDENAR POR</option>
                                            <option value="activo" <?php echo ($action === 'edit' && $cupon && $cupon['estado'] === 'activo') ? 'selected' : ''; ?>>Activo</option>
                                            <option value="inactivo" <?php echo ($action === 'edit' && $cupon && $cupon['estado'] === 'inactivo') ? 'selected' : ''; ?>>Inactivo</option>
                                            <option value="canjeado" <?php echo ($action === 'edit' && $cupon && $cupon['estado'] === 'canjeado') ? 'selected' : ''; ?>>Canjeado</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="valor" class="form-label">Valor*</label>
                                        <input type="number" id="valor" name="valor" class="form-input" 
                                            placeholder="PUNTOS" 
                                            value="<?php echo $action === 'edit' && $cupon ? $cupon['valor'] : ''; ?>" 
                                            required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="fecha_expiracion" class="form-label">Fecha de Expiración*</label>
                                        <input type="date" id="fecha_expiracion" name="fecha_expiracion" class="form-input" 
                                            value="<?php echo $action === 'edit' && $cupon ? $cupon['fecha_expiracion'] : ''; ?>" 
                                            required>
                                    </div>
                                </div>
                                
                                    <h3 class="form-section-title-blue">Vista previa del cupón</h3>
                                    
                                    <div class="coupon-preview-new">
                                        <div class="preview-image-area">
                                            <!-- Campo gris rectangular para la imagen -->
                                        </div>
                                        <div class="preview-footer">
                                            <span class="preview-discount-new" id="preview-discount"><?php echo $action === 'edit' && $cupon ? $cupon['valor'] : '20'; ?>%</span>
                                            <span class="preview-service-new" id="preview-service"><?php echo $action === 'edit' && $cupon ? htmlspecialchars($cupon['descripcion']) : 'Servicio de estacionamiento'; ?></span>
                                            <span class="preview-points-new" id="preview-points">Valor: <?php echo $action === 'edit' && $cupon ? $cupon['valor'] : '120'; ?></span>
                                            <span class="preview-expiration-new" id="preview-expiration">Fecha de Expiración: <?php echo $action === 'edit' && $cupon ? date('d/m/Y', strtotime($cupon['fecha_expiracion'])) : date('d/m/Y', strtotime('+1 year')); ?></span>
                                            <span class="preview-status-new <?php echo ($action === 'edit' && $cupon && $cupon['estado'] === 'activo') ? 'active' : 'inactive'; ?>" id="preview-status">
                                                <?php echo ($action === 'edit' && $cupon) ? strtoupper($cupon['estado']) : 'ACTIVO'; ?>
                                            </span>
                                        </div>
                                    </div>

                                <div class="form-actions">
                                    <button type="button" class="btn btn-secondary" onclick="history.back()">CANCELAR</button>
                                    <button type="submit" class="btn btn-primary"><?php echo $action === 'edit' ? 'GUARDAR CAMBIOS' : 'GUARDAR CAMBIOS'; ?></button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/admin.js"></script>
</body>
</html>