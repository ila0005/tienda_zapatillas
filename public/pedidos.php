<?php
session_start();
header('Content-Type: application/json');
// Verificar que haya sesión y que el usuario sea de tipo 'cliente'
if(!isset($_SESSION['id_usuario']) || $_SESSION['tipo'] !== 'cliente'){
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

require_once __DIR__ . '/../app/logic/pedido.php';
// Obtener pedidos del usuario
$pedidos = getPedidosByUsuario($_SESSION['id_usuario']);
echo json_encode(['success'=>true,'pedidos'=>$pedidos]);

?>
