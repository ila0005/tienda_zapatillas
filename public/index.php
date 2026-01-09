<?php
// Habilitar reporte de errores para desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


session_start();
require_once(__DIR__ . '/../app/logic/usuario.php');
require_once __DIR__ . '/../includes/header.php';


$error = '';
// Procesar formulario de login
if ($_POST) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $usuario = loginUsuario($email, $password);
    // Si el login es exitoso
    if ($usuario) {
        $_SESSION['id_usuario'] = $usuario['id_usuario'];
        $_SESSION['tipo'] = $usuario['tipo'];
        $_SESSION['nombre'] = $usuario['nombre'];

        // Redirigir según tipo
        if($usuario['tipo'] === 'admin') {
            header("Location: /admin/dashboard.php");
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
