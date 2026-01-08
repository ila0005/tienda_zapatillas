<?php
session_start();
if ($_SESSION['tipo'] !== 'admin') exit;

require_once '../app/logic/producto.php';

$producto = getProductoById($_GET['id']);

// Procesar formulario edición
if ($_POST) {
    $imagen = $_FILES['imagen']['name'] ?: null;

    if ($imagen) {
        move_uploaded_file($_FILES['imagen']['tmp_name'], "../public/img/$imagen");
    }

    actualizarProducto(
        $producto['id_producto'],
        $_POST['nombre'],
        $_POST['descripcion'],
        $_POST['precio'],
        $_POST['stock'],
        $imagen
    );

    header("Location: productos.php");
}

include '../includes/header.php';
?>

<main class="container">
<h2>Editar Producto</h2>

<form method="POST" enctype="multipart/form-data">
<input type="text" name="nombre" value="<?= $producto['nombre'] ?>" required>
<textarea name="descripcion"><?= $producto['descripcion'] ?></textarea>
<input type="number" step="0.01" name="precio" value="<?= $producto['precio'] ?>" required>
<input type="number" name="stock" value="<?= $producto['stock'] ?>" required>
<input type="file" name="imagen">
<button class="btn">Actualizar</button>
</form>
</main>
