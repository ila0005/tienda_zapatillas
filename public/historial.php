<?php
session_start();
if(!isset($_SESSION['id_usuario']) || $_SESSION['tipo']!=='cliente'){
    header("Location: index.php"); exit;
}
include '../includes/header.php';
require_once __DIR__ . '/../app/logic/pedido.php';

$pedidos = getPedidosByUsuario($_SESSION['id_usuario']);
?>

<div class="container">
<h1>Historial de Compras</h1>
<p>Aquí se mostrarán tus compras anteriores.</p>
<?php if (empty($pedidos)): ?>
    <p>No tienes pedidos todavía.</p>
<?php else: ?>
    <?php foreach($pedidos as $p): ?>
        <h3>Pedido #<?php echo $p['id_pedido']; ?> — <?php echo $p['fecha_pedido']; ?> — Total: <?php echo $p['total']; ?> €</h3>
        <table>
        <tr><th>Producto</th><th>Cantidad</th><th>Precio</th><th>Subtotal</th></tr>
        <?php foreach($p['detalles'] as $d): ?>
            <tr>
                <td><?php echo $d['nombre']; ?></td>
                <td><?php echo $d['cantidad']; ?></td>
                <td><?php echo $d['precio']; ?> €</td>
                <td><?php echo number_format($d['precio'] * $d['cantidad'], 2); ?> €</td>
            </tr>
        <?php endforeach; ?>
        </table>
    <?php endforeach; ?>
<?php endif; ?>
</div>
