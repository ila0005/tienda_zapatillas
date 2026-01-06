<?php
session_start();

if (!isset($_SESSION['id_usuario']) || $_SESSION['tipo'] !== 'cliente') {
    header("Location: index.php");
    exit;
}

require_once '../app/logic/producto.php';

$id = $_POST['id_producto'];
$producto = getProductoById($id);

if (!$producto || $producto['stock'] <= 0) {
    header("Location: productos.php");
    exit;
}

if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

if (isset($_SESSION['carrito'][$id])) {
    $_SESSION['carrito'][$id]['cantidad']++;
} else {
    $_SESSION['carrito'][$id] = [
        'id_producto' => $producto['id_producto'],
        'nombre' => $producto['nombre'],
        'precio' => $producto['precio'],
        'cantidad' => 1
    ];
}

header("Location: carrito.php");
