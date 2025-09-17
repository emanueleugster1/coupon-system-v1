<?php
class AuthController {
    private $pdo;
    private $usuario;
    
    public function __construct() {
        // El controlador maneja su propia configuración
        $this->pdo = require_once __DIR__ . '/../config/config.php';
        
        require_once __DIR__ . '/../models/Usuario.php';
        $this->usuario = new Usuario($this->pdo);
    }
    
    // Login completo - MÉTODO SIMPLE (hace todo el proceso)
    public function login($email, $password) {
        // 1. Sanitizar email y password básico
        $email = trim(strtolower(strip_tags($email)));
        $password = trim($password);
        
        // Validaciones básicas - solo campos no vacíos
        if (empty($email)) {
            return ['success' => false, 'message' => 'Ingrese un email'];
        }
        
        if (empty($password)) {
            return ['success' => false, 'message' => 'Ingrese una contraseña'];
        }
        
        // 2. Llamar Usuario::validarCredenciales()
        $usuario = $this->usuario->validarCredenciales($email, $password);
        
        if (!$usuario) {
            return ['success' => false, 'message' => 'Email o contraseña incorrectos'];
        }
        
        // 3. Si válido: iniciar session_start() y guardar en $_SESSION
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_email'] = $usuario['email'];
        $_SESSION['usuario_nombre'] = $usuario['nombre'];
        $_SESSION['usuario_apellido'] = $usuario['apellido'];
        $_SESSION['es_admin'] = $usuario['es_admin'];
        $_SESSION['rol_nombre'] = $usuario['rol_nombre'];
        
        // 4. Retornar resultado con datos del usuario (SIN redirecciones, SIN echo)
        return [
            'success' => true,
            'message' => 'Login exitoso',
            'usuario' => [
                'id' => $usuario['id'],
                'email' => $usuario['email'],
                'nombre' => $usuario['nombre'] . ' ' . $usuario['apellido'],
                'es_admin' => $usuario['es_admin'],
                'rol' => $usuario['rol_nombre']
            ]
        ];
    }
    
    // Cerrar sesión - MÉTODO SIMPLE
    public function logout() {
        // session_start() y session_destroy()
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        // Limpiar todas las variables de sesión
        $_SESSION = [];
        
        // Destruir la cookie de sesión
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Destruir la sesión
        session_destroy();
        
        // Retornar confirmación
        return ['success' => true, 'message' => 'Sesión cerrada exitosamente'];
    }
    
}
?>