<?php
session_start();
if(!isset($_SESSION['id_usuario']) || $_SESSION['tipo']!=='cliente'){
    header("Location: index.php"); exit;
}
include '../includes/header.php';
?>

<div class="container">
<h1>Historial de Compras</h1>
<p>Aquí se mostrarán tus compras anteriores.</p>
<!-- Tabla ejemplo -->
<table>
<tr><th>Fecha</th><th>Producto</th><th>Cantidad</th><th>Total</th></tr>
</table>
</div>
