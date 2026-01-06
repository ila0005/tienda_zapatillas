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
    <div class="producto" id="producto-<?= $p['id_producto'] ?>">
        <img src="img/<?= $p['imagen'] ?>" alt="<?= $p['nombre'] ?>">
        <h3><?= $p['nombre'] ?></h3>
        <p><?= $p['descripcion'] ?></p>
        <strong class="precio"><?= $p['precio'] ?> €</strong>

        <?php if($p['stock'] > 0): ?>
            <div class="actions">
                <input class="qty-input" type="number" min="1" max="<?= $p['stock'] ?>" value="1" aria-label="Cantidad" />
                <button class="btn primary add-to-cart"
                    data-id="<?= $p['id_producto'] ?>"
                    data-nombre="<?= htmlspecialchars($p['nombre'], ENT_QUOTES) ?>"
                    data-precio="<?= $p['precio'] ?>"
                    data-imagen="<?= $p['imagen'] ?>"
                    data-stock="<?= $p['stock'] ?>"
                >Añadir al carrito</button>
            </div>
        <?php else: ?>
            <span class="sin-stock">Sin stock</span>
        <?php endif; ?>
    </div>
<?php endforeach; ?>
</div>
</main>

<?php
$tipoUsuario = isset($_SESSION['tipo']) ? $_SESSION['tipo'] : '';
$usuarioLogueado = isset($_SESSION['id_usuario']) ? true : false;
?>
<script>
    const USER = { tipo: '<?= $tipoUsuario ?>', logged: <?= $usuarioLogueado ? 'true' : 'false' ?> };
</script>
<?php include '../includes/footer.php'; ?>
