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

// Campos esperados
$calle = $data['calle'] ?? trim($_POST['calle'] ?? '');
$ciudad = $data['ciudad'] ?? trim($_POST['ciudad'] ?? '');
$provincia = $data['provincia'] ?? trim($_POST['provincia'] ?? '');
$cp = $data['codigo_postal'] ?? $data['cp'] ?? trim($_POST['codigo_postal'] ?? $_POST['cp'] ?? '0');
$pais = $data['pais'] ?? trim($_POST['pais'] ?? '');

// Validación mínima
if (!$calle) {
    echo json_encode(['success'=>false,'message'=>'Calle requerida']); exit;
}

// Validar código postal
$cp = preg_replace('/\D/','', (string)$cp);
if ($cp === '') {
    echo json_encode(['success'=>false,'message'=>'Código postal inválido (solo números).']); exit;
}

// Insertar la nueva dirección en la tabla 'direcciones'
try {
    $stmt = $conn->prepare("INSERT INTO direcciones (id_usuario, calle, ciudad, provincia, codigo_postal, pais) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$_SESSION['id_usuario'], $calle, $ciudad, $provincia, $cp, $pais]);
    // Obtener id generado y devolver la dirección creada
    $id = $conn->lastInsertId();
    echo json_encode([
        'success' => true,
        'id_direccion' => $id,
        'direccion' => [
            'id_direccion' => $id,
            'calle' => $calle,
            'ciudad' => $ciudad,
            'provincia' => $provincia,
            'codigo_postal' => $cp,
            'pais' => $pais
        ]
    ]);
} catch (Exception $e) {
    // En caso de error devolver mensaje
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}

?>
