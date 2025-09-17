<?php
/**
 * Prueba unitaria para las funciones de validar y canjear cupones de la clase Cupon
 * Verifica validarParaCanje() y canjear()
 */

 /*
// Todo el código está comentado para pruebas
*/

// Incluir las dependencias necesarias
require_once __DIR__ . '/../models/Cupon.php';

class CanjearCuponTest {
    private $pdo;
    private $cupon;
    private $cuponActivo;
    private $cuponExpirado;
    private $cuponInactivo;
    
    public function __construct() {
        // Nota: Si config.php falla con die(), el script se detendrá aquí
        // Esto es intencional para evitar errores 500 en el navegador
        echo "Intentando conectar a la base de datos...<br>";
        
        // Obtener conexión PDO desde config.php
        $this->pdo = require_once __DIR__ . '/../config/config.php';
        
        if ($this->pdo) {
            echo "✅ Conexión exitosa<br>";
            $this->cupon = new Cupon($this->pdo);
            $this->prepararDatosPrueba();
        } else {
            echo "❌ Error: No se pudo establecer conexión<br>";
        }
    }
    
    private function prepararDatosPrueba() {
        // Crear cupón activo válido
        $this->cuponActivo = [
            'codigo' => 'CANJEAR' . time(),
            'descripcion' => 'Cupón activo para canjear',
            'valor' => 50.0,
            'fecha_expiracion' => date('Y-m-d', strtotime('+30 days')),
            'estado' => 'activo'
        ];
        $this->cupon->crear($this->cuponActivo);
        
        // Crear cupón expirado
        $this->cuponExpirado = [
            'codigo' => 'EXPIRADO' . time(),
            'descripcion' => 'Cupón expirado para pruebas',
            'valor' => 25.0,
            'fecha_expiracion' => date('Y-m-d', strtotime('-5 days')), // Expirado
            'estado' => 'activo'
        ];
        $this->cupon->crear($this->cuponExpirado);
        
        // Crear cupón inactivo
        $this->cuponInactivo = [
            'codigo' => 'INACTIVO' . time(),
            'descripcion' => 'Cupón inactivo para pruebas',
            'valor' => 15.0,
            'fecha_expiracion' => date('Y-m-d', strtotime('+30 days')),
            'estado' => 'inactivo'
        ];
        $this->cupon->crear($this->cuponInactivo);
    }
    
    public function testValidarCuponValido() {
        echo "<br>=== PRUEBA: VALIDAR CUPÓN VÁLIDO ===<br>";
        
        try {
            $resultado = $this->cupon->validarParaCanje($this->cuponActivo['codigo']);
            
            if (is_array($resultado) && isset($resultado['valido'])) {
                if ($resultado['valido'] === true) {
                    echo "✅ ÉXITO: Cupón válido correctamente identificado<br>";
                    echo "   - Código: {$this->cuponActivo['codigo']}<br>";
                    
                    if (isset($resultado['cupon']) && is_array($resultado['cupon'])) {
                        echo "✅ VERIFICACIÓN: Datos del cupón incluidos en la respuesta<br>";
                        echo "   - Descripción: {$resultado['cupon']['descripcion']}<br>";
                        echo "   - Valor: {$resultado['cupon']['valor']}<br>";
                        echo "   - Estado: {$resultado['cupon']['estado']}<br>";
                        return true;
                    } else {
                        echo "❌ ERROR: Datos del cupón no incluidos en la respuesta<br>";
                        return false;
                    }
                } else {
                    echo "❌ ERROR: Cupón válido marcado como inválido<br>";
                    echo "   - Mensaje: " . ($resultado['mensaje'] ?? 'Sin mensaje') . "<br>";
                    return false;
                }
            } else {
                echo "❌ ERROR: Formato de respuesta incorrecto<br>";
                return false;
            }
        } catch (Exception $e) {
            echo "❌ ERROR: Excepción al validar cupón: " . $e->getMessage() . "<br>";
            return false;
        }
    }
    
    public function testValidarCuponInvalido() {
        echo "<br>=== PRUEBA: VALIDAR CUPONES INVÁLIDOS ===<br>";
        
        $casosPrueba = [
            [
                'codigo' => '',
                'descripcion' => 'código vacío',
                'esperado' => false
            ],
            [
                'codigo' => 'INEXISTENTE' . time(),
                'descripcion' => 'código inexistente',
                'esperado' => false
            ],
            [
                'codigo' => $this->cuponExpirado['codigo'],
                'descripcion' => 'cupón expirado',
                'esperado' => false
            ],
            [
                'codigo' => $this->cuponInactivo['codigo'],
                'descripcion' => 'cupón inactivo',
                'esperado' => false
            ]
        ];
        
        $exitosas = 0;
        $total = count($casosPrueba);
        
        foreach ($casosPrueba as $caso) {
            try {
                $resultado = $this->cupon->validarParaCanje($caso['codigo']);
                
                if (is_array($resultado) && isset($resultado['valido'])) {
                    if ($resultado['valido'] === $caso['esperado']) {
                        echo "✅ ÉXITO: Correctamente rechazó {$caso['descripcion']}<br>";
                        if (isset($resultado['mensaje'])) {
                            echo "   - Mensaje: {$resultado['mensaje']}<br>";
                        }
                        $exitosas++;
                    } else {
                        echo "❌ ERROR: Incorrectamente validó {$caso['descripcion']}<br>";
                    }
                } else {
                    echo "❌ ERROR: Formato de respuesta incorrecto para {$caso['descripcion']}<br>";
                }
            } catch (Exception $e) {
                echo "❌ ERROR: Excepción al validar {$caso['descripcion']}: " . $e->getMessage() . "<br>";
            }
        }
        
        echo "\nValidaciones exitosas: $exitosas/$total<br>";
        return $exitosas == $total;
    }
    
    public function testCanjearCuponValido() {
        echo "<br>=== PRUEBA: CANJEAR CUPÓN VÁLIDO ===<br>";
        
        // Crear un cupón específico para canjear
        $cuponCanje = [
            'codigo' => 'CANJE' . time(),
            'descripcion' => 'Cupón específico para canje',
            'valor' => 75.0,
            'fecha_expiracion' => date('Y-m-d', strtotime('+30 days')),
            'estado' => 'activo'
        ];
        $this->cupon->crear($cuponCanje);
        
        try {
            $resultado = $this->cupon->canjear($cuponCanje['codigo']);
            
            if (is_array($resultado) && isset($resultado['success'])) {
                if ($resultado['success'] === true) {
                    echo "✅ ÉXITO: Cupón canjeado correctamente<br>";
                    echo "   - Mensaje: " . ($resultado['message'] ?? 'Sin mensaje') . "<br>";
                    
                    // Verificar que el cupón cambió a estado 'canjeado'
                    $cuponDespues = $this->cupon->obtenerPorCodigo($cuponCanje['codigo']);
                    
                    if ($cuponDespues && $cuponDespues['estado'] == 'canjeado') {
                        echo "✅ VERIFICACIÓN: Estado del cupón cambió a 'canjeado'<br>";
                        
                        // Verificar que se creó un registro en la tabla canjes
                        $historial = $this->cupon->obtenerHistorialCanjes();
                        $canjeEncontrado = false;
                        
                        foreach ($historial as $canje) {
                            if ($canje['codigo'] == $cuponCanje['codigo']) {
                                $canjeEncontrado = true;
                                echo "✅ VERIFICACIÓN: Registro de canje creado en historial<br>";
                                echo "   - Fecha canje: {$canje['fecha_canje']}<br>";
                                break;
                            }
                        }
                        
                        if ($canjeEncontrado) {
                            return true;
                        } else {
                            echo "❌ ERROR: No se encontró registro de canje en historial<br>";
                            return false;
                        }
                    } else {
                        echo "❌ ERROR: El estado del cupón no cambió a 'canjeado'<br>";
                        return false;
                    }
                } else {
                    echo "❌ ERROR: Canje falló inesperadamente<br>";
                    echo "   - Mensaje: " . ($resultado['message'] ?? 'Sin mensaje') . "<br>";
                    return false;
                }
            } else {
                echo "❌ ERROR: Formato de respuesta incorrecto<br>";
                return false;
            }
        } catch (Exception $e) {
            echo "❌ ERROR: Excepción al canjear cupón: " . $e->getMessage() . "<br>";
            return false;
        }
    }
    
    public function testCanjearCuponInvalido() {
        echo "<br>=== PRUEBA: CANJEAR CUPONES INVÁLIDOS ===<br>";
        
        $casosPrueba = [
            [
                'codigo' => '',
                'descripcion' => 'código vacío'
            ],
            [
                'codigo' => 'INEXISTENTE' . time(),
                'descripcion' => 'código inexistente'
            ],
            [
                'codigo' => $this->cuponExpirado['codigo'],
                'descripcion' => 'cupón expirado'
            ],
            [
                'codigo' => $this->cuponInactivo['codigo'],
                'descripcion' => 'cupón inactivo'
            ]
        ];
        
        $exitosas = 0;
        $total = count($casosPrueba);
        
        foreach ($casosPrueba as $caso) {
            try {
                $resultado = $this->cupon->canjear($caso['codigo']);
                
                if (is_array($resultado) && isset($resultado['success'])) {
                    if ($resultado['success'] === false) {
                        echo "✅ ÉXITO: Correctamente rechazó canje de {$caso['descripcion']}<br>";
                        echo "   - Mensaje: " . ($resultado['message'] ?? 'Sin mensaje') . "<br>";
                        $exitosas++;
                    } else {
                        echo "❌ ERROR: Incorrectamente permitió canje de {$caso['descripcion']}<br>";
                    }
                } else {
                    echo "❌ ERROR: Formato de respuesta incorrecto para {$caso['descripcion']}<br>";
                }
            } catch (Exception $e) {
                echo "❌ ERROR: Excepción al canjear {$caso['descripcion']}: " . $e->getMessage() . "<br>";
            }
        }
        
        echo "\nValidaciones exitosas: $exitosas/$total<br>";
        return $exitosas == $total;
    }
    
    public function testCanjearCuponYaCanjeado() {
        echo "<br>=== PRUEBA: CANJEAR CUPÓN YA CANJEADO ===<br>";
        
        // Crear y canjear un cupón
        $cuponDoble = [
            'codigo' => 'DOBLE' . time(),
            'descripcion' => 'Cupón para prueba de doble canje',
            'valor' => 30.0,
            'fecha_expiracion' => date('Y-m-d', strtotime('+30 days')),
            'estado' => 'activo'
        ];
        $this->cupon->crear($cuponDoble);
        
        try {
            // Primer canje
            $primerCanje = $this->cupon->canjear($cuponDoble['codigo']);
            
            if ($primerCanje && $primerCanje['success']) {
                echo "✅ Primer canje exitoso<br>";
                
                // Intentar segundo canje
                $segundoCanje = $this->cupon->canjear($cuponDoble['codigo']);
                
                if (is_array($segundoCanje) && isset($segundoCanje['success'])) {
                    if ($segundoCanje['success'] === false) {
                        echo "✅ ÉXITO: Correctamente rechazó segundo canje<br>";
                        echo "   - Mensaje: " . ($segundoCanje['message'] ?? 'Sin mensaje') . "<br>";
                        return true;
                    } else {
                        echo "❌ ERROR: Permitió canjear cupón ya canjeado<br>";
                        return false;
                    }
                } else {
                    echo "❌ ERROR: Formato de respuesta incorrecto en segundo canje<br>";
                    return false;
                }
            } else {
                echo "❌ ERROR: No se pudo realizar el primer canje<br>";
                return false;
            }
        } catch (Exception $e) {
            echo "❌ ERROR: Excepción en prueba de doble canje: " . $e->getMessage() . "<br>";
            return false;
        }
    }
    
    public function testHistorialCanjes() {
        echo "<br>=== PRUEBA: HISTORIAL DE CANJES ===<br>";
        
        try {
            $historial = $this->cupon->obtenerHistorialCanjes();
            
            if (is_array($historial)) {
                $cantidad = count($historial);
                echo "✅ ÉXITO: Se obtuvo historial de canjes ($cantidad registros)<br>";
                
                if ($cantidad > 0) {
                    $primerCanje = $historial[0];
                    $columnasEsperadas = ['canje_id', 'fecha_canje', 'codigo', 'descripcion', 'valor'];
                    $columnasPresentes = array_keys($primerCanje);
                    
                    $columnasFaltantes = array_diff($columnasEsperadas, $columnasPresentes);
                    
                    if (empty($columnasFaltantes)) {
                        echo "✅ VERIFICACIÓN: Historial contiene columnas esperadas<br>";
                        echo "   - Primer registro - Código: {$primerCanje['codigo']}<br>";
                        echo "   - Primer registro - Fecha: {$primerCanje['fecha_canje']}<br>";
                        return true;
                    } else {
                        echo "❌ ERROR: Faltan columnas en historial: " . implode(', ', $columnasFaltantes) . "<br>";
                        return false;
                    }
                } else {
                    echo "⚠️  ADVERTENCIA: No hay canjes en el historial<br>";
                    return true; // No es un error si no hay canjes
                }
            } else {
                echo "❌ ERROR: Historial no devolvió un array<br>";
                return false;
            }
        } catch (Exception $e) {
            echo "❌ ERROR: Excepción al obtener historial: " . $e->getMessage() . "<br>";
            return false;
        }
    }
    
    public function ejecutarTodasLasPruebas() {
        // Verificar si hay conexión a la base de datos
        if (!$this->pdo || !$this->cupon) {
            echo "\n❌ No se pueden ejecutar las pruebas: Sin conexión a la base de datos<br>";
            return false;
        }
        
        echo "<br>" . str_repeat("=", 60) . "<br>";
        echo "EJECUTANDO PRUEBAS DE VALIDAR Y CANJEAR CUPONES<br>";
        echo str_repeat("=", 60) . "<br>";
        
        $resultados = [];
        $resultados['validar_valido'] = $this->testValidarCuponValido();
        $resultados['validar_invalido'] = $this->testValidarCuponInvalido();
        $resultados['canjear_valido'] = $this->testCanjearCuponValido();
        $resultados['canjear_invalido'] = $this->testCanjearCuponInvalido();
        $resultados['canjear_ya_canjeado'] = $this->testCanjearCuponYaCanjeado();
        $resultados['historial_canjes'] = $this->testHistorialCanjes();
        
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
            echo "\n🎉 TODAS LAS PRUEBAS DE VALIDAR Y CANJEAR CUPONES PASARON<br>";
        } else {
            echo "\n⚠️  ALGUNAS PRUEBAS FALLARON - REVISAR IMPLEMENTACIÓN<br>";
        }
        
        return $exitosas == $total;
    }
}


// Ejecutar las pruebas directamente
$test = new CanjearCuponTest();
$test->ejecutarTodasLasPruebas();


?>