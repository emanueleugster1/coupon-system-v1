<?php
class Cupon {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    // Crear nuevo cupón - FUNCIÓN BÁSICA
    public function crear($datos) {
        // Sanitización básica
        $codigo = trim(strip_tags($datos['codigo']));
        $descripcion = trim(strip_tags($datos['descripcion']));
        $valor = (float) $datos['valor'];
        $fecha_expiracion = $datos['fecha_expiracion'];
        $estado = isset($datos['estado']) ? $datos['estado'] : 'activo';
        
        // Validaciones básicas
        if (empty($codigo) || empty($descripcion) || $valor <= 0) {
            return false;
        }
        
        try {
            $stmt = $this->pdo->prepare("INSERT INTO cupones (codigo, descripcion, valor, fecha_expiracion, estado) VALUES (?, ?, ?, ?, ?)");
            return $stmt->execute([$codigo, $descripcion, $valor, $fecha_expiracion, $estado]);
        } catch (PDOException $e) {
            return false;
        }
    }
    
    // Obtener todos los cupones - FUNCIÓN BÁSICA  
    public function obtenerTodos() {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM cupones ORDER BY fecha_creacion DESC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
    
    // Obtener cupón por ID - FUNCIÓN BÁSICA
    public function obtenerPorId($id) {
        $id = (int) $id;
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM cupones WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }
    
    // Obtener cupón por CÓDIGO - FUNCIÓN BÁSICA
    public function obtenerPorCodigo($codigo) {
        $codigo = trim(strip_tags($codigo));
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM cupones WHERE codigo = ?");
            $stmt->execute([$codigo]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }
    
    // Actualizar cupón existente - FUNCIÓN BÁSICA
    public function actualizar($id, $datos) {
        $id = (int) $id;
        
        // Sanitización básica
        $codigo = trim(strip_tags($datos['codigo']));
        $descripcion = trim(strip_tags($datos['descripcion']));
        $valor = (float) $datos['valor'];
        $fecha_expiracion = $datos['fecha_expiracion'];
        $estado = $datos['estado'];
        
        // Validaciones básicas
        if (empty($codigo) || empty($descripcion) || $valor <= 0) {
            return false;
        }
        
        try {
            $stmt = $this->pdo->prepare("UPDATE cupones SET codigo = ?, descripcion = ?, valor = ?, fecha_expiracion = ?, estado = ? WHERE id = ?");
            return $stmt->execute([$codigo, $descripcion, $valor, $fecha_expiracion, $estado, $id]);
        } catch (PDOException $e) {
            return false;
        }
    }
    
    // Eliminar cupón (soft delete) - FUNCIÓN BÁSICA
    public function eliminar($id) {
        // Validar que el ID sea un entero positivo válido
        if (!is_numeric($id) || $id <= 0 || $id != (int)$id) {
            return false;
        }
        
        $id = (int) $id;
        
        try {
            // Primero verificar que el cupón existe
            $stmt = $this->pdo->prepare("SELECT id FROM cupones WHERE id = ?");
            $stmt->execute([$id]);
            
            if (!$stmt->fetch()) {
                return false; // El cupón no existe
            }
            
            // Si existe, proceder con el soft delete
            $stmt = $this->pdo->prepare("UPDATE cupones SET estado = 'inactivo' WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            return false;
        }
    }
    
    // Filtrar cupones - FUNCIÓN BÁSICA
    public function filtrar($filtros = []) {
        $sql = "SELECT c.*, 
                       COALESCE(CONCAT(u.nombre, ' ', u.apellido), '-') as usuario_canje
                FROM cupones c
                LEFT JOIN canjes cj ON c.id = cj.cupon_id
                LEFT JOIN usuarios u ON cj.usuario_id = u.id
                WHERE 1=1";
        $params = [];
        
        // Filtrar por estados múltiples
        if (!empty($filtros['estados']) && is_array($filtros['estados'])) {
            $placeholders = str_repeat('?,', count($filtros['estados']) - 1) . '?';
            $sql .= " AND c.estado IN ($placeholders)";
            $params = array_merge($params, $filtros['estados']);
        }
        
        // Ordenamiento múltiple
        $orderBy = "c.fecha_creacion DESC"; // Por defecto
        if (!empty($filtros['orden'])) {
            $orderClauses = [];
            $ordenArray = is_array($filtros['orden']) ? $filtros['orden'] : [$filtros['orden']];
            
            foreach ($ordenArray as $orden) {
                switch ($orden) {
                    case 'fecha':
                        $orderClauses[] = "c.fecha_creacion DESC";
                        break;
                    case 'nombre':
                        $orderClauses[] = "c.descripcion ASC";
                        break;
                    case 'valor':
                        $orderClauses[] = "c.valor DESC";
                        break;
                }
            }
            
            if (!empty($orderClauses)) {
                $orderBy = implode(', ', $orderClauses);
            }
        }
        
        $sql .= " ORDER BY $orderBy";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
    
    // Validar cupón para canje - FUNCIÓN BÁSICA (hace todas las validaciones)
    public function validarParaCanje($codigo) {
        // Sanitizar código básico
        $codigo = strtoupper(trim(strip_tags($codigo)));
        
        // Validar código no vacío
        if (empty($codigo)) {
            return ['valido' => false, 'mensaje' => 'Ingrese un código de cupón'];
        }
        
        try {
            // Verificar en una sola consulta: existe, activo, no expirado
            $stmt = $this->pdo->prepare("SELECT * FROM cupones WHERE codigo = ? AND estado = 'activo' AND fecha_expiracion >= CURDATE()");
            $stmt->execute([$codigo]);
            $cupon = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$cupon) {
                return ['valido' => false, 'mensaje' => 'El cupón no existe o ha expirado'];
            }
            
            return ['valido' => true, 'cupon' => $cupon];
            
        } catch (PDOException $e) {
            return ['valido' => false, 'mensaje' => 'Error al validar el cupón'];
        }
    }
    
    // Canjear cupón - FUNCIÓN BÁSICA  
    public function canjear($codigo, $usuario_id = null) {
        // 1. Validar cupón
        $validacion = $this->validarParaCanje($codigo);
        
        if (!$validacion['valido']) {
            return ['success' => false, 'message' => $validacion['mensaje']];
        }
        
        $cupon = $validacion['cupon'];
        $usuario_id = $usuario_id ? (int) $usuario_id : null;
        
        try {
            // Iniciar transacción para consistencia
            $this->pdo->beginTransaction();
            
            // 2. Actualizar cupón a estado canjeado
            $stmt = $this->pdo->prepare("UPDATE cupones SET estado = 'canjeado' WHERE id = ?");
            $stmt->execute([$cupon['id']]);
            
            // 3. Insertar en tabla canjes
            $stmt = $this->pdo->prepare("INSERT INTO canjes (cupon_id, usuario_id, fecha_canje) VALUES (?, ?, NOW())");
            $stmt->execute([$cupon['id'], $usuario_id]);
            
            // Confirmar transacción
            $this->pdo->commit();
            
            return [
                'success' => true, 
                'message' => 'Cupón canjeado exitosamente',
                'cupon' => $cupon
            ];
            
        } catch (PDOException $e) {
            // Revertir transacción en caso de error
            $this->pdo->rollback();
            return ['success' => false, 'message' => 'Error al canjear el cupón'];
        }
    }
    
    // Obtener historial de canjes - FUNCIÓN BÁSICA
    public function obtenerHistorialCanjes() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    c.id as canje_id,
                    c.fecha_canje,
                    cup.codigo,
                    cup.descripcion,
                    cup.valor,
                    COALESCE(CONCAT(u.nombre, ' ', u.apellido), 'Usuario anónimo') as usuario_nombre,
                    COALESCE(u.email, 'N/A') as usuario_email
                FROM canjes c
                INNER JOIN cupones cup ON c.cupon_id = cup.id
                LEFT JOIN usuarios u ON c.usuario_id = u.id
                ORDER BY c.fecha_canje DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}
?>