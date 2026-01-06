<?php
require_once '../includes/sesion.php';
require_once '../app/config/db.php';
if ($_SESSION['tipo'] !== 'admin') { header("Location: ../public/home.php"); exit; }

$id = $_GET['id'];
$pdo->query("DELETE FROM usuarios WHERE id_usuario=$id");
header("Location: usuarios.php");
