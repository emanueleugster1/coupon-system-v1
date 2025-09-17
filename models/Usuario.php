<?php
class Usuario {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    // Validar credenciales - FUNCIÓN BÁSICA (retorna TODO)
    public function validarCredenciales($email, $password) {
        // Sanitización básica
        $email = trim(strip_tags($email)); // No convertir a lowercase para permitir usernames
        $password = trim($password); // No strip_tags en password
        
        // Validaciones básicas - solo verificar que tengan contenido
        if (empty($email) || empty($password)) {
            return false;
        }
        
        try {
            // SELECT usuario con JOIN a roles - buscar por email exacto
            $stmt = $this->pdo->prepare("
                SELECT u.id, u.email, u.nombre, u.apellido, u.password, u.estado,
                       r.nombre as rol_nombre, r.es_admin
                FROM usuarios u 
                JOIN roles r ON u.rol_id = r.id 
                WHERE u.email = ? AND u.estado = 'activo'
            ");
            $stmt->execute([$email]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$usuario) {
                return false;
            }
            
            // Verificar password con PASSWORD() de MySQL
            // Como setup.sql usa PASSWORD(), necesitamos verificar de forma diferente
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) as valido 
                FROM usuarios 
                WHERE email = ? AND password = PASSWORD(?)
            ");
            $stmt->execute([$email, $password]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($resultado['valido'] == 1) {
                // Quitar el campo password antes de retornar
                unset($usuario['password']);
                return $usuario;
            }
            
            return false;
            
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>