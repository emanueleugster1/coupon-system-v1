<?php
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../index.php');
    exit();
}

// Verificar que no sea admin
if (isset($_SESSION['es_admin']) && $_SESSION['es_admin']) {
    header('Location: admin.php');
    exit();
}

// Solo incluir el controlador - él maneja su configuración
require_once __DIR__ . '/../controllers/UserController.php';

// Crear instancia del controlador (sin pasar $pdo)
$userController = new UserController();

// Variables para mensajes
$mensaje = '';
$tipo_mensaje = '';

// Procesar acciones GET
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'canjear_cupon':
            $codigo = $_GET['codigo'] ?? '';
            
            if (!empty($codigo)) {
                // Delegar toda la lógica al UserController
                $resultado = $userController->procesarCanje($codigo, $_SESSION['usuario_id']);
                $mensaje = $resultado['message'];
                $tipo_mensaje = $resultado['success'] ? 'success' : 'error';
            } else {
                $mensaje = 'Por favor, ingresa un código de cupón.';
                $tipo_mensaje = 'error';
            }
            
            // Redireccionar para evitar reenvío del formulario
            header('Location: usuario.php');
            exit();

        case 'logout':
            // Cerrar sesión
            session_destroy();
            header('Location: ../index.php');
            exit();
    }
}

// Procesar canje de cupón si se envió el formulario (mantener compatibilidad)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'canjear_cupon') {
        $codigo = $_POST['codigo'] ?? '';
        
        if (!empty($codigo)) {
            // Redireccionar a GET para mantener patrón MVC
            header('Location: usuario.php?action=canjear_cupon&codigo=' . urlencode($codigo));
            exit();
        } else {
            $mensaje = 'Por favor, ingresa un código de cupón.';
            $tipo_mensaje = 'error';
        }
    }
    
    // Endpoint AJAX para canje de cupón
    if ($_POST['action'] === 'canjear_cupon_ajax') {
        header('Content-Type: application/json');
        
        $codigo = $_POST['codigo'] ?? '';
        
        if (!empty($codigo)) {
            // Delegar toda la lógica al UserController
            $resultado = $userController->procesarCanje($codigo, $_SESSION['usuario_id']);
            echo json_encode($resultado);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Por favor, ingresa un código de cupón.'
            ]);
        }
        exit();
    }
}

// Obtener información del usuario desde la sesión
$user_data = [
    'id' => $_SESSION['usuario_id'],
    'nombre' => $_SESSION['usuario_nombre'],
    'apellido' => $_SESSION['usuario_apellido'], 
    'email' => $_SESSION['usuario_email'],
    'es_admin' => $_SESSION['es_admin'],
    'rol_nombre' => $_SESSION['rol_nombre']
];

// Obtener cupones activos para mostrar
$cupones_activos = $userController->obtenerCuponesActivos() ?? [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Usuario - Sistema de Cupones</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/usuario.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Mobile overlay -->
        <div class="mobile-overlay" id="mobileOverlay"></div>
        
        <!-- Sidebar -->
        <nav class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo">LOGO</div>
            </div>
            <div class="sidebar-nav">
                <button class="nav-item active" data-section="inicio">
                    <i class="bi bi-house"></i>
                    <span>Inicio</span>
                </button>
                <button class="nav-item" data-section="mi-cuenta">
                    <i class="bi bi-person"></i>
                    <span>Mi cuenta</span>
                </button>
                <button class="nav-item" data-section="mis-beneficios">
                    <i class="bi bi-gift"></i>
                    <span>Mis beneficios</span>
                </button>
                <button class="nav-item" data-section="mis-cupones">
                    <i class="bi bi-ticket"></i>
                    <span>Mis cupones</span>
                </button>
                <button class="nav-item" data-section="canjear-cupon">
                    <i class="bi bi-arrow-left-right"></i>
                    <span>Canjear Cupón</span>
                </button>
                <button class="nav-item" data-section="recomienda">
                    <i class="bi bi-share"></i>
                    <span>Recomendá</span>
                </button>
            </div>
            <div class="sidebar-footer">
                <div class="darkmode-toggle">
                    <span>Darkmode</span>
                    <button class="toggle-btn"><i class="bi bi-moon"></i></button>
                </div>
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
                        <h1 class="logo-text">LOGO</h1>
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
                                <button style="background: none; border: none; color: var(--brand-secondary); font-size: 14px; cursor: pointer; margin-top: 8px;">Marcar como leídas</button>
                            </div>
                            <div class="dropdown-actions" style="max-height: 300px; overflow-y: auto;">
                                <div class="notification-item">
                                    <div class="notification-icon">
                                        <i class="bi bi-ticket" style="color: var(--brand-secondary);"></i>
                                    </div>
                                    <div class="notification-content">
                                        <h4>Nuevo cupón disponible</h4>
                                        <p>20% OFF en tu próxima compra en la tienda asociada.</p>
                                        <div class="notification-time">5 min ago</div>
                                    </div>
                                </div>
                                <div class="notification-item">
                                    <div class="notification-icon">
                                        <i class="bi bi-unlock" style="color: var(--success-primary);"></i>
                                    </div>
                                    <div class="notification-content">
                                        <h4>Beneficio exclusivo desbloqueado</h4>
                                        <p>Accede antes que nadie a las rebajas de temporada.</p>
                                        <div class="notification-time">5 min ago</div>
                                    </div>
                                </div>
                                <div class="notification-item">
                                    <div class="notification-icon">
                                        <i class="bi bi-clock" style="color: var(--warning-primary);"></i>
                                    </div>
                                    <div class="notification-content">
                                        <h4>Este cupón está por vencer</h4>
                                        <p>Tienes hasta mañana para usar tu cupón de envío gratis.</p>
                                        <div class="notification-time">5 min ago</div>
                                    </div>
                                </div>
                                <button class="dropdown-btn" style="width: 100%; margin-top: 12px; justify-content: center;">
                                    <span>Ver todas</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="dropdown">
                        <button class="profile-btn" id="profileBtn">
                            <div class="profile-avatar">
                                <?php echo strtoupper(substr($user_data['nombre'], 0, 1)); ?>
                            </div>
                        </button>
                        <div class="dropdown-content" id="profileDropdown">
                            <div class="dropdown-header">
                                <div class="dropdown-avatar">
                                    <?php echo strtoupper(substr($user_data['nombre'], 0, 1)); ?>
                                </div>
                                <div class="dropdown-name"><?php echo htmlspecialchars($user_data['nombre'] . ' ' . $user_data['apellido']); ?></div>
                                <div class="dropdown-email"><?php echo htmlspecialchars($user_data['email']); ?></div>
                                <div class="dropdown-dni">DNI: 44 785 5698</div>
                            </div>
                            <div class="dropdown-actions">
                                <button class="dropdown-btn">
                                    <i class="bi bi-pencil"></i>
                                    <span>Editar perfil</span>
                                </button>
                                <button class="dropdown-btn" onclick="window.location.href='usuario.php?action=logout'">
                                    <i class="bi bi-box-arrow-right"></i>
                                    <span>Cerrar sesión</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <div class="content">
                <!-- Mostrar mensaje si existe -->
                <?php if (!empty($mensaje)): ?>
                    <div class="message <?php echo $tipo_mensaje; ?>">
                        <?php echo htmlspecialchars($mensaje); ?>
                    </div>
                <?php endif; ?>

                <!-- Sección Inicio -->
                <section id="inicio" class="content-section active">
                    <div class="welcome-section">
                        <div class="welcome-header">
                            <h1>Bienvenido, <?php echo htmlspecialchars($user_data['nombre'] . ' ' . $user_data['apellido']); ?></h1>
                            <div class="version-info">ÚLTIMA VERSIÓN: 0.1.0</div>
                        </div>
                        <p>Aquí se mostrará un resumen de tu actividad, saldo y beneficios destacados.</p>
                    </div>

                    <!-- Contenedor principal reorganizado -->
                    <div class="main-dashboard-container">
                        <!-- Lado izquierdo -->
                        <div class="left-content">
                            <!-- Estadísticas de puntos -->
                            <div class="points-section">
                                <div class="stat-card points-card">
                                    <div class="points-header">
                                        <span>MIS PUNTOS</span>
                                        <span>MI NIVEL</span>
                                    </div>
                                    <div class="points-values">
                                        <div class="points-value">100 puntos</div>
                                        <div class="level-value">Nivel 1</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Beneficios y Experiencias -->
                            <div class="benefits-section">
                                <div class="section-header">
                                    <h3>BENEFICIOS Y EXPERIENCIAS</h3>
                                    <a href="#" class="view-all">Ver todo</a>
                                </div>
                                <div class="benefits-grid">
                                    <?php if (!empty($cupones_activos)): ?>
                                        <?php foreach (array_slice($cupones_activos, 0, 4) as $cupon): ?>
                                            <div class="benefit-card">
                                                <div class="benefit-image">
                                                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='140' height='80' viewBox='0 0 140 80'%3E%3Crect width='140' height='80' fill='%23f0f0f0'/%3E%3Ctext x='70' y='45' text-anchor='middle' fill='%23666' font-size='12'%3EImagen%3C/text%3E%3C/svg%3E" alt="Beneficio">
                                                    <span class="benefit-badge">BENEFICIO</span>
                                                </div>
                                                <div class="benefit-info">
                                                    <p class="benefit-type">Tipo de descuento</p>
                                                    <p class="benefit-description"><?php echo htmlspecialchars(substr($cupon['descripcion'], 0, 50)); ?>...</p>
                                                    <button class="benefit-btn" data-codigo="<?php echo htmlspecialchars($cupon['codigo']); ?>">QUIERO EL CUPÓN</button>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p>No hay beneficios disponibles en este momento.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Lado derecho - Insignias -->
                        <div class="right-content">
                            <div class="insignias-vertical-card">
                                <span class="insignias-label">TUS INSIGNIAS</span>
                                <span class="insignias-count">0 de 4</span>
                                <div class="insignias-text">¿Quieres obtener 200 puntos?</div>
                                <div class="insignias-subtext">Registra 4 visitas en nuestras tiendas de Lorem Ipsum is a Met.</div>
                                <button class="btn-register">REGISTRAR</button>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Sección Mi Cuenta -->
                <section id="mi-cuenta" class="content-section">
                    <div class="welcome-section">
                        <div class="welcome-header">
                            <h1>Mi cuenta</h1>
                        </div>
                        <p>Aquí se mostrará un resumen de tu actividad, saldo y beneficios destacados.</p>
                    </div>

                    <!-- Pestañas de Mi Cuenta -->
                    <div class="account-tabs">
                        <button class="account-tab active">MI BALANCE</button>
                        <button class="account-tab">MIS MOVIMIENTOS</button>
                    </div>

                    <!-- Contenedor principal de Mi Cuenta -->
                    <div class="main-dashboard-container">
                        <!-- Lado izquierdo -->
                        <div class="left-content">
                            <!-- Tarjetas de estadísticas -->
                            <div class="account-stats-grid">
                                <div class="account-stat-card">
                                    <div class="account-stat-header">
                                        <span class="account-stat-label">MIS PUNTOS</span>
                                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                                            <circle cx="10" cy="10" r="8" stroke="#94A3B8" stroke-width="2" fill="none"/>
                                            <path d="M10 6v4l3 2" stroke="#94A3B8" stroke-width="2" stroke-linecap="round"/>
                                        </svg>
                                    </div>
                                    <div class="account-stat-value">100 puntos</div>
                                    <button class="account-action-btn">CANJEAR PUNTOS</button>
                                </div>
                                
                                <div class="account-stat-card">
                                    <div class="account-stat-header">
                                        <span class="account-stat-label">MI NIVEL</span>
                                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                                            <circle cx="10" cy="10" r="8" stroke="#94A3B8" stroke-width="2" fill="none"/>
                                            <path d="M10 6v4l3 2" stroke="#94A3B8" stroke-width="2" stroke-linecap="round"/>
                                        </svg>
                                    </div>
                                    <div class="account-stat-value">Nivel 1</div>
                                    <button class="account-action-btn">MIS BENEFICIOS</button>
                                </div>
                            </div>

                            <!-- Tarjeta de cupones -->
                            <div class="account-coupons-section">
                                <div class="account-coupons-card">
                                    <div class="account-coupons-header">
                                        <span class="account-coupons-label">MIS CUPONES</span>
                                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                                            <circle cx="10" cy="10" r="8" stroke="#94A3B8" stroke-width="2" fill="none"/>
                                            <path d="M10 6v4l3 2" stroke="#94A3B8" stroke-width="2" stroke-linecap="round"/>
                                        </svg>
                                    </div>
                                    <div class="account-coupons-value">0 cupones</div>
                                    <button class="account-action-btn-full">VER CUPONES DISPONIBLES</button>
                                </div>
                            </div>
                        </div>

                        <!-- Lado derecho - Insignias -->
                        <div class="right-content">
                            <div class="insignias-vertical-card">
                                <span class="insignias-label">TUS INSIGNIAS</span>
                                <span class="insignias-count">0 de 4</span>
                                <div class="insignias-text">¿Quieres obtener 200 puntos?</div>
                                <div class="insignias-subtext">Registra 4 visitas en nuestras tiendas de Lorem Ipsum a Met.</div>
                                <button class="btn-register">REGISTRAR</button>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Sección Mis Beneficios -->
                <section id="mis-beneficios" class="content-section">
                    <div class="page-header">
                        <h2>Mis beneficios</h2>
                        <p>Una galería con todos los beneficios disponibles para vos por ser parte de la plataforma.</p>
                    </div>
                    
                    <div class="benefits-categories">
                        <div class="category-card">
                            <div class="category-icon shopping">
                                <i class="bi bi-cart"></i>
                            </div>
                            <h3>Descuentos en compras</h3>
                            <p>Accede a rebajas exclusivas en cientos de tiendas asociadas.</p>
                        </div>
                        
                        <div class="category-card">
                            <div class="category-icon early-access">
                                <i class="bi bi-lightning"></i>
                            </div>
                            <h3>Acceso anticipado</h3>
                            <p>Sé el primero en enterarte y compra en lanzamientos especiales.</p>
                        </div>
                        
                        <div class="category-card">
                            <div class="category-icon events">
                                <i class="bi bi-calendar-event"></i>
                            </div>
                            <h3>Eventos especiales</h3>
                            <p>Recibí invitaciones a eventos y experiencias únicas.</p>
                        </div>
                    </div>
                </section>

                <!-- Sección Mis Cupones -->
                <section id="mis-cupones" class="content-section">
                    <div class="coupons-page-header">
                        <h2>Mis cupones</h2>
                        <p>Una galería con todos los beneficios disponibles para vos por ser parte de la plataforma.</p>
                    </div>
                    
                    <div class="coupons-tabs">
                        <button class="coupon-tab active">DISPONIBLES</button>
                        <button class="coupon-tab">CASH</button>
                        <button class="coupon-tab">USADOS</button>
                    </div>
                    
                    <div class="coupons-prototype-grid">
                        <div class="coupon-prototype-card">
                            <div class="coupon-brand-logo">
                                <svg width="60" height="40" viewBox="0 0 60 40" fill="none">
                                    <rect width="60" height="40" fill="#000"/>
                                    <text x="30" y="25" text-anchor="middle" fill="white" font-family="Arial, sans-serif" font-size="12" font-weight="bold">adidas</text>
                                </svg>
                            </div>
                            <div class="coupon-discount">
                                <span class="discount-number">20</span>
                                <span class="discount-percent">% OFF</span>
                            </div>
                            <div class="coupon-details">
                                <h3>En tiendas deportivas</h3>
                                <p>Válido en productos seleccionados</p>
                                <small>Vence: 31/12/2025</small>
                            </div>
                            <div class="coupon-badge">
                                <svg width="40" height="30" viewBox="0 0 40 30" fill="none">
                                    <rect width="40" height="30" rx="4" fill="#10B981"/>
                                    <rect x="8" y="8" width="24" height="14" rx="2" fill="white"/>
                                    <rect x="12" y="10" width="16" height="2" fill="#10B981"/>
                                    <rect x="12" y="14" width="12" height="2" fill="#10B981"/>
                                    <rect x="12" y="18" width="8" height="2" fill="#10B981"/>
                                </svg>
                                <span>ENVÍO GRATIS</span>
                            </div>
                            <button class="coupon-open-btn">ABRIR</button>
                        </div>
                        
                        <div class="coupon-prototype-card">
                            <div class="coupon-shipping-icon">
                                <svg width="60" height="40" viewBox="0 0 60 40" fill="none">
                                    <rect width="60" height="40" rx="8" fill="#10B981"/>
                                    <rect x="10" y="12" width="40" height="16" rx="2" fill="white"/>
                                    <rect x="15" y="16" width="30" height="2" fill="#10B981"/>
                                    <rect x="15" y="20" width="20" height="2" fill="#10B981"/>
                                    <rect x="15" y="24" width="15" height="2" fill="#10B981"/>
                                </svg>
                            </div>
                            <div class="coupon-shipping-text">
                                <h3>Envío gratis</h3>
                                <p>En compras online</p>
                                <p>Superando los $5000 en tu carrito.</p>
                                <small>Vence: 31/12/2025</small>
                            </div>
                            <button class="coupon-open-btn">ABRIR</button>
                        </div>
                        
                        <div class="coupon-prototype-card">
                            <div class="coupon-brand-logo">
                                <svg width="60" height="40" viewBox="0 0 60 40" fill="none">
                                    <rect width="60" height="40" fill="#000"/>
                                    <text x="30" y="25" text-anchor="middle" fill="white" font-family="Arial, sans-serif" font-size="12" font-weight="bold">adidas</text>
                                </svg>
                            </div>
                            <div class="coupon-discount">
                                <span class="discount-number">20</span>
                                <span class="discount-percent">% OFF</span>
                            </div>
                            <div class="coupon-details">
                                <h3>En tiendas deportivas</h3>
                                <p>Válido en productos seleccionados</p>
                                <small>Vence: 31/12/2025</small>
                            </div>
                            <div class="coupon-badge">
                                <svg width="40" height="30" viewBox="0 0 40 30" fill="none">
                                    <rect width="40" height="30" rx="4" fill="#10B981"/>
                                    <rect x="8" y="8" width="24" height="14" rx="2" fill="white"/>
                                    <rect x="12" y="10" width="16" height="2" fill="#10B981"/>
                                    <rect x="12" y="14" width="12" height="2" fill="#10B981"/>
                                    <rect x="12" y="18" width="8" height="2" fill="#10B981"/>
                                </svg>
                                <span>ENVÍO GRATIS</span>
                            </div>
                            <button class="coupon-open-btn">ABRIR</button>
                        </div>
                        
                        <div class="coupon-prototype-card">
                            <div class="coupon-shipping-icon">
                                <svg width="60" height="40" viewBox="0 0 60 40" fill="none">
                                    <rect width="60" height="40" rx="8" fill="#10B981"/>
                                    <rect x="10" y="12" width="40" height="16" rx="2" fill="white"/>
                                    <rect x="15" y="16" width="30" height="2" fill="#10B981"/>
                                    <rect x="15" y="20" width="20" height="2" fill="#10B981"/>
                                    <rect x="15" y="24" width="15" height="2" fill="#10B981"/>
                                </svg>
                            </div>
                            <div class="coupon-shipping-text">
                                <h3>Envío gratis</h3>
                                <p>En compras online</p>
                                <p>Superando los $5000 en tu carrito.</p>
                                <small>Vence: 31/12/2025</small>
                            </div>
                            <button class="coupon-open-btn">ABRIR</button>
                        </div>
                    </div>
                </section>

                <!-- Sección Recomienda -->
                <section id="recomienda" class="content-section">
                    <!-- Header de la sección -->
                    <div class="recommend-header">
                        <h2>Recomendá y ganá</h2>
                        <p>Compartí tu código de referido con amigos. Cuando se registren, ¡ambos reciben un beneficio!</p>
                    </div>

                    <!-- Código de referido -->
                    <div class="referral-code-container">
                        <div class="referral-code-box">
                            <span class="referral-code">JUAN - A478C</span>
                            <button class="copy-code-btn">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M16 1H4C2.9 1 2 1.9 2 3V17H4V3H16V1ZM19 5H8C6.9 5 6 5.9 6 7V21C6 22.1 6.9 23 8 23H19C20.1 23 21 22.1 21 21V7C21 5.9 20.1 5 19 5ZM19 21H8V7H19V21Z" fill="currentColor"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Cómo funciona -->
                    <div class="how-it-works">
                        <h3>¿Cómo funciona?</h3>
                        
                        <div class="steps-container">
                            <div class="step-item">
                                <div class="step-number">1</div>
                                <div class="step-content">
                                    <h4>COPIÁ TU CÓDIGO</h4>
                                    <p>Hacé clic en el ícono para copiar tu código único.</p>
                                </div>
                            </div>

                            <div class="step-item">
                                <div class="step-number">2</div>
                                <div class="step-content">
                                    <h4>COMPARTILO</h4>
                                    <p>Enviáselo a tus amigos por donde prefieras.</p>
                                </div>
                            </div>

                            <div class="step-item">
                                <div class="step-number">3</div>
                                <div class="step-content">
                                    <h4>AMBOS GANAN</h4>
                                    <p>Cuando tu amigo se registre, recibirá un cupón de bienvenida y vos también.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Sección Canjear Cupón -->
                <section id="canjear-cupon" class="content-section">
                    <h2>Canjear Cupón</h2>
                    <div class="redeem-section">
                        <form method="POST" class="coupon-form" id="couponForm">
                            <input type="hidden" name="action" value="canjear_cupon">
                            <div class="form-group">
                                <label for="codigo">Código del Cupón:</label>
                                <input type="text" id="codigo" name="codigo" placeholder="Ingresa tu código" required>
                            </div>
                            <button type="submit" class="redeem-btn">CANJEAR CUPÓN</button>
                        </form>
                        <div id="couponMessage"></div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <!-- Notifications Modal -->

    <!-- Modal de Cupón -->
    <div id="couponModal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-icon" id="modalIcon"></div>
            <h3 id="modalTitle">Título</h3>
            <p id="modalMessage">Mensaje</p>
            <button id="modalAcceptBtn" class="modal-btn">ACEPTAR</button>
        </div>
    </div>

    <script src="../assets/js/usuario.js"></script>
</body>
</html>