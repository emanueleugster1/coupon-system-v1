<?php
// Función simple para leer .env
function loadEnv($path) {
    if (!file_exists($path)) {
        die('Archivo .env no encontrado');
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
    }
}

// Cargar variables de entorno
loadEnv(__DIR__ . '/../.env');

// Configuración desde .env
$host = $_ENV['DB_HOST'];
$dbname = $_ENV['DB_DATABASE'];
$username = $_ENV['DB_USERNAME'];
$password = $_ENV['DB_PASSWORD'];
$charset = $_ENV['DB_CHARSET'];

// Conexión PDO con manejo de errores mejorado
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=$charset", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 10,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ]);
} catch (PDOException $e) {
    // Log del error para debugging
    error_log("Error de conexión DB: " . $e->getMessage());
    
    // Mostrar error más específico
    if (strpos($e->getMessage(), 'Unknown database') !== false) {
        die('Error: La base de datos no existe. Ejecuta install.php primero.');
    } elseif (strpos($e->getMessage(), 'Access denied') !== false) {
        die('Error: Credenciales de base de datos incorrectas.');
    } elseif (strpos($e->getMessage(), 'Connection refused') !== false) {
        die('Error: No se puede conectar al servidor MySQL.');
    } else {
        die('Error de conexión: ' . $e->getMessage());
    }
}

// Retornar la conexión PDO
return $pdo;
?>