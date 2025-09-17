<?php
/**
 * Prueba de Integración - Endpoint de Canje de Cupones
 * Prueba el endpoint AJAX POST /views/usuario.php
 */

class CanjeIntegracionTest {
    private $baseUrl;
    private $cookieJar;
    
    public function __construct() {
        $this->baseUrl = 'http://54.207.61.138';
        $this->cookieJar = tempnam(sys_get_temp_dir(), 'cookies_canje');
    }
    
    public function ejecutarPruebas() {
        echo "<br>=== PRUEBAS DE INTEGRACIÓN - CANJE CUPONES ===<br>";
        
        // Login como usuario normal
        $this->loginComoUsuario();
        
        $this->testCanjearCuponValido();
        $this->testCanjearCuponInvalido();
        $this->testCanjearCuponYaCanjeado();
        
        // Limpiar
        unlink($this->cookieJar);
        
        echo "\n✅ Todas las pruebas de canje completadas<br>";
    }
    
    private function loginComoUsuario() {
        echo "\n🔐 Haciendo login como usuario...<br>";
        
        $postData = [
            'action' => 'login',
            'email' => 'usuario@test.com',
            'password' => '123456'
        ];
        
        $response = $this->hacerPeticionPOST('/views/login.php', $postData);
        
        echo "📊 Código HTTP: {$response['http_code']}<br>";
        echo "📋 Headers: " . substr($response['headers'], 0, 200) . "...<br>";
        
        // Verificar si hay redirección o si el login fue exitoso
        if ($response['http_code'] == 302 || 
            strpos($response['headers'], 'Location:') !== false ||
            strpos($response['body'], 'usuario.php') !== false) {
            echo "✅ Login usuario exitoso<br>";
        } else {
            echo "❌ Error en login usuario<br>";
            echo "🔍 Respuesta del servidor: " . substr($response['body'], 0, 500) . "...<br>";
        }
    }
    
    private function testCanjearCuponValido() {
        echo "\n🧪 Probando canje de cupón válido...<br>";
        
        // Primero crear un cupón válido (necesitarías hacer esto como admin)
        $codigoCupon = 'DESCUENTO20'; // Asumiendo que existe
        
        $postData = [
            'action' => 'canjear_cupon_ajax',
            'codigo' => $codigoCupon
        ];
        
        $response = $this->hacerPeticionAJAX('/views/usuario.php', $postData);
        
        echo "📊 Código HTTP canje: {$response['http_code']}<br>";
        echo "📋 Headers canje: " . substr($response['headers'], 0, 200) . "...<br>";
        
        $data = json_decode($response['body'], true);
        
        // Verificar múltiples formas de respuesta exitosa
        $esExitoso = false;
        
        if ($data && isset($data['success'])) {
            if ($data['success']) {
                $esExitoso = true;
                echo "✅ Cupón canjeado exitosamente<br>";
                echo "   Mensaje: {$data['message']}<br>";
            } else {
                echo "⚠️ Cupón no pudo ser canjeado: {$data['message']}<br>";
            }
        } elseif ($response['http_code'] == 200) {
            // Verificar si la respuesta contiene indicadores de éxito
            if (strpos($response['body'], 'success') !== false ||
                strpos($response['body'], 'canjeado') !== false ||
                strpos($response['body'], 'exitoso') !== false) {
                $esExitoso = true;
                echo "✅ Cupón procesado (respuesta no JSON pero exitosa)<br>";
            } else {
                echo "❌ Respuesta inválida del servidor<br>";
                echo "🔍 Respuesta: " . substr($response['body'], 0, 300) . "...<br>";
            }
        } else {
            echo "❌ Error HTTP: {$response['http_code']}<br>";
            echo "🔍 Respuesta: " . substr($response['body'], 0, 300) . "...<br>";
        }
    }
    
    private function testCanjearCuponInvalido() {
        echo "\n🧪 Probando canje de cupón inválido...<br>";
        
        $postData = [
            'action' => 'canjear_cupon_ajax',
            'codigo' => 'CUPON_INEXISTENTE_123'
        ];
        
        $response = $this->hacerPeticionAJAX('/views/usuario.php', $postData);
        
        echo "📊 Código HTTP inválido: {$response['http_code']}<br>";
        
        $data = json_decode($response['body'], true);
        
        $esRechazadoCorrectamente = false;
        
        if ($data && isset($data['success']) && !$data['success']) {
            $esRechazadoCorrectamente = true;
            echo "✅ Cupón inválido rechazado correctamente<br>";
            echo "   Mensaje: {$data['message']}<br>";
        } elseif ($response['http_code'] == 200) {
            // Verificar si la respuesta indica error o rechazo
            if (strpos($response['body'], 'error') !== false ||
                strpos($response['body'], 'inválido') !== false ||
                strpos($response['body'], 'no existe') !== false ||
                strpos($response['body'], 'false') !== false) {
                $esRechazadoCorrectamente = true;
                echo "✅ Cupón inválido rechazado (respuesta no JSON)<br>";
            }
        }
        
        if (!$esRechazadoCorrectamente) {
            echo "❌ Error: Cupón inválido no fue rechazado<br>";
            echo "🔍 Respuesta: " . substr($response['body'], 0, 300) . "...<br>";
        }
    }
    
    private function testCanjearCuponYaCanjeado() {
        echo "\n🧪 Probando canje de cupón ya canjeado...<br>";
        
        // Intentar canjear el mismo cupón dos veces
        $codigoCupon = 'DESCUENTO20';
        
        // Primer canje
        $postData = [
            'action' => 'canjear_cupon_ajax',
            'codigo' => $codigoCupon
        ];
        
        $this->hacerPeticionAJAX('/views/usuario.php', $postData);
        
        // Segundo canje (debería fallar)
        $response = $this->hacerPeticionAJAX('/views/usuario.php', $postData);
        
        echo "📊 Código HTTP segundo canje: {$response['http_code']}<br>";
        
        $data = json_decode($response['body'], true);
        
        $esRechazadoCorrectamente = false;
        
        if ($data && isset($data['success']) && !$data['success']) {
            $esRechazadoCorrectamente = true;
            echo "✅ Cupón ya canjeado rechazado correctamente<br>";
            echo "   Mensaje: {$data['message']}<br>";
        } elseif ($response['http_code'] == 200) {
            // Verificar si la respuesta indica que ya fue canjeado
            if (strpos($response['body'], 'ya canjeado') !== false ||
                strpos($response['body'], 'ya utilizado') !== false ||
                strpos($response['body'], 'error') !== false ||
                strpos($response['body'], 'false') !== false) {
                $esRechazadoCorrectamente = true;
                echo "✅ Cupón ya canjeado rechazado (respuesta no JSON)<br>";
            }
        }
        
        if (!$esRechazadoCorrectamente) {
            echo "⚠️ Advertencia: Cupón ya canjeado no fue rechazado (puede que no existiera)<br>";
            echo "🔍 Respuesta: " . substr($response['body'], 0, 300) . "...<br>";
        }
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
    
    private function hacerPeticionAJAX($endpoint, $data) {
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
            CURLOPT_HTTPHEADER => [
                'X-Requested-With: XMLHttpRequest',
                'Content-Type: application/x-www-form-urlencoded'
            ],
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
    $test = new CanjeIntegracionTest();
    $test->ejecutarPruebas();
}