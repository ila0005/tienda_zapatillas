<?php
session_start();
header('Content-Type: application/json');
// Verificar que haya sesión y que el usuario sea de tipo 'cliente'
if(!isset($_SESSION['id_usuario']) || $_SESSION['tipo'] !== 'cliente'){
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

require_once __DIR__ . '/../app/logic/pedido.php';
require_once __DIR__ . '/../app/config/db.php';

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!$data || !isset($data['carrito'])) {
    echo json_encode(['success' => false, 'message' => 'Datos de carrito faltantes']);
    exit;
}

$items = $data['carrito'];

if (is_object($items) || (is_array($items) && array_keys($items) !== range(0, count($items)-1))) {
    $normalized = [];
    foreach ($items as $k => $v) {
        $normalized[] = [
            'id_producto' => $v['id_producto'] ?? $k,
            'cantidad' => intval($v['cantidad'] ?? 1),
            'precio' => floatval($v['precio'] ?? 0)
        ];
    }
    $items = $normalized;
} else {
}

try {
    // Requerir id_direccion: no permitir pedidos sin dirección
    if (!isset($data['id_direccion']) || !$data['id_direccion']) {
        echo json_encode(['success' => false, 'message' => 'Se requiere una dirección para confirmar el pedido.']);
        exit;
    }
    $id_direccion = intval($data['id_direccion']);

    // Verificar que la dirección pertenezca al usuario
    $stmt = $pdo->prepare("SELECT id_direccion FROM direcciones WHERE id_direccion = ? AND id_usuario = ? LIMIT 1");
    $stmt->execute([$id_direccion, $_SESSION['id_usuario']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        echo json_encode(['success' => false, 'message' => 'Dirección no válida para este usuario.']);
        exit;
    }
    // Crear el pedido
    $id_pedido = crearPedidoConDetalles($_SESSION['id_usuario'], $items, $id_direccion);
    echo json_encode(['success' => true, 'id_pedido' => $id_pedido]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

?>
