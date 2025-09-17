<?php
/**
 * Instalación automática del sistema de cupones
 */

// Leer archivo .env
$env = [];
$lines = file('.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    if (strpos($line, '=') !== false) {
        list($key, $value) = explode('=', $line, 2);
        $env[trim($key)] = trim($value);
    }
}

echo "Iniciando instalación...\n";
echo "Host: {$env['DB_HOST']}\n";
echo "Base de datos: {$env['DB_DATABASE']}\n";

try {
    // Probar conexión básica primero
    echo "Probando conexión al servidor MySQL...\n";
    $dsn = "mysql:host={$env['DB_HOST']}";
    $pdo = new PDO($dsn, $env['DB_USERNAME'], $env['DB_PASSWORD'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 10
    ]);
    echo "✓ Conexión al servidor MySQL exitosa\n";
    

    
    // Ejecutar setup.sql línea por línea
    echo "Ejecutando configuración de tablas...\n";
    $sql = file_get_contents('install/setup.sql');
    
    // Ejecutar setup.sql completo
    echo "Ejecutando setup.sql...\n";
    
    $pdo->exec($sql);
    echo "✓ Configuración completada\n";
    echo "\nINSTALACIÓN EXITOSA: El sistema está listo para usar.";
    
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Connection timed out') !== false) {
        echo "ERROR: No se puede conectar al servidor de base de datos.\n";
        echo "Verifica que:\n";
        echo "1. El servidor MySQL esté ejecutándose\n";
        echo "2. La IP {$env['DB_HOST']} sea correcta\n";
        echo "3. El puerto 3306 esté abierto\n";
        echo "4. Las credenciales sean válidas\n";
    } else {
        echo "ERROR EN LA INSTALACIÓN: " . $e->getMessage();
    }
} catch (Exception $e) {
    echo "ERROR EN LA INSTALACIÓN: " . $e->getMessage();
}
?>