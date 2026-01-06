<?php
session_start();

// Si no hay sesión iniciada, redirigir al login
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../public/index.php");
    exit;
}
