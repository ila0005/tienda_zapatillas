<?php
session_start();
require_once __DIR__ . '/../app/logic/usuario.php';
include __DIR__.'/../includes/header.php';

// Redirigir si ya hay sesión iniciada
if (isset($_SESSION['id_usuario'])) {
    header("Location: home.php");
    exit;
}
$error='';

// Procesar formulario de registro
if($_SERVER['REQUEST_METHOD']=='POST'){
    if(crearUsuario($_POST['nombre'],$_POST['email'],$_POST['password'],'cliente')){
        header("Location: index.php");
        exit;
    } else {
        $error="Error al registrar el usuario";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Registro</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container">
<h1>Registro</h1>
</div>
<?php if($error) echo "<p class='alert'>$error</p>"; ?>
<form method="POST">
<input type="text" name="nombre" placeholder="Nombre completo" required>
<input type="email" name="email" placeholder="Correo electrónico" required>
<input type="password" name="password" placeholder="Contraseña" required>
<button type="submit">Registrarse</button>
</form>
<p style="text-align:center;"><a href="index.php">Volver al login</a></p>

</body>
</html>
