<?php
session_start();
if(!isset($_SESSION['id_usuario']) || $_SESSION['tipo']!=='cliente'){
    header("Location: index.php"); exit;
}
include '../includes/header.php';
require_once __DIR__.'/../app/logic/producto.php';

// TODO: Recuperar carrito de BD (tabla carritos y detalle_pedido)
?>

<div class="container">
<h1>Carrito de Compras</h1>
<p>Aquí aparecerán los productos agregados al carrito.</p>
<!-- Tabla ejemplo -->
</table>
<table>
<thead>
<tr><th>Producto</th><th>Cantidad</th><th>Precio</th><th>Subtotal</th></tr>
</thead>
<tbody id="carrito-body">
<!-- rows injected by JS -->
</tbody>
<tfoot>
<tr><td colspan="3" style="text-align:right">Total:</td><td id="carrito-total">0 €</td></tr>
</tfoot>
</table>
<button id="confirmar-compra">Confirmar compra</button>
</div>

<script>
// Rellenar la tabla desde localStorage
function renderCarrito() {
    const raw = localStorage.getItem('carrito');
    const tbody = document.getElementById('carrito-body');
    const totalEl = document.getElementById('carrito-total');
    tbody.innerHTML = '';
    let total = 0;
    if (!raw) {
        tbody.innerHTML = '<tr><td colspan="4">El carrito está vacío.</td></tr>';
        totalEl.textContent = '0 €';
        return;
    }
    const carrito = JSON.parse(raw);
    const keys = Object.keys(carrito);
    if (keys.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4">El carrito está vacío.</td></tr>';
        totalEl.textContent = '0 €';
        return;
    }
    keys.forEach(k => {
        const it = carrito[k];
        const subtotal = it.precio * it.cantidad;
        total += subtotal;
        const tr = document.createElement('tr');
        tr.innerHTML = `<td>${it.nombre}</td><td>${it.cantidad}</td><td>${it.precio} €</td><td>${subtotal.toFixed(2)} €</td>`;
        tbody.appendChild(tr);
    });
    totalEl.textContent = total.toFixed(2) + ' €';
}

document.addEventListener('DOMContentLoaded', function() {
    renderCarrito();
    document.getElementById('confirmar-compra').addEventListener('click', function() {
        if (!confirm('Confirmar compra?')) return;
        const raw = localStorage.getItem('carrito');
        if (!raw) { alert('Carrito vacío'); return; }
        const carrito = JSON.parse(raw);
        fetch('confirmar_compra.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ carrito: carrito })
        }).then(r => r.json()).then(data => {
            if (data.success) {
                localStorage.removeItem('carrito');
                // Redirigir a historial
                window.location.href = 'historial.php';
            } else {
                alert('Error al confirmar: ' + (data.message || '')); 
            }
        }).catch(err => {
            alert('Error de red: ' + err);
        });
    });
});
</script>
</div>
