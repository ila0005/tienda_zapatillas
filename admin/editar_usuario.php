<?php
require_once '../includes/sesion.php';
require_once '../app/config/db.php';
if ($_SESSION['tipo'] !== 'admin') { header("Location: ../public/home.php"); exit; }

$id = intval($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id_usuario = ? LIMIT 1");
$stmt->execute([$id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
        header("Location: usuarios.php"); exit;
}

if (isset($_POST['guardar'])) {
        $nombre = $_POST['nombre'];
        $email = $_POST['email'];
        $tipo = $_POST['tipo'];
        $sql = "UPDATE usuarios SET nombre=?, email=?, tipo=? WHERE id_usuario=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombre,$email,$tipo,$id]);
        header("Location: usuarios.php");
}
?>

<?php include '../includes/header.php'; ?>

<div class="container">
    <h2>Editar Usuario</h2>
    <form method="POST">
        <label>Nombre</label>
        <input type="text" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>

        <label>Email</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>

        <label>Tipo</label>
        <select name="tipo">
                <option value="cliente" <?php if($usuario['tipo']=='cliente') echo 'selected'; ?>>Cliente</option>
                <option value="admin" <?php if($usuario['tipo']=='admin') echo 'selected'; ?>>Admin</option>
        </select>

        <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:6px">
            <a href="usuarios.php" class="btn ghost">Cancelar</a>
            <button class="btn primary" type="submit" name="guardar">Guardar Cambios</button>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>