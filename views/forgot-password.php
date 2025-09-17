<?php
session_start();

// Si ya está logueado, redirigir
if (isset($_SESSION['usuario_id']) && $_SESSION['usuario_id']) {
    header('Location: ../index.php');
    exit;
}

// Vista solo para fines visuales - sin funcionalidad real
$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'forgot') {
    // Solo mostrar mensaje visual sin procesar realmente
    $mensaje = 'Funcionalidad de recuperación de contraseña - Solo vista previa';
    $tipo_mensaje = 'info';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>25Watts - Recuperar Contraseña</title>
    <link rel="stylesheet" href="../assets/css/login.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="page-forgot">
    <!-- Header -->
    <header class="login-header">
        <div class="header-logo">LOGO</div>
        <div class="header-actions">
            <!-- Toggle de tema -->
            <button class="theme-toggle" onclick="toggleTheme()" title="Cambiar tema">
                <i class="bi bi-moon"></i>
            </button>
            
            <!-- Selector de idioma -->
            <div class="language-selector-wrapper">
                <button class="language-selector" onclick="toggleLanguageDropdown()">
                    <i class="bi bi-globe"></i>
                    <span>Esp</span>
                    <i class="bi bi-chevron-down"></i>
                </button>
                <div class="language-dropdown" id="languageDropdown">
                    <button onclick="changeLanguage('es')">Español</button>
                    <button onclick="changeLanguage('en')">English</button>
                </div>
            </div>
        </div>
    </header>

    <div class="login-container">
        <div class="login-card">
            <!-- Título -->
            <h2 class="login-title">RECUPERA TU CONTRASEÑA</h2>
            <p class="login-subtitle">Ingresa tu correo electrónico y te ayudaremos a recuperarla fácilmente</p>
            
            <!-- Mensaje de error/éxito -->
            <?php if ($mensaje): ?>
                <div class="message message-<?php echo $tipo_mensaje; ?>">
                    <?php echo htmlspecialchars($mensaje); ?>
                </div>
            <?php endif; ?>
            
            <!-- Formulario de recuperación -->
            <form class="login-form" method="POST" action="">
                <input type="hidden" name="action" value="forgot">
                
                <!-- Campo Email -->
                <div class="form-group">
                    <label for="email" class="form-label">Correo electrónico</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-input" 
                        placeholder="ejemplo@gmail.com"
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                        required
                    >
                </div>
                
                <!-- Botón de enviar -->
                <button type="submit" class="btn btn-primary">INICIAR SESIÓN</button>
                
                <!-- Enlace de vuelta al login -->
                <p class="register-text">
                    ¿Ya recuperaste tu contraseña? <a href="login.php" class="register-link">INICIAR SESIÓN</a>
                </p>
            </form>
        </div>
    </div>

    <script src="../assets/js/login.js"></script>
</body>
</html>