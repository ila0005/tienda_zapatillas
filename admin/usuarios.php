<?php
require_once '../includes/sesion.php';
require_once '../app/config/db.php';

// Solo admin
if ($_SESSION['tipo'] !== 'admin') {
    header("Location: ../public/home.php");
    exit;
}

// Crear usuario
if (isset($_POST['crear'])) {
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $tipo = $_POST['tipo'];
    $fecha = date('Y-m-d');

    $sql = "INSERT INTO usuarios (nombre,email,password,tipo,fecha_registro) VALUES (?,?,?,?,?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nombre,$email,$password,$tipo,$fecha]);
}

// Leer usuarios
$usuarios = $pdo->query("SELECT * FROM usuarios")->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<h2>Crear Usuario</h2>
<form method="POST">
    Nombre: <input type="text" name="nombre" required><br>
    Email: <input type="email" name="email" required><br>
    Contraseña: <input type="password" name="password" required><br>
    Tipo: 
    <select name="tipo">
        <option value="cliente">Cliente</option>
        <option value="admin">Admin</option>
    </select><br>
    <button type="submit" name="crear">Crear Usuario</button>
</form>

<h2>Usuarios Existentes</h2>
<table border="1">
<tr><th>ID</th><th>Nombre</th><th>Email</th><th>Tipo</th><th>Acciones</th></tr>
<?php foreach($usuarios as $u): ?>
<tr>
    <td><?php echo $u['id_usuario']; ?></td>
    <td><?php echo $u['nombre']; ?></td>
    <td><?php echo $u['email']; ?></td>
    <td><?php echo $u['tipo']; ?></td>
    <td>
        <a href="editar_usuario.php?id=<?php echo $u['id_usuario']; ?>">Editar</a> |
        <a href="eliminar_usuario.php?id=<?php echo $u['id_usuario']; ?>">Eliminar</a>
    </td>
</tr>
<?php endforeach; ?>
</table>

<?php include '../includes/footer.php'; ?>