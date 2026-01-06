<?php
session_start();
if (!isset($_SESSION['id_usuario']) || $_SESSION['tipo'] !== 'cliente') {
    header("Location: index.php");
    exit;
}

require_once '../app/logic/producto.php';
include '../includes/header.php';

$productos = getProductos();
?>

<main class="container">
<section class="hero">
    <h1>JUST DO IT.</h1>
    <p>Zapatillas diseñadas para rendir al máximo</p>
    <a href="productos.php" class="btn-grande">Comprar ahora</a>
</section>

<h2>Productos destacados</h2>
<div class="grid-productos">
<?php foreach(array_slice($productos, 0, 4) as $p): ?>
    <a class="producto-link" href="productos.php?id=<?= $p['id_producto'] ?>">
    <div class="producto">
        <img src="img/<?= $p['imagen'] ?>" alt="<?= $p['nombre'] ?>">
        <h3><?= $p['nombre'] ?></h3>
        <strong><?= $p['precio'] ?> €</strong>
    </div>
    </a>
<?php endforeach; ?>
</div>
</main>
<?php include '../includes/footer.php'; ?>
