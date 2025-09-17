<?php

class UserController {
    private $pdo;
    private $cupon;
    
    public function __construct() {
        // El controlador maneja su propia configuración
        $this->pdo = require_once __DIR__ . '/../config/config.php';
        
        require_once __DIR__ . '/../models/Cupon.php';
        $this->cupon = new Cupon($this->pdo);
    }
    
    // Procesar canje - MÉTODO SIMPLE
    public function procesarCanje($codigo, $usuario_id = null) {
        // Sanitizar código básico
        $codigo = strtoupper(trim(strip_tags($codigo)));
        $usuario_id = $usuario_id ? (int) $usuario_id : null;
        
        // Validar código no vacío
        if (empty($codigo)) {
            return ['success' => false, 'message' => 'Ingrese un código de cupón'];
        }
        
        // Llamar Cupon::canjear()
        $resultado = $this->cupon->canjear($codigo, $usuario_id);
        
        // Retornar resultado y mensaje (SIN redirecciones, SIN echo)
        return $resultado;
    }
    
    // Obtener cupones activos para mostrar al usuario - MÉTODO SIMPLE
    public function obtenerCuponesActivos() {
        $filtros = ['estados' => ['activo']];
        return $this->cupon->filtrar($filtros);
    }
}



?>