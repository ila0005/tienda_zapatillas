<?php
session_start();
header('Content-Type: application/json');

if(!isset($_SESSION['id_usuario']) || $_SESSION['tipo'] !== 'cliente'){
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

require_once __DIR__ . '/../app/logic/pedido.php';

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!$data || !isset($data['carrito'])) {
    echo json_encode(['success' => false, 'message' => 'Datos de carrito faltantes']);
    exit;
}

$items = $data['carrito'];
// Normalize items: expect object map or array
if (is_object($items) || (is_array($items) && array_keys($items) !== range(0, count($items)-1))) {
    // associative map
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
    // assume proper array
}

try {
    $id_pedido = crearPedidoConDetalles($_SESSION['id_usuario'], $items);
    echo json_encode(['success' => true, 'id_pedido' => $id_pedido]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

?>
