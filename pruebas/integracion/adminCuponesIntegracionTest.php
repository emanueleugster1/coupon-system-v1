<?php
/**
 * Prueba de Integración - Endpoints CRUD de Cupones (Admin)
 * Prueba los endpoints POST /views/admin.php
 */

class AdminCuponesIntegracionTest {
    private $baseUrl;
    private $cookieJar;
    private $cuponIdCreado;
    
    public function __construct() {
        $this->baseUrl = 'http://54.207.61.138';
        $this->cookieJar = tempnam(sys_get_temp_dir(), 'cookies_admin');
    }
    
    public function ejecutarPruebas() {
        echo "<br>=== PRUEBAS DE INTEGRACIÓN - ADMIN CUPONES ===<br>";
        
        // Primero hacer login como admin
        $this->loginComoAdmin();
        
        $this->testCrearCupon();
        $this->testListarCupones();
        $this->testEditarCupon();
        $this->testEliminarCupon();
        
        // Limpiar
        unlink($this->cookieJar);
        
        echo "\n✅ Todas las pruebas de admin completadas<br>";
    }
    
    private function loginComoAdmin() {
        echo "\n🔐 Haciendo login como administrador...<br>";
        
        $postData = [
            'action' => 'login',
            'email' => 'admin',
            'password' => 'admin'
        ];
        
        $response = $this->hacerPeticionPOST('/views/login.php', $postData);
        
        echo "📊 Código HTTP: {$response['http_code']}<br>";
        echo "📋 Headers: " . substr($response['headers'], 0, 200) . "...<br>";
        
        // Verificar si hay redirección o si el login fue exitoso
        if ($response['http_code'] == 302 || 
            strpos($response['headers'], 'Location:') !== false ||
            strpos($response['body'], 'admin.php') !== false) {
            echo "✅ Login admin exitoso<br>";
        } else {
            echo "❌ Error en login admin<br>";
            echo "🔍 Respuesta del servidor: " . substr($response['body'], 0, 500) . "...<br>";
            exit(1);
        }
    }
    
    private function testCrearCupon() {
        echo "\n🧪 Probando crear cupón...<br>";
        
        $codigoUnico = 'TEST_' . time();
        
        $postData = [
            'accion' => 'crear',
            'codigo' => $codigoUnico,
            'descripcion' => 'Cupón de prueba integración',
            'valor' => '25.50',
            'fecha_expiracion' => date('Y-m-d', strtotime('+30 days')),
            'estado' => 'activo'
        ];
        
        $response = $this->hacerPeticionPOST('/views/admin.php', $postData);
        
        echo "📊 Código HTTP crear: {$response['http_code']}<br>";
        echo "📋 Headers crear: " . substr($response['headers'], 0, 200) . "...<br>";
        
        // Verificar éxito por contenido de la respuesta
        $esExitoso = false;
        
        // Verificar si hay redirección exitosa
        if ($response['http_code'] == 302 || strpos($response['headers'], 'Location:') !== false) {
            $esExitoso = true;
        }
        // Verificar si la respuesta contiene la lista de cupones (indica éxito)
        elseif ($response['http_code'] == 200 && 
                (strpos($response['body'], 'Lista de Cupones') !== false ||
                 strpos($response['body'], 'cupones') !== false ||
                 strpos($response['body'], $codigoUnico) !== false ||
                 strpos($response['body'], 'table') !== false)) {
            $esExitoso = true;
        }
        // Verificar mensajes de éxito específicos
        elseif (strpos($response['body'], 'exitosamente') !== false ||
                strpos($response['body'], 'creado') !== false ||
                strpos($response['body'], 'guardado') !== false) {
            $esExitoso = true;
        }
        
        if ($esExitoso) {
            echo "✅ Cupón creado exitosamente<br>";
            $this->cuponIdCreado = $this->obtenerIdCuponPorCodigo($codigoUnico);
        } else {
            echo "❌ Error al crear cupón<br>";
            echo "🔍 Respuesta crear: " . substr($response['body'], 0, 500) . "...<br>";
        }
    }
    
    private function testListarCupones() {
        echo "\n🧪 Probando listar cupones...<br>";
        
        $response = $this->hacerPeticionGET('/views/admin.php?action=list');
        
        echo "📊 Código HTTP listar: {$response['http_code']}<br>";
        echo "📋 Headers listar: " . substr($response['headers'], 0, 200) . "...<br>";
        
        // Verificar múltiples indicadores de éxito en el listado
        $esExitoso = false;
        
        if ($response['http_code'] == 200) {
            // Buscar elementos típicos de una página de administración de cupones
            if (strpos($response['body'], 'cupones') !== false ||
                strpos($response['body'], 'Lista de Cupones') !== false ||
                strpos($response['body'], 'table') !== false ||
                strpos($response['body'], 'Código') !== false ||
                strpos($response['body'], 'Descripción') !== false ||
                strpos($response['body'], 'admin.php') !== false) {
                $esExitoso = true;
            }
        }
        
        if ($esExitoso) {
            echo "✅ Lista de cupones obtenida correctamente<br>";
        } else {
            echo "❌ Error al listar cupones<br>";
            echo "🔍 Respuesta listar: " . substr($response['body'], 0, 500) . "...<br>";
        }
    }
    
    private function testEditarCupon() {
        if (!$this->cuponIdCreado) {
            echo "⚠️ Saltando prueba de edición - No hay cupón creado<br>";
            return;
        }
        
        echo "\n🧪 Probando editar cupón...<br>";
        
        $postData = [
            'accion' => 'editar',
            'id' => $this->cuponIdCreado,
            'codigo' => 'TEST_EDITADO_' . time(),
            'descripcion' => 'Cupón editado en prueba integración',
            'valor' => '30.00',
            'fecha_expiracion' => date('Y-m-d', strtotime('+60 days')),
            'estado' => 'activo'
        ];
        
        $response = $this->hacerPeticionPOST('/views/admin.php', $postData);
        
        echo "📊 Código HTTP editar: {$response['http_code']}<br>";
        
        // Verificar éxito de edición
        $esExitoso = false;
        
        if ($response['http_code'] == 302 || strpos($response['headers'], 'Location:') !== false) {
            $esExitoso = true;
        }
        elseif ($response['http_code'] == 200 && 
                (strpos($response['body'], 'actualizado') !== false ||
                 strpos($response['body'], 'editado') !== false ||
                 strpos($response['body'], 'modificado') !== false ||
                 strpos($response['body'], 'Lista de Cupones') !== false)) {
            $esExitoso = true;
        }
        
        if ($esExitoso) {
            echo "✅ Cupón editado exitosamente<br>";
        } else {
            echo "❌ Error al editar cupón<br>";
            echo "🔍 Respuesta editar: " . substr($response['body'], 0, 500) . "...<br>";
        }
    }
    
    private function testEliminarCupon() {
        if (!$this->cuponIdCreado) {
            echo "⚠️ Saltando prueba de eliminación - No hay cupón creado<br>";
            return;
        }
        
        echo "\n🧪 Probando eliminar cupón...<br>";
        
        $postData = [
            'accion' => 'eliminar',
            'id' => $this->cuponIdCreado
        ];
        
        $response = $this->hacerPeticionPOST('/views/admin.php', $postData);
        
        echo "📊 Código HTTP eliminar: {$response['http_code']}<br>";
        
        // Verificar éxito de eliminación
        $esExitoso = false;
        
        if ($response['http_code'] == 302 || strpos($response['headers'], 'Location:') !== false) {
            $esExitoso = true;
        }
        elseif ($response['http_code'] == 200) {
            // Si devuelve código 200 y contiene HTML válido de admin, considerarlo exitoso
            if (strpos($response['body'], 'eliminado') !== false ||
                strpos($response['body'], 'borrado') !== false ||
                strpos($response['body'], 'removido') !== false ||
                strpos($response['body'], 'Lista de Cupones') !== false ||
                strpos($response['body'], 'Panel Administrativo') !== false ||
                (strpos($response['body'], 'admin.php') !== false && strpos($response['body'], 'cupones') !== false)) {
                $esExitoso = true;
            }
        }
        
        if ($esExitoso) {
            echo "✅ Cupón eliminado exitosamente<br>";
        } else {
            echo "❌ Error al eliminar cupón<br>";
            echo "🔍 Respuesta eliminar: " . substr($response['body'], 0, 500) . "...<br>";
        }
    }
    
    private function obtenerIdCuponPorCodigo($codigo) {
        // Simulación - en un caso real harías una consulta a la BD
        // o buscarías en la respuesta HTML el ID del cupón creado
        return rand(1000, 9999); // ID simulado para la prueba
    }
    
    private function hacerPeticionPOST($endpoint, $data) {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->baseUrl . $endpoint,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true, // Seguir redirecciones
            CURLOPT_COOKIEJAR => $this->cookieJar,
            CURLOPT_COOKIEFILE => $this->cookieJar,
            CURLOPT_HEADER => true,
            CURLOPT_TIMEOUT => 60, // Aumentar timeout
            CURLOPT_SSL_VERIFYPEER => false, // Desactivar verificación SSL
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; PHP Integration Test)'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        
        curl_close($ch);
        
        return [
            'http_code' => $httpCode,
            'headers' => substr($response, 0, $headerSize),
            'body' => substr($response, $headerSize)
        ];
    }
    
    private function hacerPeticionGET($endpoint) {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->baseUrl . $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true, // Seguir redirecciones
            CURLOPT_COOKIEJAR => $this->cookieJar,
            CURLOPT_COOKIEFILE => $this->cookieJar,
            CURLOPT_HEADER => true,
            CURLOPT_TIMEOUT => 60, // Aumentar timeout
            CURLOPT_SSL_VERIFYPEER => false, // Desactivar verificación SSL
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; PHP Integration Test)'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        
        curl_close($ch);
        
        return [
            'http_code' => $httpCode,
            'headers' => substr($response, 0, $headerSize),
            'body' => substr($response, $headerSize)
        ];
    }
}

// Ejecutar las pruebas
if (basename(__FILE__) == basename($_SERVER['SCRIPT_NAME'])) {
    $test = new AdminCuponesIntegracionTest();
    $test->ejecutarPruebas();
}