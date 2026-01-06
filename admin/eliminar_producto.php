<?php
session_start();
if ($_SESSION['tipo'] !== 'admin') exit;

require_once '../app/logic/producto.php';

eliminarProducto($_GET['id']);
header("Location: productos.php");
