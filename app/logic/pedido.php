<?php
require_once __DIR__ . '/../config/db.php';

function crearPedidoConDetalles($id_usuario, $items, $id_direccion = null) {
    global $conn;
    try {
        $conn->beginTransaction();

        // Ensure there's a direccion: if none provided, try to get any existing, else create placeholder
        if (!$id_direccion) {
            $stmt = $conn->prepare("SELECT id_direccion FROM direcciones WHERE id_usuario = ? LIMIT 1");
            $stmt->execute([$id_usuario]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $id_direccion = $row['id_direccion'];
            } else {
                $stmt = $conn->prepare("INSERT INTO direcciones (id_usuario, calle, ciudad, provincia, codigo_postal, pais) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$id_usuario, 'Sin dirección', 'Sin ciudad', 'Sin provincia', 0, 'Sin pais']);
                $id_direccion = $conn->lastInsertId();
            }
        }

        // Calculate total and verify stock
        $total = 0;
        foreach ($items as $it) {
            $stmt = $conn->prepare("SELECT precio, stock FROM productos WHERE id_producto = ?");
            $stmt->execute([$it['id_producto']]);
            $prod = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$prod) throw new Exception('Producto no encontrado: ' . $it['id_producto']);
            if ($prod['stock'] < $it['cantidad']) throw new Exception('Stock insuficiente para producto: ' . $it['id_producto']);
            $total += $prod['precio'] * $it['cantidad'];
        }

        // Insert pedido
        $stmt = $conn->prepare("INSERT INTO pedidos (id_usuario, id_direccion, fecha_pedido, total, estado) VALUES (?, ?, ?, ?, ?)");
        $fecha = date('Y-m-d H:i:s');
        $estado = 'confirmado';
        $stmt->execute([$id_usuario, $id_direccion, $fecha, $total, $estado]);
        $id_pedido = $conn->lastInsertId();

        // Insert detalles and update stock
        $stmtDet = $conn->prepare("INSERT INTO detalle_pedido (id_pedido, id_producto, cantidad, precio) VALUES (?, ?, ?, ?)");
        $stmtUpd = $conn->prepare("UPDATE productos SET stock = stock - ? WHERE id_producto = ?");
        foreach ($items as $it) {
            // Use provided precio or DB precio
            $stmtP = $conn->prepare("SELECT precio FROM productos WHERE id_producto = ?");
            $stmtP->execute([$it['id_producto']]);
            $p = $stmtP->fetch(PDO::FETCH_ASSOC);
            $precio = $p ? $p['precio'] : ($it['precio'] ?? 0);
            $stmtDet->execute([$id_pedido, $it['id_producto'], $it['cantidad'], $precio]);
            $stmtUpd->execute([$it['cantidad'], $it['id_producto']]);
        }

        $conn->commit();
        return $id_pedido;
    } catch (Exception $e) {
        $conn->rollBack();
        throw $e;
    }
}

function getPedidosByUsuario($id_usuario) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM pedidos WHERE id_usuario = ? ORDER BY fecha_pedido DESC");
    $stmt->execute([$id_usuario]);
    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($pedidos as &$p) {
        $stmt2 = $conn->prepare("SELECT dp.*, pr.nombre FROM detalle_pedido dp JOIN productos pr ON dp.id_producto = pr.id_producto WHERE dp.id_pedido = ?");
        $stmt2->execute([$p['id_pedido']]);
        $p['detalles'] = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    }
    return $pedidos;
}

?>
