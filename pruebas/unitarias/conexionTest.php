<?php
/**
 * Prueba unitaria para la conexión a la base de datos
 * Verifica que la conexión PDO funcione correctamente con las credenciales proporcionadas
 */

class ConexionTest {
    private $host = 'localhost';
    private $dbname = 'coupon_system';
    private $username = 'root';
    private $password = 'coupon123';
    private $charset = 'utf8mb4';
    
    public function testConexionBaseDatos() {
        echo "<br>=== PRUEBA DE CONEXIÓN A BASE DE DATOS ===<br>";
        
        try {
            $pdo = new PDO(
                "mysql:host={$this->host};dbname={$this->dbname};charset={$this->charset}", 
                $this->username, 
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_TIMEOUT => 10,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                ]
            );
            
            echo "✅ ÉXITO: Conexión a la base de datos establecida correctamente<br>";
            echo "   - Host: {$this->host}<br>";
            echo "   - Base de datos: {$this->dbname}<br>";
            echo "   - Usuario: {$this->username}<br>";
            echo "   - Charset: {$this->charset}<br>";
            
            return true;
            
        } catch (PDOException $e) {
            echo "❌ ERROR: No se pudo conectar a la base de datos<br>";
            echo "   - Mensaje: " . $e->getMessage() . "<br>";
            
            if (strpos($e->getMessage(), 'Unknown database') !== false) {
                echo "   - Solución: Ejecutar install.php para crear la base de datos<br>";
            } elseif (strpos($e->getMessage(), 'Access denied') !== false) {
                echo "   - Solución: Verificar credenciales de usuario y contraseña<br>";
            } elseif (strpos($e->getMessage(), 'Connection refused') !== false) {
                echo "   - Solución: Verificar que MySQL esté ejecutándose<br>";
            }
            
            return false;
        }
    }
    
    public function testConsultaSimple() {
        echo "<br>=== PRUEBA DE CONSULTA SIMPLE ===<br>";
        
        try {
            $pdo = new PDO(
                "mysql:host={$this->host};dbname={$this->dbname};charset={$this->charset}", 
                $this->username, 
                $this->password,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            
            // Probar una consulta simple
            $stmt = $pdo->query("SELECT 1 as test");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result && $result['test'] == 1) {
                echo "✅ ÉXITO: Consulta simple ejecutada correctamente<br>";
                return true;
            } else {
                echo "❌ ERROR: La consulta no devolvió el resultado esperado<br>";
                return false;
            }
            
        } catch (PDOException $e) {
            echo "❌ ERROR: No se pudo ejecutar la consulta<br>";
            echo "   - Mensaje: " . $e->getMessage() . "<br>";
            return false;
        }
    }
    
    public function ejecutarTodasLasPruebas() {
        echo "<br>" . str_repeat("=", 50) . "<br>";
        echo "EJECUTANDO PRUEBAS DE CONEXIÓN<br>";
        echo str_repeat("=", 50) . "<br>";
        
        $resultados = [];
        $resultados['conexion'] = $this->testConexionBaseDatos();
        $resultados['consulta'] = $this->testConsultaSimple();
        
        echo "<br>" . str_repeat("-", 50) . "<br>";
        echo "RESUMEN DE RESULTADOS:<br>";
        echo str_repeat("-", 50) . "<br>";
        
        $exitosas = 0;
        $total = count($resultados);
        
        foreach ($resultados as $prueba => $resultado) {
            $status = $resultado ? "✅ ÉXITO" : "❌ FALLO";
            echo "- " . ucfirst($prueba) . ": $status<br>";
            if ($resultado) $exitosas++;
        }
        
        echo "\nPruebas exitosas: $exitosas/$total<br>";
        
        if ($exitosas == $total) {
            echo "\n🎉 TODAS LAS PRUEBAS DE CONEXIÓN PASARON<br>";
        } else {
            echo "\n⚠️  ALGUNAS PRUEBAS FALLARON - REVISAR CONFIGURACIÓN<br>";
        }
        
        return $exitosas == $total;
    }
}

// Ejecutar las pruebas si el archivo se ejecuta directamente
if (basename(__FILE__) == basename($_SERVER['SCRIPT_NAME'])) {
    $test = new ConexionTest();
    $test->ejecutarTodasLasPruebas();
}
?>