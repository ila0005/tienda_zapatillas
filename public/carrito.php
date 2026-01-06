<?php
session_start();
if(!isset($_SESSION['id_usuario']) || $_SESSION['tipo']!=='cliente'){
    header("Location: index.php"); exit;
}
include '../includes/header.php';
require_once __DIR__.'/../app/logic/producto.php';

// TODO: Recuperar carrito de BD (tabla carritos y detalle_pedido)
?>

<div class="container">
<h1>Carrito de Compras</h1>
<p>Aquí aparecerán los productos agregados al carrito.</p>
<!-- Tabla ejemplo -->
<table>
<tr><th>Producto</th><th>Cantidad</th><th>Precio</th><th>Subtotal</th></tr>
<!-- Repetir por cada producto del carrito -->
</table>
<button>Confirmar compra</button>
</div>
