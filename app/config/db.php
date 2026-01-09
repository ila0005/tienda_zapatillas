<?php 
$host = 'sql312.infinityfree.com';        // Host de InfinityFree
$db   = 'if0_40860046_tienda_zapatillas'; // Nombre de la base de datos
$user = 'if0_40860046';                   // Usuario de la base de datos
$pass = 'Iaguado2025';                    // Contraseña
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// Configurar la conexión PDO
try {
    $conn = new PDO($dsn, $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo = $conn;

} catch(PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
    exit;
}
