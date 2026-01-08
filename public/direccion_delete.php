<?php
session_start();
header('Content-Type: application/json');
// Verificar que haya sesión y que el usuario sea de tipo 'cliente'
if(!isset($_SESSION['id_usuario']) || $_SESSION['tipo'] !== 'cliente'){
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

require_once __DIR__ . '/../app/config/db.php';

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: $_POST;
$id = intval($data['id_direccion'] ?? 0);
if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID de dirección inválido']); exit;
}

try {
    // Verificar pertenencia
    $stmt = $conn->prepare("SELECT id_direccion FROM direcciones WHERE id_direccion = ? AND id_usuario = ? LIMIT 1");
    $stmt->execute([$id, $_SESSION['id_usuario']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        echo json_encode(['success' => false, 'message' => 'Dirección no encontrada o no pertenece al usuario']); exit;
    }
    // Verificar si la dirección está usada en pedidos
    $stmtChk = $conn->prepare("SELECT COUNT(*) as cnt FROM pedidos WHERE id_direccion = ?");
    $stmtChk->execute([$id]);
    $cntRow = $stmtChk->fetch(PDO::FETCH_ASSOC);
    if ($cntRow && intval($cntRow['cnt']) > 0) {
        echo json_encode(['success' => false, 'message' => 'No se puede eliminar la dirección porque está asociada a uno o más pedidos.']); exit;
    }

    // Eliminar
    $stmt = $conn->prepare("DELETE FROM direcciones WHERE id_direccion = ?");
    $stmt->execute([$id]);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
