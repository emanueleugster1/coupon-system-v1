<?php
/**
 * Prueba unitaria para la función crear() de la clase Cupon
 * Verifica que se puedan crear cupones correctamente en la base de datos
 */



// Incluir las dependencias necesarias
require_once __DIR__ . '/../models/Cupon.php';


class CrearCuponTest {
    private $pdo;
    private $cupon;
    
    public function __construct() {
        // Obtener conexión PDO desde config.php
        $this->pdo = require_once __DIR__ . '/../config/config.php';
        $this->cupon = new Cupon($this->pdo);
    }
    
    public function testCrearCuponValido() {
        echo "<br>=== PRUEBA: CREAR CUPÓN VÁLIDO ===<br>";
        
        $datos = [
            'codigo' => 'TEST' . time(), // Código único usando timestamp
            'descripcion' => 'Cupón de prueba unitaria',
            'valor' => 25.50,
            'fecha_expiracion' => date('Y-m-d', strtotime('+30 days')),
            'estado' => 'activo'
        ];
        
        try {
            $resultado = $this->cupon->crear($datos);
            
            if ($resultado) {
                echo "✅ ÉXITO: Cupón creado correctamente<br>";
                echo "   - Código: {$datos['codigo']}<br>";
                echo "   - Descripción: {$datos['descripcion']}<br>";
                echo "   - Valor: {$datos['valor']}<br>";
                echo "   - Fecha expiración: {$datos['fecha_expiracion']}<br>";
                
                // Verificar que el cupón se guardó en la base de datos
                $cuponCreado = $this->cupon->obtenerPorCodigo($datos['codigo']);
                if ($cuponCreado) {
                    echo "✅ VERIFICACIÓN: Cupón encontrado en la base de datos<br>";
                    return true;
                } else {
                    echo "❌ ERROR: Cupón no encontrado en la base de datos<br>";
                    return false;
                }
            } else {
                echo "❌ ERROR: No se pudo crear el cupón<br>";
                return false;
            }
        } catch (Exception $e) {
            echo "❌ ERROR: Excepción al crear cupón: " . $e->getMessage() . "<br>";
            return false;
        }
    }
    
    public function testCrearCuponDatosInvalidos() {
        echo "<br>=== PRUEBA: CREAR CUPÓN CON DATOS INVÁLIDOS ===<br>";
        
        $datosPrueba = [
            // Código vacío
            [
                'codigo' => '',
                'descripcion' => 'Descripción válida',
                'valor' => 10.0,
                'fecha_expiracion' => date('Y-m-d', strtotime('+30 days')),
                'nombre' => 'código vacío'
            ],
            // Descripción vacía
            [
                'codigo' => 'VALID' . time(),
                'descripcion' => '',
                'valor' => 10.0,
                'fecha_expiracion' => date('Y-m-d', strtotime('+30 days')),
                'nombre' => 'descripción vacía'
            ],
            // Valor inválido (cero)
            [
                'codigo' => 'VALID' . time(),
                'descripcion' => 'Descripción válida',
                'valor' => 0,
                'fecha_expiracion' => date('Y-m-d', strtotime('+30 days')),
                'nombre' => 'valor cero'
            ],
            // Valor negativo
            [
                'codigo' => 'VALID' . time(),
                'descripcion' => 'Descripción válida',
                'valor' => -5.0,
                'fecha_expiracion' => date('Y-m-d', strtotime('+30 days')),
                'nombre' => 'valor negativo'
            ]
        ];
        
        $exitosas = 0;
        $total = count($datosPrueba);
        
        foreach ($datosPrueba as $datos) {
            $nombre = $datos['nombre'];
            unset($datos['nombre']);
            
            $resultado = $this->cupon->crear($datos);
            
            if (!$resultado) {
                echo "✅ ÉXITO: Rechazó correctamente cupón con $nombre<br>";
                $exitosas++;
            } else {
                echo "❌ ERROR: Aceptó incorrectamente cupón con $nombre<br>";
            }
        }
        
        echo "\nValidaciones exitosas: $exitosas/$total<br>";
        return $exitosas == $total;
    }
    
    public function testCrearCuponCodigoDuplicado() {
        echo "<br>=== PRUEBA: CREAR CUPÓN CON CÓDIGO DUPLICADO ===<br>";
        
        $codigoUnico = 'DUPLICATE' . time();
        
        $datos1 = [
            'codigo' => $codigoUnico,
            'descripcion' => 'Primer cupón',
            'valor' => 15.0,
            'fecha_expiracion' => date('Y-m-d', strtotime('+30 days'))
        ];
        
        $datos2 = [
            'codigo' => $codigoUnico, // Mismo código
            'descripcion' => 'Segundo cupón',
            'valor' => 20.0,
            'fecha_expiracion' => date('Y-m-d', strtotime('+30 days'))
        ];
        
        try {
            // Crear primer cupón
            $resultado1 = $this->cupon->crear($datos1);
            
            if (!$resultado1) {
                echo "❌ ERROR: No se pudo crear el primer cupón<br>";
                return false;
            }
            
            echo "✅ Primer cupón creado correctamente<br>";
            
            // Intentar crear segundo cupón con mismo código
            $resultado2 = $this->cupon->crear($datos2);
            
            if (!$resultado2) {
                echo "✅ ÉXITO: Rechazó correctamente cupón con código duplicado<br>";
                return true;
            } else {
                echo "❌ ERROR: Permitió crear cupón con código duplicado<br>";
                return false;
            }
            
        } catch (Exception $e) {
            echo "✅ ÉXITO: Excepción controlada por código duplicado: " . $e->getMessage() . "<br>";
            return true;
        }
    }
    
    public function ejecutarTodasLasPruebas() {
        echo "<br>" . str_repeat("=", 60) . "<br>";
        echo "EJECUTANDO PRUEBAS DE CREAR CUPÓN<br>";
        echo str_repeat("=", 60) . "<br>";
        
        $resultados = [];
        $resultados['crear_valido'] = $this->testCrearCuponValido();
        $resultados['datos_invalidos'] = $this->testCrearCuponDatosInvalidos();
        $resultados['codigo_duplicado'] = $this->testCrearCuponCodigoDuplicado();
        
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
            echo "\n🎉 TODAS LAS PRUEBAS DE CREAR CUPÓN PASARON<br>";
        } else {
            echo "\n⚠️  ALGUNAS PRUEBAS FALLARON - REVISAR IMPLEMENTACIÓN<br>";
        }
        
        return $exitosas == $total;
    }
}

// Ejecutar las pruebas si el archivo se ejecuta directamente
if (basename(__FILE__) == basename($_SERVER['SCRIPT_NAME'])) {
    $test = new CrearCuponTest();
    $test->ejecutarTodasLasPruebas();
}
?>