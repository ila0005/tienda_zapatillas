<?php
// Iniciar sesión si no está iniciada
if(session_status() == PHP_SESSION_NONE) session_start();
$logoutPath = (isset($_SESSION['tipo']) && $_SESSION['tipo']=='admin') ? '../public/logout.php' : 'logout.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>SneakersILA</title>
<link rel="stylesheet" href="<?= (isset($_SESSION['tipo']) && $_SESSION['tipo']=='admin') ? '../public/css/style.css' : 'css/style.css' ?>">
</head>
<body>

<header>
    <div class="container">
        <h1 class="logo">SneakersILA</h1>
        <nav>
            <ul>
                <?php if(isset($_SESSION['tipo']) && $_SESSION['tipo']=='admin'): ?>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="usuarios.php">Usuarios</a></li>
                    <li><a href="productos.php">Productos</a></li>
                <?php elseif(isset($_SESSION['tipo']) && $_SESSION['tipo']=='cliente'): ?>
                    <li><a href="home.php">Inicio</a></li>
                    <li><a href="productos.php">Productos</a></li>
                    <li><a href="carrito.php" id="open-cart">Carrito</a></li>
                    <li><a href="historial.php">Historial</a></li>
                <?php endif; ?>
                <?php if(isset($_SESSION['id_usuario'])): ?>
                    <li class="right"><a href="<?= $logoutPath ?>">Cerrar sesión</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>
