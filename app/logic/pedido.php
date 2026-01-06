<?php
require_once __DIR__ . '/../config/db.php';

function crearPedidoConDetalles($id_usuario, $items, $id_direccion = null) {
    global $conn;
    try {
        $conn->beginTransaction();
        // Si se proporcionó id_direccion, verificar que pertenezca al usuario
        if ($id_direccion) {
            $stmtCheck = $conn->prepare("SELECT id_direccion FROM direcciones WHERE id_direccion = ? AND id_usuario = ? LIMIT 1");
            $stmtCheck->execute([$id_direccion, $id_usuario]);
            $found = $stmtCheck->fetch(PDO::FETCH_ASSOC);
            if (!$found) {
                throw new Exception('Dirección inválida para este usuario.');
            }
        } else {
            // Si no se pasa, intentar obtener una existente
            $stmt = $conn->prepare("SELECT id_direccion FROM direcciones WHERE id_usuario = ? LIMIT 1");
            $stmt->execute([$id_usuario]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $id_direccion = $row['id_direccion'];
            } else {
                // No crear dirección genérica automáticamente: requerir explicitamente una dirección
                throw new Exception('Se requiere una dirección para crear el pedido.');
            }
        }

        // Calcular total y verificar stock
        $total = 0;
        foreach ($items as $it) {
            $stmt = $conn->prepare("SELECT precio, stock FROM productos WHERE id_producto = ?");
            $stmt->execute([$it['id_producto']]);
            $prod = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$prod) throw new Exception('Producto no encontrado: ' . $it['id_producto']);
            if ($prod['stock'] < $it['cantidad']) throw new Exception('Stock insuficiente para producto: ' . $it['id_producto']);
            $total += $prod['precio'] * $it['cantidad'];
        }

        // Insertar pedido
        $stmt = $conn->prepare("INSERT INTO pedidos (id_usuario, id_direccion, fecha_pedido, total, estado) VALUES (?, ?, ?, ?, ?)");
        $fecha = date('Y-m-d H:i:s');
        $estado = 'confirmado';
        $stmt->execute([$id_usuario, $id_direccion, $fecha, $total, $estado]);
        $id_pedido = $conn->lastInsertId();

        // Insertar detalles y actualizar stock
        $stmtDet = $conn->prepare("INSERT INTO detalle_pedido (id_pedido, id_producto, cantidad, precio) VALUES (?, ?, ?, ?)");
        $stmtUpd = $conn->prepare("UPDATE productos SET stock = stock - ? WHERE id_producto = ?");
        foreach ($items as $it) {
            // Usar el precio de la BD o el precio enviado
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
    $stmt = $conn->prepare("SELECT p.*, d.calle, d.ciudad, d.provincia, d.codigo_postal, d.pais
        FROM pedidos p
        LEFT JOIN direcciones d ON p.id_direccion = d.id_direccion
        WHERE p.id_usuario = ?
        ORDER BY p.fecha_pedido DESC");
    $stmt->execute([$id_usuario]);
    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($pedidos as &$p) {
        $stmt2 = $conn->prepare("SELECT dp.*, pr.nombre FROM detalle_pedido dp JOIN productos pr ON dp.id_producto = pr.id_producto WHERE dp.id_pedido = ?");
        $stmt2->execute([$p['id_pedido']]);
        $p['detalles'] = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    }
    return $pedidos;
}

function getAllPedidos() {
    global $conn;
    $stmt = $conn->query("SELECT p.*, u.nombre AS cliente, d.calle, d.ciudad, d.provincia, d.codigo_postal, d.pais
        FROM pedidos p
        JOIN usuarios u ON p.id_usuario = u.id_usuario
        LEFT JOIN direcciones d ON p.id_direccion = d.id_direccion
        ORDER BY p.fecha_pedido DESC");
    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($pedidos as &$p) {
        $stmt2 = $conn->prepare("SELECT dp.*, pr.nombre FROM detalle_pedido dp JOIN productos pr ON dp.id_producto = pr.id_producto WHERE dp.id_pedido = ?");
        $stmt2->execute([$p['id_pedido']]);
        $p['detalles'] = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    }
    return $pedidos;
}

function getPedidoById($id_pedido) {
    global $conn;
    $stmt = $conn->prepare("SELECT p.*, u.nombre AS cliente, u.id_usuario, d.calle, d.ciudad, d.provincia, d.codigo_postal, d.pais
        FROM pedidos p
        JOIN usuarios u ON p.id_usuario = u.id_usuario
        LEFT JOIN direcciones d ON p.id_direccion = d.id_direccion
        WHERE p.id_pedido = ?");
    $stmt->execute([$id_pedido]);
    $p = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$p) return null;
    $stmt2 = $conn->prepare("SELECT dp.*, pr.nombre FROM detalle_pedido dp JOIN productos pr ON dp.id_producto = pr.id_producto WHERE dp.id_pedido = ?");
    $stmt2->execute([$id_pedido]);
    $p['detalles'] = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    return $p;
}

function updatePedido($id_pedido, $items, $id_direccion = null, $estado = null) {
    global $conn;
    try {
        $conn->beginTransaction();

        // Obtener detalles actuales
        $stmt = $conn->prepare("SELECT id_producto, cantidad, precio FROM detalle_pedido WHERE id_pedido = ?");
        $stmt->execute([$id_pedido]);
        $actual = [];
        while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $actual[$r['id_producto']] = $r;
        }

        // Normalizar items entrantes por id_producto
        $nuevos = [];
        foreach ($items as $it) {
            $pid = intval($it['id_producto']);
            $cant = intval($it['cantidad']);
            $precio = floatval($it['precio'] ?? 0);
            $nuevos[$pid] = ['cantidad' => $cant, 'precio' => $precio];
        }

        // Procesar actualizaciones y ajustar stock
        $stmtIns = $conn->prepare("INSERT INTO detalle_pedido (id_pedido, id_producto, cantidad, precio) VALUES (?, ?, ?, ?)");
        $stmtUpd = $conn->prepare("UPDATE detalle_pedido SET cantidad = ?, precio = ? WHERE id_pedido = ? AND id_producto = ?");
        $stmtDel = $conn->prepare("DELETE FROM detalle_pedido WHERE id_pedido = ? AND id_producto = ?");
        $stmtProd = $conn->prepare("SELECT stock, precio FROM productos WHERE id_producto = ?");
        $stmtStockUp = $conn->prepare("UPDATE productos SET stock = stock + ? WHERE id_producto = ?");
        $stmtStockDown = $conn->prepare("UPDATE productos SET stock = stock - ? WHERE id_producto = ?");

        // Handle products present in new set
        foreach ($nuevos as $pid => $info) {
            $newQty = $info['cantidad'];
            $newPrice = $info['precio'];
            $oldQty = isset($actual[$pid]) ? intval($actual[$pid]['cantidad']) : 0;

            $delta = $newQty - $oldQty;
            if ($delta > 0) {
                // reduce stock by delta
                // check stock
                $stmtProd->execute([$pid]);
                $p = $stmtProd->fetch(PDO::FETCH_ASSOC);
                if (!$p) throw new Exception('Producto no encontrado: '.$pid);
                if ($p['stock'] < $delta) throw new Exception('Stock insuficiente para producto: '.$pid);
                $stmtStockDown->execute([$delta, $pid]);
            } elseif ($delta < 0) {
                // increase stock (restaura)
                $stmtStockUp->execute([- $delta, $pid]);
            }

            if ($oldQty > 0) {
                $stmtUpd->execute([$newQty, $newPrice, $id_pedido, $pid]);
            } else {
                $stmtIns->execute([$id_pedido, $pid, $newQty, $newPrice]);
            }
        }

        // Handle products removed in new set
        foreach ($actual as $pid => $info) {
            if (!isset($nuevos[$pid])) {
                // delete detalle and restore stock
                $stmtDel->execute([$id_pedido, $pid]);
                $stmtStockUp->execute([$info['cantidad'], $pid]);
            }
        }

        // Recalcular total usando precios guardados en detalle_pedido
        $stmtT = $conn->prepare("SELECT SUM(cantidad * precio) as total FROM detalle_pedido WHERE id_pedido = ?");
        $stmtT->execute([$id_pedido]);
        $row = $stmtT->fetch(PDO::FETCH_ASSOC);
        $total = $row['total'] ?? 0;

        // Actualizar pedido
        $stmtP = $conn->prepare("UPDATE pedidos SET total = ?, id_direccion = ?, estado = ? WHERE id_pedido = ?");
        $stmtP->execute([$total, $id_direccion, $estado, $id_pedido]);

        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollBack();
        throw $e;
    }
}

function deletePedidoById($id_pedido) {
    global $conn;
    try {
        $conn->beginTransaction();

        // Recuperar detalles para restaurar stock
        $stmt = $conn->prepare("SELECT id_producto, cantidad FROM detalle_pedido WHERE id_pedido = ?");
        $stmt->execute([$id_pedido]);
        $detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Eliminar detalles
        $stmt = $conn->prepare("DELETE FROM detalle_pedido WHERE id_pedido = ?");
        $stmt->execute([$id_pedido]);

        // Restaurar stock
        $stmtUpd = $conn->prepare("UPDATE productos SET stock = stock + ? WHERE id_producto = ?");
        foreach ($detalles as $d) {
            $stmtUpd->execute([$d['cantidad'], $d['id_producto']]);
        }

        // Eliminar pedido
        $stmt = $conn->prepare("DELETE FROM pedidos WHERE id_pedido = ?");
        $stmt->execute([$id_pedido]);

        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollBack();
        throw $e;
    }
}

?>
