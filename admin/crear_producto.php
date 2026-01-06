<?php
session_start();
if ($_SESSION['tipo'] !== 'admin') exit;

require_once '../app/logic/producto.php';

if ($_POST) {
    $imagen = $_FILES['imagen']['name'];
    move_uploaded_file($_FILES['imagen']['tmp_name'], "../public/img/$imagen");

    crearProducto(
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
<h2>Nuevo Producto</h2>

<form method="POST" enctype="multipart/form-data">
<input type="text" name="nombre" placeholder="Nombre" required>
<textarea name="descripcion" placeholder="Descripción"></textarea>
<input type="number" step="0.01" name="precio" placeholder="Precio" required>
<input type="number" name="stock" placeholder="Stock" required>
<input type="file" name="imagen" required>
<button class="btn">Guardar</button>
</form>
</main>
