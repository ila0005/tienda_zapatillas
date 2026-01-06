<?php
session_start();
require_once '../app/logic/usuario.php';
include '../includes/header.php';

$error = '';

if ($_POST) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $usuario = loginUsuario($email, $password);

    if ($usuario) {
        $_SESSION['id_usuario'] = $usuario['id_usuario'];
        $_SESSION['tipo'] = $usuario['tipo'];
        $_SESSION['nombre'] = $usuario['nombre'];

        // Redirigir según tipo
        if($usuario['tipo'] === 'admin') {
            header("Location: ../admin/dashboard.php");
        } else {
            header("Location: home.php");
        }
        exit;
    } else {
        $error = "Email o contraseña incorrectos.";
    }
}
?>

<main class="container">
<h2>Iniciar Sesión</h2>

<?php if($error): ?>
<p class="error"><?= $error ?></p>
<?php endif; ?>

<form method="POST">
<input type="email" name="email" placeholder="Email" required>
<input type="password" name="password" placeholder="Contraseña" required>
<button class="btn">Entrar</button>
<p>¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a></p>
</form>
</main>
