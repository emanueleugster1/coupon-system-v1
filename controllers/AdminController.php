<?php
class AdminController {
    private $pdo;
    private $cupon;
    
    public function __construct() {
        // El controlador maneja la configuración
        $this->pdo = require_once __DIR__ . '/../config/config.php';
        
        require_once __DIR__ . '/../models/Cupon.php';
        $this->cupon = new Cupon($this->pdo);
    }
    
    // Procesar creación - MÉTODO SIMPLE
    public function procesarCreacion() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return false;
        }
        
        // Validar campos no vacíos (básico)
        if (empty($_POST['codigo']) || empty($_POST['descripcion']) || empty($_POST['valor']) || empty($_POST['fecha_expiracion'])) {
            return false;
        }
        
        $datos = [
            'codigo' => $_POST['codigo'],
            'descripcion' => $_POST['descripcion'],
            'valor' => $_POST['valor'],
            'fecha_expiracion' => $_POST['fecha_expiracion'],
            'estado' => isset($_POST['estado']) ? $_POST['estado'] : 'activo'
        ];
        
        // Llamar Cupon::crear()
        if ($this->cupon->crear($datos)) {
            return true;
        }
        
        return false;
    }
    
    // Procesar edición - MÉTODO SIMPLE
    public function procesarEdicion($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return false;
        }
        
        $id = (int) $id;
        
        // Validar campos no vacíos (básico)
        if (empty($_POST['codigo']) || empty($_POST['descripcion']) || empty($_POST['valor']) || empty($_POST['fecha_expiracion'])) {
            return false;
        }
        
        $datos = [
            'codigo' => $_POST['codigo'],
            'descripcion' => $_POST['descripcion'],
            'valor' => $_POST['valor'],
            'fecha_expiracion' => $_POST['fecha_expiracion'],
            'estado' => $_POST['estado']
        ];
        
        // Llamar Cupon::actualizar()
        if ($this->cupon->actualizar($id, $datos)) {
            return true;
        }
        
        return false;
    }
    
    // Procesar eliminación - MÉTODO SIMPLE
    public function procesarEliminacion($id) {
        $id = (int) $id;
        
        // Llamar Cupon::eliminar()
        if ($this->cupon->eliminar($id)) {
            return true;
        }
        
        return false;
    }
    
    // Obtener cupones con filtros - MÉTODO SIMPLE
    public function obtenerCuponesFiltrados() {
        $filtros = [];
        
        // Recibir parámetros GET para estados
        if (!empty($_GET['estados'])) {
            $filtros['estados'] = explode(',', $_GET['estados']);
        }
        
        // Recibir parámetros GET para ordenamiento múltiple
        if (!empty($_GET['orden'])) {
            $filtros['orden'] = explode(',', $_GET['orden']);
        }
        
        // Llamar Cupon::filtrar()
        return $this->cupon->filtrar($filtros);
    }
    
    // Obtener cupón por ID - MÉTODO SIMPLE
    public function obtenerCuponPorId($id) {
        $id = (int) $id;
        return $this->cupon->obtenerPorId($id);
    }
}
?>