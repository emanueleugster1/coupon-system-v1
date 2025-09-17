<?php
/**
 * Prueba unitaria para las funciones de obtener cupones de la clase Cupon
 * Verifica obtenerTodos(), obtenerPorId() y obtenerPorCodigo()
 */

// Incluir las dependencias necesarias
require_once __DIR__ . '/../models/Cupon.php';

class ObtenerCuponTest {
    private $pdo;
    private $cupon;
    private $cuponPrueba;
    
    public function __construct() {
        // Obtener conexión PDO desde config.php
        $this->pdo = require_once __DIR__ . '/../config/config.php';
        $this->cupon = new Cupon($this->pdo);
        $this->prepararDatosPrueba();
    }
    
    private function prepararDatosPrueba() {
        // Crear un cupón de prueba para usar en las pruebas
        $this->cuponPrueba = [
            'codigo' => 'OBTENER' . time(),
            'descripcion' => 'Cupón para pruebas de obtener',
            'valor' => 30.0,
            'fecha_expiracion' => date('Y-m-d', strtotime('+15 days')),
            'estado' => 'activo'
        ];
        
        // Crear el cupón en la base de datos
        $this->cupon->crear($this->cuponPrueba);
    }
    
    public function testObtenerTodos() {
        echo "<br>=== PRUEBA: OBTENER TODOS LOS CUPONES ===<br>";
        
        try {
            $cupones = $this->cupon->obtenerTodos();
            
            if (is_array($cupones)) {
                $cantidad = count($cupones);
                echo "✅ ÉXITO: Se obtuvieron $cantidad cupones<br>";
                
                if ($cantidad > 0) {
                    echo "   - Primer cupón ID: {$cupones[0]['id']}<br>";
                    echo "   - Primer cupón código: {$cupones[0]['codigo']}<br>";
                    
                    // Verificar que tenga las columnas esperadas
                    $columnasEsperadas = ['id', 'codigo', 'descripcion', 'valor', 'fecha_expiracion', 'estado'];
                    $columnasPresentes = array_keys($cupones[0]);
                    
                    $columnasFaltantes = array_diff($columnasEsperadas, $columnasPresentes);
                    
                    if (empty($columnasFaltantes)) {
                        echo "✅ VERIFICACIÓN: Todas las columnas esperadas están presentes<br>";
                        return true;
                    } else {
                        echo "❌ ERROR: Faltan columnas: " . implode(', ', $columnasFaltantes) . "<br>";
                        return false;
                    }
                } else {
                    echo "⚠️  ADVERTENCIA: No hay cupones en la base de datos<br>";
                    return true; // No es un error si no hay cupones
                }
            } else {
                echo "❌ ERROR: La función no devolvió un array<br>";
                return false;
            }
        } catch (Exception $e) {
            echo "❌ ERROR: Excepción al obtener cupones: " . $e->getMessage() . "<br>";
            return false;
        }
    }
    
    public function testObtenerPorCodigo() {
        echo "<br>=== PRUEBA: OBTENER CUPÓN POR CÓDIGO ===<br>";
        
        try {
            // Probar con código existente
            $cupon = $this->cupon->obtenerPorCodigo($this->cuponPrueba['codigo']);
            
            if ($cupon && is_array($cupon)) {
                echo "✅ ÉXITO: Cupón encontrado por código<br>";
                echo "   - ID: {$cupon['id']}<br>";
                echo "   - Código: {$cupon['codigo']}<br>";
                echo "   - Descripción: {$cupon['descripcion']}<br>";
                echo "   - Valor: {$cupon['valor']}<br>";
                
                // Verificar que los datos coincidan
                if ($cupon['codigo'] == $this->cuponPrueba['codigo'] && 
                    $cupon['descripcion'] == $this->cuponPrueba['descripcion']) {
                    echo "✅ VERIFICACIÓN: Los datos del cupón coinciden<br>";
                    $resultado1 = true;
                } else {
                    echo "❌ ERROR: Los datos del cupón no coinciden<br>";
                    $resultado1 = false;
                }
            } else {
                echo "❌ ERROR: No se encontró el cupón por código<br>";
                $resultado1 = false;
            }
            
            // Probar con código inexistente
            $cuponInexistente = $this->cupon->obtenerPorCodigo('CODIGO_INEXISTENTE_' . time());
            
            if (!$cuponInexistente) {
                echo "✅ ÉXITO: Correctamente devolvió false para código inexistente<br>";
                $resultado2 = true;
            } else {
                echo "❌ ERROR: Devolvió datos para código inexistente<br>";
                $resultado2 = false;
            }
            
            return $resultado1 && $resultado2;
            
        } catch (Exception $e) {
            echo "❌ ERROR: Excepción al obtener cupón por código: " . $e->getMessage() . "<br>";
            return false;
        }
    }
    
    public function testObtenerPorId() {
        echo "<br>=== PRUEBA: OBTENER CUPÓN POR ID ===<br>";
        
        try {
            // Primero obtener el ID del cupón de prueba
            $cuponReferencia = $this->cupon->obtenerPorCodigo($this->cuponPrueba['codigo']);
            
            if (!$cuponReferencia) {
                echo "❌ ERROR: No se pudo obtener el cupón de referencia<br>";
                return false;
            }
            
            $idPrueba = $cuponReferencia['id'];
            
            // Probar con ID existente
            $cupon = $this->cupon->obtenerPorId($idPrueba);
            
            if ($cupon && is_array($cupon)) {
                echo "✅ ÉXITO: Cupón encontrado por ID<br>";
                echo "   - ID: {$cupon['id']}<br>";
                echo "   - Código: {$cupon['codigo']}<br>";
                echo "   - Descripción: {$cupon['descripcion']}<br>";
                
                // Verificar que el ID coincida
                if ($cupon['id'] == $idPrueba) {
                    echo "✅ VERIFICACIÓN: El ID del cupón coincide<br>";
                    $resultado1 = true;
                } else {
                    echo "❌ ERROR: El ID del cupón no coincide<br>";
                    $resultado1 = false;
                }
            } else {
                echo "❌ ERROR: No se encontró el cupón por ID<br>";
                $resultado1 = false;
            }
            
            // Probar con ID inexistente (número muy alto)
            $idInexistente = 999999;
            $cuponInexistente = $this->cupon->obtenerPorId($idInexistente);
            
            if (!$cuponInexistente) {
                echo "✅ ÉXITO: Correctamente devolvió false para ID inexistente<br>";
                $resultado2 = true;
            } else {
                echo "❌ ERROR: Devolvió datos para ID inexistente<br>";
                $resultado2 = false;
            }
            
            // Probar con ID inválido (string)
            $cuponInvalido = $this->cupon->obtenerPorId('abc');
            
            if (!$cuponInvalido) {
                echo "✅ ÉXITO: Correctamente manejó ID inválido<br>";
                $resultado3 = true;
            } else {
                echo "❌ ERROR: No manejó correctamente ID inválido<br>";
                $resultado3 = false;
            }
            
            return $resultado1 && $resultado2 && $resultado3;
            
        } catch (Exception $e) {
            echo "❌ ERROR: Excepción al obtener cupón por ID: " . $e->getMessage() . "<br>";
            return false;
        }
    }
    
    public function testFiltrarCupones() {
        echo "<br>=== PRUEBA: FILTRAR CUPONES ===<br>";
        
        try {
            // Probar filtro básico sin parámetros
            $cuponesSinFiltro = $this->cupon->filtrar();
            
            if (is_array($cuponesSinFiltro)) {
                echo "✅ ÉXITO: Filtro sin parámetros funciona (" . count($cuponesSinFiltro) . " cupones)<br>";
                $resultado1 = true;
            } else {
                echo "❌ ERROR: Filtro sin parámetros no devolvió array<br>";
                $resultado1 = false;
            }
            
            // Probar filtro por estado
            $cuponesActivos = $this->cupon->filtrar(['estados' => ['activo']]);
            
            if (is_array($cuponesActivos)) {
                echo "✅ ÉXITO: Filtro por estado 'activo' funciona (" . count($cuponesActivos) . " cupones)<br>";
                $resultado2 = true;
            } else {
                echo "❌ ERROR: Filtro por estado no devolvió array<br>";
                $resultado2 = false;
            }
            
            return $resultado1 && $resultado2;
            
        } catch (Exception $e) {
            echo "❌ ERROR: Excepción al filtrar cupones: " . $e->getMessage() . "<br>";
            return false;
        }
    }
    
    public function ejecutarTodasLasPruebas() {
        echo "<br>" . str_repeat("=", 60) . "<br>";
        echo "EJECUTANDO PRUEBAS DE OBTENER CUPONES<br>";
        echo str_repeat("=", 60) . "<br>";
        
        $resultados = [];
        $resultados['obtener_todos'] = $this->testObtenerTodos();
        $resultados['obtener_por_codigo'] = $this->testObtenerPorCodigo();
        $resultados['obtener_por_id'] = $this->testObtenerPorId();
        $resultados['filtrar_cupones'] = $this->testFiltrarCupones();
        
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
            echo "\n🎉 TODAS LAS PRUEBAS DE OBTENER CUPONES PASARON<br>";
        } else {
            echo "\n⚠️  ALGUNAS PRUEBAS FALLARON - REVISAR IMPLEMENTACIÓN<br>";
        }
        
        return $exitosas == $total;
    }
}

// Ejecutar las pruebas si el archivo se ejecuta directamente
if (basename(__FILE__) == basename($_SERVER['SCRIPT_NAME'])) {
    $test = new ObtenerCuponTest();
    $test->ejecutarTodasLasPruebas();
}
?>