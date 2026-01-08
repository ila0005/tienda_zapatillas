<?php
session_start();
header('Content-Type: application/json');

// Verificar que haya sesión y que el usuario sea de tipo 'admin'
if (!isset($_SESSION['id_usuario']) || $_SESSION['tipo'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

require_once __DIR__ . '/../app/logic/pedido.php';

// Leer cuerpo de la petición
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!$data || !isset($data['id_pedido'])) {
    echo json_encode(['success' => false, 'message' => 'Falta id_pedido']);
    exit;
}

// Eliminar pedido
$id = intval($data['id_pedido']);
try {
    $ok = deletePedidoById($id);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

?>
