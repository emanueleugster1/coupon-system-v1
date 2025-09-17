<?php
session_start();

// Si ya está logueado, redirigir al index
if (isset($_SESSION['usuario_id']) && $_SESSION['usuario_id']) {
    header('Location: ../index.php');
    exit;
}

// Procesar login si se envió el formulario
$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    // Solo incluir el controlador - él maneja su configuración
    require_once __DIR__ . '/../controllers/AuthController.php';
    
    $authController = new AuthController();
    $resultado = $authController->login($_POST['email'], $_POST['password']);
    
    if ($resultado['success']) {
        // Redirigir al index para que maneje la redirección según el rol
        header('Location: ../index.php');
        exit;
    } else {
        $mensaje = $resultado['message'];
        $tipo_mensaje = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>25Watts - Iniciar Sesión</title>
    <link rel="stylesheet" href="../assets/css/login.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="page-login">
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
            <h2 class="login-title">INICIAR SESIÓN</h2>
            <p class="login-subtitle">Ingresa tu sesión escribiendo tu correo electrónico y contraseña.</p>
            
            <!-- Mensaje de error/éxito -->
            <?php if ($mensaje): ?>
                <div class="message message-<?php echo $tipo_mensaje; ?>">
                    <?php echo htmlspecialchars($mensaje); ?>
                </div>
            <?php endif; ?>
            
            <!-- Formulario de login -->
            <form class="login-form" method="POST" action="">
                <input type="hidden" name="action" value="login">
                
                <!-- Campo Email -->
                <div class="form-group">
                    <label for="email" class="form-label">Correo electrónico</label>
                    <input 
                        type="text" 
                        id="email" 
                        name="email" 
                        class="form-input" 
                        placeholder="ejemplo@gmail.com"
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                        required
                    >
                </div>
                
                <!-- Campo Contraseña -->
                <div class="form-group">
                    <label for="password" class="form-label">Contraseña</label>
                    <div class="input-group">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="form-input" 
                            placeholder="••••••••••••"
                            required
                        >
                        <button type="button" class="input-toggle" onclick="togglePassword()">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Recordarme y Olvidaste contraseña -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px;">
                    <div class="form-checkbox-group" style="margin-bottom: 0;">
                        <input type="checkbox" id="remember" name="remember" class="form-checkbox">
                        <label for="remember" class="checkbox-label">Recuérdame</label>
                    </div>
                    <a href="forgot-password.php" class="form-link">¿Olvidaste tu contraseña?</a>
                </div>
                
                <!-- Botón de login -->
                <button type="submit" class="btn btn-primary">INICIAR SESIÓN</button>
                
                <!-- Enlace de registro -->
                <p class="register-text">
                    ¿No tienes una cuenta? <a href="#" class="register-link">REGÍSTRAME</a>
                </p>
            </form>
        </div>
    </div>

    <script src="../assets/js/login.js"></script>
</body>
</html>