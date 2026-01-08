<?php
require_once __DIR__ . '/../config/db.php';

// Obtener todos los productos
function getProductos() {
    global $conn;
    $stmt = $conn->query("SELECT * FROM productos");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
// Obtener un producto 
function getProductoById($id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM productos WHERE id_producto = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
// Crear un nuevo producto
function crearProducto($nombre, $descripcion, $precio, $stock, $imagen) {
    global $conn;
    $stmt = $conn->prepare("
        INSERT INTO productos (nombre, descripcion, precio, stock, imagen)
        VALUES (?, ?, ?, ?, ?)
    ");
    return $stmt->execute([$nombre, $descripcion, $precio, $stock, $imagen]);
}
// Actualizar un producto existente
function actualizarProducto($id, $nombre, $descripcion, $precio, $stock, $imagen = null) {
    global $conn;
    if ($imagen) {
        $stmt = $conn->prepare("
            UPDATE productos 
            SET nombre=?, descripcion=?, precio=?, stock=?, imagen=?
            WHERE id_producto=?
        ");
        return $stmt->execute([$nombre, $descripcion, $precio, $stock, $imagen, $id]);
    } else {
        $stmt = $conn->prepare("
            UPDATE productos 
            SET nombre=?, descripcion=?, precio=?, stock=?
            WHERE id_producto=?
        ");
        return $stmt->execute([$nombre, $descripcion, $precio, $stock, $id]);
    }
}
// Eliminar un producto 
function eliminarProducto($id) {
    global $conn;
    $stmt = $conn->prepare("DELETE FROM productos WHERE id_producto = ?");
    return $stmt->execute([$id]);
}
