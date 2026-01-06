<?php
session_start();
require_once '../app/logic/producto.php';
include '../includes/header.php';

$productos = getProductos();
?>

<main class="container">
<h2>Zapatillas</h2>

<div class="grid-productos">
<?php foreach($productos as $p): ?>
    <div class="producto">
        <img src="img/<?= $p['imagen'] ?>" alt="<?= $p['nombre'] ?>">
        <h3><?= $p['nombre'] ?></h3>
        <p><?= $p['descripcion'] ?></p>
        <strong><?= $p['precio'] ?> €</strong>

        <?php if($p['stock'] > 0): ?>
            <form action="agregar_carrito.php" method="POST">
                <input type="hidden" name="id_producto" value="<?= $p['id_producto'] ?>">
                <button class="btn">Añadir al carrito</button>
            </form>
        <?php else: ?>
            <span class="sin-stock">Sin stock</span>
        <?php endif; ?>
    </div>
<?php endforeach; ?>
</div>
</main>
