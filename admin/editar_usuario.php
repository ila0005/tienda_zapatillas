<?php
require_once '../includes/sesion.php';
require_once '../app/config/db.php';
if ($_SESSION['tipo'] !== 'admin') { header("Location: ../public/home.php"); exit; }

$id = $_GET['id'];
$usuario = $pdo->query("SELECT * FROM usuarios WHERE id_usuario=$id")->fetch();

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

<form method="POST">
    Nombre: <input type="text" name="nombre" value="<?php echo $usuario['nombre']; ?>" required><br>
    Email: <input type="email" name="email" value="<?php echo $usuario['email']; ?>" required><br>
    Tipo:
    <select name="tipo">
        <option value="cliente" <?php if($usuario['tipo']=='cliente') echo 'selected'; ?>>Cliente</option>
        <option value="admin" <?php if($usuario['tipo']=='admin') echo 'selected'; ?>>Admin</option>
    </select><br>
    <button type="submit" name="guardar">Guardar Cambios</button>
</form>