<?php
require_once __DIR__ . '/../config/db.php';

function crearUsuario($nombre, $email, $password, $tipo='cliente') {
    global $conn;
    $stmt = $conn->prepare("
        INSERT INTO usuarios (nombre, email, password, tipo)
        VALUES (?, ?, ?, ?)
    ");
    return $stmt->execute([$nombre, $email, password_hash($password, PASSWORD_DEFAULT), $tipo]);
}

function loginUsuario($email, $password) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if($usuario && password_verify($password, $usuario['password'])) {
        return $usuario;
    }
    return false;
}
