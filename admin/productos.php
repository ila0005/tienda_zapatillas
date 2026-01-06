<?php
session_start();
if (!isset($_SESSION['id_usuario']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: ../public/index.php");
    exit;
}

require_once '../app/logic/producto.php';
$productos = getProductos();

include '../includes/header.php';
?>

<main class="container">
<h2>Gestión de Productos</h2>

<a href="crear_producto.php" class="btn">➕ Nuevo producto</a>

<table class="tabla">
<tr>
    <th>Imagen</th>
    <th>Nombre</th>
    <th>Precio</th>
    <th>Stock</th>
    <th>Acciones</th>
</tr>

<?php foreach($productos as $p): ?>
<tr>
    <td><img src="../public/img/<?= $p['imagen'] ?>" width="80"></td>
    <td><?= $p['nombre'] ?></td>
    <td><?= $p['precio'] ?> €</td>
    <td><?= $p['stock'] ?></td>
    <td>
        <a href="producto_editar.php?id=<?= $p['id_producto'] ?>">✏️</a>
        <a href="producto_eliminar.php?id=<?= $p['id_producto'] ?>" onclick="return confirm('¿Eliminar producto?')">🗑</a>
    </td>
</tr>
<?php endforeach; ?>
</table>
</main>
