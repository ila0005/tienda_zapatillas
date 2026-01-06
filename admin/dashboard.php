<?php
session_start();
if(!isset($_SESSION['id_usuario']) || $_SESSION['tipo']!=='admin'){
    header("Location: ../public/index.php"); exit;
}
include '../includes/header.php';
require_once __DIR__ . '/../app/logic/pedido.php';

$pedidos = getAllPedidos();
?>

<div class="container">
<h1>Panel de Administración</h1>
<p>Resumen de usuarios, productos y ventas</p>
</div>

<div class="container">
<h2>Pedidos recientes</h2>
<?php if (empty($pedidos)): ?>
    <p>No hay pedidos.</p>
<?php else: ?>
    <?php foreach($pedidos as $p): ?>
        <div style="background:#fff;padding:12px;border-radius:8px;margin-bottom:12px;box-shadow:var(--shadow-sm);">
            <strong>Pedido #<?php echo $p['id_pedido']; ?></strong>
            <div>Cliente: <?php echo htmlspecialchars($p['cliente']); ?> | Fecha: <?php echo $p['fecha_pedido']; ?> | Total: <?php echo $p['total']; ?> € | Estado: <?php echo htmlspecialchars($p['estado']); ?></div>
            <div>Dirección: <?php echo htmlspecialchars($p['calle'] . ' - ' . $p['ciudad'] . ' ' . $p['provincia']); ?></div>
            <details style="margin-top:8px;">
                <summary>Ver detalles</summary>
                <table style="width:100%;margin-top:8px;border-collapse:collapse;">
                    <tr style="background:#111;color:#fff"><th>Producto</th><th>Cantidad</th><th>Precio</th><th>Subtotal</th></tr>
                    <?php foreach($p['detalles'] as $d): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($d['nombre']); ?></td>
                            <td><?php echo $d['cantidad']; ?></td>
                            <td><?php echo $d['precio']; ?> €</td>
                            <td><?php echo number_format($d['precio']*$d['cantidad'],2); ?> €</td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </details>
            <div style="margin-top:8px; display:flex; gap:8px;">
                <button class="btn primary" onclick="location.href='editar_pedido.php?id=<?php echo $p['id_pedido']; ?>'">Editar</button>
                <button class="btn" onclick="deletePedido(<?php echo $p['id_pedido']; ?>)">Eliminar pedido</button>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
</div>

<script>
function deletePedido(id) {
    if (!confirm('¿Eliminar este pedido? Esta acción restaurará el stock y no se puede deshacer.')) return;
    fetch('eliminar_pedido.php', { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify({id_pedido: id}) })
    .then(r=>r.json()).then(data=>{
        if (data.success) {
            alert('Pedido eliminado');
            location.reload();
        } else alert('Error: '+(data.message||''));
    }).catch(err=>alert('Error de red: '+err));
}
</script>
