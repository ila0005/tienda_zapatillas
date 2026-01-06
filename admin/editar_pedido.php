<?php
session_start();
if(!isset($_SESSION['id_usuario']) || $_SESSION['tipo']!=='admin'){
    header("Location: ../public/index.php"); exit;
}
require_once __DIR__ . '/../app/logic/pedido.php';
require_once __DIR__ . '/../app/config/db.php';
include '../includes/header.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id) { echo '<div class="container">ID de pedido inválido</div>'; exit; }

$pedido = getPedidoById($id);
if (!$pedido) { echo '<div class="container">Pedido no encontrado</div>'; exit; }

// Direcciones del cliente
$stmt = $conn->prepare("SELECT * FROM direcciones WHERE id_usuario = ?");
$stmt->execute([$pedido['id_usuario']]);
$direcciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Procesar POST de actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $estado = $_POST['estado'] ?? $pedido['estado'];
    $id_dir = intval($_POST['id_direccion'] ?? 0);
    $items = [];
    if (isset($_POST['cantidad']) && is_array($_POST['cantidad'])) {
        foreach ($_POST['cantidad'] as $pid => $cant) {
            $items[] = ['id_producto' => intval($pid), 'cantidad' => intval($cant), 'precio' => floatval($_POST['precio'][$pid] ?? 0)];
        }
    }
    try {
        updatePedido($id, $items, $id_dir, $estado);
        header('Location: dashboard.php'); exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

?>
<div class="container">
    <h1>Editar Pedido #<?php echo $pedido['id_pedido']; ?></h1>
    <?php if (!empty($error)): ?><div class="alert"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    <form method="POST">
        <div>
            <label>Cliente: </label> <?php echo htmlspecialchars($pedido['cliente']); ?>
        </div>
        <div>
            <label>Dirección:</label>
            <select name="id_direccion">
                <?php foreach($direcciones as $d): ?>
                    <option value="<?php echo $d['id_direccion']; ?>" <?php if($d['id_direccion']==$pedido['id_direccion']) echo 'selected'; ?>><?php echo htmlspecialchars($d['calle'].' - '.$d['ciudad']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label>Estado:</label>
            <select name="estado">
                <?php $estados = ['confirmado','procesando','enviado','completado','cancelado']; foreach($estados as $e): ?>
                    <option value="<?php echo $e; ?>" <?php if($pedido['estado']==$e) echo 'selected'; ?>><?php echo $e; ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <h3>Productos</h3>
        <table>
            <tr><th>Producto</th><th>Cantidad</th><th>Precio</th><th>Subtotal</th></tr>
            <?php foreach($pedido['detalles'] as $d): ?>
            <tr>
                <td><?php echo htmlspecialchars($d['nombre']); ?></td>
                <td><input type="number" name="cantidad[<?php echo $d['id_producto']; ?>]" value="<?php echo $d['cantidad']; ?>" min="0"></td>
                <td><input type="text" name="precio[<?php echo $d['id_producto']; ?>]" value="<?php echo $d['precio']; ?>"></td>
                <td><?php echo number_format($d['precio']*$d['cantidad'],2); ?> €</td>
            </tr>
            <?php endforeach; ?>
        </table>
        <div style="margin-top:12px;"><button class="btn primary">Guardar cambios</button> <a class="btn ghost" href="dashboard.php">Cancelar</a></div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
