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

<div class="container">
  <h2>Crear Usuario</h2>
  <form method="POST">
    <label>Nombre</label>
    <input type="text" name="nombre" required>

    <label>Email</label>
    <input type="email" name="email" required>

    <label>Contraseña</label>
    <input type="password" name="password" required>

    <label>Tipo</label>
    <select name="tipo">
        <option value="cliente">Cliente</option>
        <option value="admin">Admin</option>
    </select>

    <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:6px">
      <button class="btn primary" type="submit" name="crear">Crear Usuario</button>
    </div>
  </form>

  <h2 style="margin-top:28px">Usuarios Existentes</h2>
  <table class="" aria-describedby="Listado de usuarios">
    <thead>
      <tr><th>ID</th><th>Nombre</th><th>Email</th><th>Tipo</th><th>Acciones</th></tr>
    </thead>
    <tbody>
    <?php foreach($usuarios as $u): ?>
    <tr>
        <td><?php echo (int)$u['id_usuario']; ?></td>
        <td><?php echo htmlspecialchars($u['nombre']); ?></td>
        <td><?php echo htmlspecialchars($u['email']); ?></td>
        <td><?php echo htmlspecialchars($u['tipo']); ?></td>
        <td>
            <a class="btn ghost" href="editar_usuario.php?id=<?php echo (int)$u['id_usuario']; ?>">Editar</a>
            <a class="btn" style="margin-left:6px;background:#b33;color:#fff;padding:6px 8px;border-radius:8px;text-decoration:none" href="eliminar_usuario.php?id=<?php echo (int)$u['id_usuario']; ?>">Eliminar</a>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php include '../includes/footer.php'; ?>