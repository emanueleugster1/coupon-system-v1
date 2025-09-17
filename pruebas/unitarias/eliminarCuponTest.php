<?php
/**
 * Prueba unitaria para la función eliminar() de la clase Cupon
 * Verifica que se puedan eliminar cupones correctamente (soft delete)
 */

// Incluir las dependencias necesarias
require_once __DIR__ . '/../models/Cupon.php';

class EliminarCuponTest {
    private $pdo;
    private $cupon;
    private $cuponPrueba;
    private $idCuponPrueba;
    
    public function __construct() {
        // Obtener conexión PDO desde config.php
        $this->pdo = require_once __DIR__ . '/../config/config.php';
        $this->cupon = new Cupon($this->pdo);
        $this->prepararDatosPrueba();
    }
    
    private function prepararDatosPrueba() {
        // Crear un cupón de prueba para usar en las pruebas de eliminación
        $this->cuponPrueba = [
            'codigo' => 'ELIMINAR' . time(),
            'descripcion' => 'Cupón para prueba de eliminación',
            'valor' => 40.0,
            'fecha_expiracion' => date('Y-m-d', strtotime('+25 days')),
            'estado' => 'activo'
        ];
        
        // Crear el cupón en la base de datos
        $this->cupon->crear($this->cuponPrueba);
        
        // Obtener el ID del cupón creado
        $cuponCreado = $this->cupon->obtenerPorCodigo($this->cuponPrueba['codigo']);
        $this->idCuponPrueba = $cuponCreado['id'];
    }
    
    public function testEliminarCuponExistente() {
        echo "<br>=== PRUEBA: ELIMINAR CUPÓN EXISTENTE ===<br>";
        
        try {
            // Verificar que el cupón existe y está activo antes de eliminar
            $cuponAntes = $this->cupon->obtenerPorId($this->idCuponPrueba);
            
            if (!$cuponAntes || $cuponAntes['estado'] != 'activo') {
                echo "❌ ERROR: El cupón de prueba no está en estado activo<br>";
                return false;
            }
            
            echo "✅ Cupón antes de eliminar - Estado: {$cuponAntes['estado']}<br>";
            
            // Eliminar el cupón
            $resultado = $this->cupon->eliminar($this->idCuponPrueba);
            
            if ($resultado) {
                echo "✅ ÉXITO: Función eliminar() devolvió true<br>";
                
                // Verificar que el cupón ahora está marcado como inactivo
                $cuponDespues = $this->cupon->obtenerPorId($this->idCuponPrueba);
                
                if ($cuponDespues) {
                    echo "   - Estado después de eliminar: {$cuponDespues['estado']}<br>";
                    
                    if ($cuponDespues['estado'] == 'inactivo') {
                        echo "✅ VERIFICACIÓN: Cupón marcado correctamente como 'inactivo' (soft delete)<br>";
                        
                        // Verificar que otros datos permanecen intactos
                        if ($cuponDespues['codigo'] == $this->cuponPrueba['codigo'] &&
                            $cuponDespues['descripcion'] == $this->cuponPrueba['descripcion'] &&
                            $cuponDespues['valor'] == $this->cuponPrueba['valor']) {
                            echo "✅ VERIFICACIÓN: Los demás datos del cupón permanecen intactos<br>";
                            return true;
                        } else {
                            echo "❌ ERROR: Los datos del cupón se modificaron incorrectamente<br>";
                            return false;
                        }
                    } else {
                        echo "❌ ERROR: El cupón no se marcó como 'inactivo'<br>";
                        return false;
                    }
                } else {
                    echo "❌ ERROR: No se pudo obtener el cupón después de eliminar<br>";
                    return false;
                }
            } else {
                echo "❌ ERROR: La función eliminar() devolvió false<br>";
                return false;
            }
        } catch (Exception $e) {
            echo "❌ ERROR: Excepción al eliminar cupón: " . $e->getMessage() . "<br>";
            return false;
        }
    }
    
    public function testEliminarCuponInexistente() {
        echo "<br>=== PRUEBA: ELIMINAR CUPÓN INEXISTENTE ===<br>";
        
        $idInexistente = 999999;
        
        try {
            $resultado = $this->cupon->eliminar($idInexistente);
            
            if (!$resultado) {
                echo "✅ ÉXITO: Correctamente falló al eliminar cupón inexistente<br>";
                return true;
            } else {
                echo "❌ ERROR: Reportó éxito al eliminar cupón inexistente<br>";
                return false;
            }
        } catch (Exception $e) {
            echo "✅ ÉXITO: Excepción controlada para cupón inexistente: " . $e->getMessage() . "<br>";
            return true;
        }
    }
    
    public function testEliminarCuponYaInactivo() {
        echo "<br>=== PRUEBA: ELIMINAR CUPÓN YA INACTIVO ===<br>";
        
        // Crear un cupón adicional para esta prueba
        $cuponInactivo = [
            'codigo' => 'INACTIVO' . time(),
            'descripcion' => 'Cupón para prueba de eliminación de inactivo',
            'valor' => 12.0,
            'fecha_expiracion' => date('Y-m-d', strtotime('+30 days')),
            'estado' => 'inactivo' // Ya inactivo desde el inicio
        ];
        
        $this->cupon->crear($cuponInactivo);
        $cuponCreado = $this->cupon->obtenerPorCodigo($cuponInactivo['codigo']);
        $idCupon = $cuponCreado['id'];
        
        try {
            $resultado = $this->cupon->eliminar($idCupon);
            
            if ($resultado) {
                echo "✅ ÉXITO: Función eliminar() manejó correctamente cupón ya inactivo<br>";
                
                // Verificar que sigue inactivo
                $cuponDespues = $this->cupon->obtenerPorId($idCupon);
                if ($cuponDespues && $cuponDespues['estado'] == 'inactivo') {
                    echo "✅ VERIFICACIÓN: Cupón permanece en estado 'inactivo'<br>";
                    return true;
                } else {
                    echo "❌ ERROR: El estado del cupón cambió inesperadamente<br>";
                    return false;
                }
            } else {
                echo "⚠️  ADVERTENCIA: Función eliminar() devolvió false para cupón ya inactivo<br>";
                echo "   (Esto puede ser comportamiento esperado según la implementación)<br>";
                return true; // Consideramos esto como válido
            }
        } catch (Exception $e) {
            echo "❌ ERROR: Excepción al eliminar cupón ya inactivo: " . $e->getMessage() . "<br>";
            return false;
        }
    }
    
    public function testEliminarConIdInvalido() {
        echo "<br>=== PRUEBA: ELIMINAR CON ID INVÁLIDO ===<br>";
        
        $idsInvalidos = [
            'string' => 'abc',
            'negativo' => -1,
            'cero' => 0,
            'decimal' => 1.5
        ];
        
        $exitosas = 0;
        $total = count($idsInvalidos);
        
        foreach ($idsInvalidos as $tipo => $id) {
            try {
                $resultado = $this->cupon->eliminar($id);
                
                if (!$resultado) {
                    echo "✅ ÉXITO: Rechazó correctamente ID $tipo ($id)<br>";
                    $exitosas++;
                } else {
                    echo "❌ ERROR: Aceptó incorrectamente ID $tipo ($id)<br>";
                }
            } catch (Exception $e) {
                echo "✅ ÉXITO: Excepción controlada para ID $tipo: " . $e->getMessage() . "<br>";
                $exitosas++;
            }
        }
        
        echo "\nValidaciones exitosas: $exitosas/$total<br>";
        return $exitosas == $total;
    }
    
    public function testSoftDeleteVsHardDelete() {
        echo "<br>=== PRUEBA: VERIFICAR SOFT DELETE (NO HARD DELETE) ===<br>";
        
        // Crear un cupón específico para esta prueba
        $cuponSoftDelete = [
            'codigo' => 'SOFTDEL' . time(),
            'descripcion' => 'Cupón para verificar soft delete',
            'valor' => 18.0,
            'fecha_expiracion' => date('Y-m-d', strtotime('+30 days')),
            'estado' => 'activo'
        ];
        
        $this->cupon->crear($cuponSoftDelete);
        $cuponCreado = $this->cupon->obtenerPorCodigo($cuponSoftDelete['codigo']);
        $idCupon = $cuponCreado['id'];
        
        try {
            // Eliminar el cupón
            $resultado = $this->cupon->eliminar($idCupon);
            
            if ($resultado) {
                // Verificar que el registro aún existe en la base de datos
                $cuponDespues = $this->cupon->obtenerPorId($idCupon);
                
                if ($cuponDespues) {
                    echo "✅ ÉXITO: El registro aún existe en la base de datos (soft delete)<br>";
                    echo "   - ID: {$cuponDespues['id']}<br>";
                    echo "   - Código: {$cuponDespues['codigo']}<br>";
                    echo "   - Estado: {$cuponDespues['estado']}<br>";
                    
                    if ($cuponDespues['estado'] == 'inactivo') {
                        echo "✅ VERIFICACIÓN: Implementación correcta de soft delete<br>";
                        return true;
                    } else {
                        echo "❌ ERROR: El estado no cambió a 'inactivo'<br>";
                        return false;
                    }
                } else {
                    echo "❌ ERROR: El registro fue eliminado completamente (hard delete)<br>";
                    echo "   Esto no es el comportamiento esperado para soft delete<br>";
                    return false;
                }
            } else {
                echo "❌ ERROR: No se pudo eliminar el cupón<br>";
                return false;
            }
        } catch (Exception $e) {
            echo "❌ ERROR: Excepción en prueba de soft delete: " . $e->getMessage() . "<br>";
            return false;
        }
    }
    
    public function ejecutarTodasLasPruebas() {
        echo "<br>" . str_repeat("=", 60) . "<br>";
        echo "EJECUTANDO PRUEBAS DE ELIMINAR CUPÓN<br>";
        echo str_repeat("=", 60) . "<br>";
        
        $resultados = [];
        $resultados['eliminar_existente'] = $this->testEliminarCuponExistente();
        $resultados['eliminar_inexistente'] = $this->testEliminarCuponInexistente();
        $resultados['eliminar_ya_inactivo'] = $this->testEliminarCuponYaInactivo();
        $resultados['id_invalido'] = $this->testEliminarConIdInvalido();
        $resultados['soft_delete'] = $this->testSoftDeleteVsHardDelete();
        
        echo "<br>" . str_repeat("-", 60) . "<br>";
        echo "RESUMEN DE RESULTADOS:<br>";
        echo str_repeat("-", 60) . "<br>";
        
        $exitosas = 0;
        $total = count($resultados);
        
        foreach ($resultados as $prueba => $resultado) {
            $status = $resultado ? "✅ ÉXITO" : "❌ FALLO";
            echo "- " . str_replace('_', ' ', ucfirst($prueba)) . ": $status<br>";
            if ($resultado) $exitosas++;
        }
        
        echo "\nPruebas exitosas: $exitosas/$total<br>";
        
        if ($exitosas == $total) {
            echo "\n🎉 TODAS LAS PRUEBAS DE ELIMINAR CUPÓN PASARON<br>";
        } else {
            echo "\n⚠️  ALGUNAS PRUEBAS FALLARON - REVISAR IMPLEMENTACIÓN<br>";
        }
        
        return $exitosas == $total;
    }
}

// Ejecutar las pruebas si el archivo se ejecuta directamente
if (basename(__FILE__) == basename($_SERVER['SCRIPT_NAME'])) {
    $test = new EliminarCuponTest();
    $test->ejecutarTodasLasPruebas();
}
?>