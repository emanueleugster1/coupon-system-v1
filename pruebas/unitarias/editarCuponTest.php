<?php
/**
 * Prueba unitaria para la función actualizar() de la clase Cupon
 * Verifica que se puedan actualizar cupones correctamente en la base de datos
 */

// Incluir las dependencias necesarias
require_once __DIR__ . '/../models/Cupon.php';

class EditarCuponTest {
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
        // Crear un cupón de prueba para usar en las pruebas de actualización
        $this->cuponPrueba = [
            'codigo' => 'EDITAR' . time(),
            'descripcion' => 'Cupón original para editar',
            'valor' => 20.0,
            'fecha_expiracion' => date('Y-m-d', strtotime('+20 days')),
            'estado' => 'activo'
        ];
        
        // Crear el cupón en la base de datos
        $this->cupon->crear($this->cuponPrueba);
        
        // Obtener el ID del cupón creado
        $cuponCreado = $this->cupon->obtenerPorCodigo($this->cuponPrueba['codigo']);
        $this->idCuponPrueba = $cuponCreado['id'];
    }
    
    public function testActualizarCuponValido() {
        echo "<br>=== PRUEBA: ACTUALIZAR CUPÓN CON DATOS VÁLIDOS ===<br>";
        
        $datosActualizados = [
            'codigo' => $this->cuponPrueba['codigo'], // Mantener el mismo código
            'descripcion' => 'Cupón actualizado - descripción modificada',
            'valor' => 35.75,
            'fecha_expiracion' => date('Y-m-d', strtotime('+45 days')),
            'estado' => 'activo'
        ];
        
        try {
            $resultado = $this->cupon->actualizar($this->idCuponPrueba, $datosActualizados);
            
            if ($resultado) {
                echo "✅ ÉXITO: Cupón actualizado correctamente<br>";
                
                // Verificar que los cambios se guardaron
                $cuponActualizado = $this->cupon->obtenerPorId($this->idCuponPrueba);
                
                if ($cuponActualizado) {
                    echo "   - Nueva descripción: {$cuponActualizado['descripcion']}<br>";
                    echo "   - Nuevo valor: {$cuponActualizado['valor']}<br>";
                    echo "   - Nueva fecha: {$cuponActualizado['fecha_expiracion']}<br>";
                    
                    // Verificar que los datos coincidan
                    if ($cuponActualizado['descripcion'] == $datosActualizados['descripcion'] &&
                        $cuponActualizado['valor'] == $datosActualizados['valor'] &&
                        $cuponActualizado['fecha_expiracion'] == $datosActualizados['fecha_expiracion']) {
                        echo "✅ VERIFICACIÓN: Los datos se actualizaron correctamente<br>";
                        return true;
                    } else {
                        echo "❌ ERROR: Los datos no se actualizaron correctamente<br>";
                        return false;
                    }
                } else {
                    echo "❌ ERROR: No se pudo obtener el cupón actualizado<br>";
                    return false;
                }
            } else {
                echo "❌ ERROR: No se pudo actualizar el cupón<br>";
                return false;
            }
        } catch (Exception $e) {
            echo "❌ ERROR: Excepción al actualizar cupón: " . $e->getMessage() . "<br>";
            return false;
        }
    }
    
    public function testActualizarCuponDatosInvalidos() {
        echo "<br>=== PRUEBA: ACTUALIZAR CUPÓN CON DATOS INVÁLIDOS ===<br>";
        
        $datosPrueba = [
            // Código vacío
            [
                'codigo' => '',
                'descripcion' => 'Descripción válida',
                'valor' => 10.0,
                'fecha_expiracion' => date('Y-m-d', strtotime('+30 days')),
                'estado' => 'activo',
                'nombre' => 'código vacío'
            ],
            // Descripción vacía
            [
                'codigo' => 'VALID' . time(),
                'descripcion' => '',
                'valor' => 10.0,
                'fecha_expiracion' => date('Y-m-d', strtotime('+30 days')),
                'estado' => 'activo',
                'nombre' => 'descripción vacía'
            ],
            // Valor cero
            [
                'codigo' => 'VALID' . time(),
                'descripcion' => 'Descripción válida',
                'valor' => 0,
                'fecha_expiracion' => date('Y-m-d', strtotime('+30 days')),
                'estado' => 'activo',
                'nombre' => 'valor cero'
            ],
            // Valor negativo
            [
                'codigo' => 'VALID' . time(),
                'descripcion' => 'Descripción válida',
                'valor' => -15.0,
                'fecha_expiracion' => date('Y-m-d', strtotime('+30 days')),
                'estado' => 'activo',
                'nombre' => 'valor negativo'
            ]
        ];
        
        $exitosas = 0;
        $total = count($datosPrueba);
        
        foreach ($datosPrueba as $datos) {
            $nombre = $datos['nombre'];
            unset($datos['nombre']);
            
            $resultado = $this->cupon->actualizar($this->idCuponPrueba, $datos);
            
            if (!$resultado) {
                echo "✅ ÉXITO: Rechazó correctamente actualización con $nombre<br>";
                $exitosas++;
            } else {
                echo "❌ ERROR: Aceptó incorrectamente actualización con $nombre<br>";
            }
        }
        
        echo "\nValidaciones exitosas: $exitosas/$total<br>";
        return $exitosas == $total;
    }
    
    public function testActualizarCuponInexistente() {
        echo "<br>=== PRUEBA: ACTUALIZAR CUPÓN INEXISTENTE ===<br>";
        
        $idInexistente = 999999;
        $datosActualizacion = [
            'codigo' => 'INEXISTENTE' . time(),
            'descripcion' => 'Descripción para cupón inexistente',
            'valor' => 25.0,
            'fecha_expiracion' => date('Y-m-d', strtotime('+30 days')),
            'estado' => 'activo'
        ];
        
        try {
            $resultado = $this->cupon->actualizar($idInexistente, $datosActualizacion);
            
            if (!$resultado) {
                echo "✅ ÉXITO: Correctamente falló al actualizar cupón inexistente<br>";
                return true;
            } else {
                echo "❌ ERROR: Reportó éxito al actualizar cupón inexistente<br>";
                return false;
            }
        } catch (Exception $e) {
            echo "✅ ÉXITO: Excepción controlada para cupón inexistente: " . $e->getMessage() . "<br>";
            return true;
        }
    }
    
    public function testActualizarEstadoCupon() {
        echo "<br>=== PRUEBA: ACTUALIZAR ESTADO DEL CUPÓN ===<br>";
        
        // Crear un cupón adicional para esta prueba
        $cuponEstado = [
            'codigo' => 'ESTADO' . time(),
            'descripcion' => 'Cupón para prueba de estado',
            'valor' => 15.0,
            'fecha_expiracion' => date('Y-m-d', strtotime('+30 days')),
            'estado' => 'activo'
        ];
        
        $this->cupon->crear($cuponEstado);
        $cuponCreado = $this->cupon->obtenerPorCodigo($cuponEstado['codigo']);
        $idCupon = $cuponCreado['id'];
        
        try {
            // Cambiar estado a inactivo
            $datosActualizacion = [
                'codigo' => $cuponEstado['codigo'],
                'descripcion' => $cuponEstado['descripcion'],
                'valor' => $cuponEstado['valor'],
                'fecha_expiracion' => $cuponEstado['fecha_expiracion'],
                'estado' => 'inactivo'
            ];
            
            $resultado = $this->cupon->actualizar($idCupon, $datosActualizacion);
            
            if ($resultado) {
                // Verificar que el estado cambió
                $cuponActualizado = $this->cupon->obtenerPorId($idCupon);
                
                if ($cuponActualizado && $cuponActualizado['estado'] == 'inactivo') {
                    echo "✅ ÉXITO: Estado del cupón actualizado correctamente a 'inactivo'<br>";
                    
                    // Cambiar de vuelta a activo
                    $datosActualizacion['estado'] = 'activo';
                    $resultado2 = $this->cupon->actualizar($idCupon, $datosActualizacion);
                    
                    if ($resultado2) {
                        $cuponFinal = $this->cupon->obtenerPorId($idCupon);
                        if ($cuponFinal && $cuponFinal['estado'] == 'activo') {
                            echo "✅ ÉXITO: Estado del cupón actualizado correctamente a 'activo'<br>";
                            return true;
                        }
                    }
                    
                    echo "❌ ERROR: No se pudo cambiar el estado de vuelta a activo<br>";
                    return false;
                } else {
                    echo "❌ ERROR: El estado no se actualizó correctamente<br>";
                    return false;
                }
            } else {
                echo "❌ ERROR: No se pudo actualizar el estado del cupón<br>";
                return false;
            }
        } catch (Exception $e) {
            echo "❌ ERROR: Excepción al actualizar estado: " . $e->getMessage() . "<br>";
            return false;
        }
    }
    
    public function ejecutarTodasLasPruebas() {
        echo "<br>" . str_repeat("=", 60) . "<br>";
        echo "EJECUTANDO PRUEBAS DE ACTUALIZAR CUPÓN<br>";
        echo str_repeat("=", 60) . "<br>";
        
        $resultados = [];
        $resultados['actualizar_valido'] = $this->testActualizarCuponValido();
        $resultados['datos_invalidos'] = $this->testActualizarCuponDatosInvalidos();
        $resultados['cupon_inexistente'] = $this->testActualizarCuponInexistente();
        $resultados['actualizar_estado'] = $this->testActualizarEstadoCupon();
        
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
            echo "\n🎉 TODAS LAS PRUEBAS DE ACTUALIZAR CUPÓN PASARON<br>";
        } else {
            echo "\n⚠️  ALGUNAS PRUEBAS FALLARON - REVISAR IMPLEMENTACIÓN<br>";
        }
        
        return $exitosas == $total;
    }
}

// Ejecutar las pruebas si el archivo se ejecuta directamente
if (basename(__FILE__) == basename($_SERVER['SCRIPT_NAME'])) {
    $test = new EditarCuponTest();
    $test->ejecutarTodasLasPruebas();
}
?>