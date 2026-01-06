<?php
session_start();
if(!isset($_SESSION['id_usuario']) || $_SESSION['tipo']!=='admin'){
    header("Location: ../public/index.php"); exit;
}
include '../includes/header.php';
?>

<div class="container">
<h1>Panel de Administración</h1>
<p>Resumen de usuarios, productos y ventas</p>
<!-- Aquí puedes añadir gráficos, estadísticas o tablas -->
</div>
