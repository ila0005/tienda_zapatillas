<?php
session_start();
if(!isset($_SESSION['id_usuario']) || $_SESSION['tipo']!=='cliente'){
    header("Location: index.php"); exit;
}
include '../includes/header.php';
?>

<div class="container">
<h1>Historial de Compras</h1>
<p>Aquí se mostrarán tus compras anteriores.</p>
<div id="historial-list"></div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
// Obtener y mostrar el historial de pedidos del usuario autenticado
async function fetchPedidos(){
    const res = await fetch('pedidos.php');
    const data = await res.json();
    const container = document.getElementById('historial-list');
    if(!data.success){ container.innerHTML = '<p>Error al cargar pedidos.</p>'; return; }
    const pedidos = data.pedidos || [];
    if(pedidos.length===0){ container.innerHTML = '<p>No tienes pedidos todavía.</p>'; return; }
    container.innerHTML = '';
    const grid = document.createElement('div');
    grid.className = 'historial-grid';
    pedidos.forEach(p=>{
      
        const div = document.createElement('article');
        div.className = 'pedido-card';

        const header = document.createElement('div');
        header.className = 'pedido-header';
        
        const h = document.createElement('h3');
        h.textContent = `Pedido #${p.id_pedido}`;
       
        const meta = document.createElement('div');
        meta.className = 'pedido-meta';
        meta.innerHTML = `<time>${escapeHtml(p.fecha_pedido)}</time>`;

        const status = document.createElement('span');
       
        const estadoText = String(p.estado || 'pendiente');
        status.className = 'status-badge status-' + estadoText.replace(/\s+/g,'-').toLowerCase();
        status.textContent = estadoText.charAt(0).toUpperCase() + estadoText.slice(1);

        const total = document.createElement('strong');
        total.textContent = (Number(p.total) || 0).toFixed(2) + ' €';

        header.appendChild(h);
        header.appendChild(meta);
        header.appendChild(status);
        header.appendChild(total);
        div.appendChild(header);

        const addr = document.createElement('div');
        addr.className = 'pedido-direccion muted';
       
        const addrText = [p.calle, p.ciudad, p.provincia, p.codigo_postal, p.pais].filter(Boolean).join(', ');
        addr.textContent = 'Dirección: ' + (addrText || 'Sin dirección registrada');
        div.appendChild(addr);

        const details = document.createElement('table');
        details.className = 'pedido-detalles';
        details.innerHTML = '<thead><tr><th>Producto</th><th>Cant.</th><th>Precio</th><th>Subtotal</th></tr></thead>';
        
        const tbody = document.createElement('tbody');
        (p.detalles||[]).forEach(d=>{
            const tr = document.createElement('tr');
            const subtotal = (Number(d.precio) * Number(d.cantidad)) || 0;
            tr.innerHTML = `<td class="prod-name">${escapeHtml(d.nombre)}</td><td>${d.cantidad}</td><td>${Number(d.precio).toFixed(2)} €</td><td>${subtotal.toFixed(2)} €</td>`;
            tbody.appendChild(tr);
        });
        details.appendChild(tbody);
        div.appendChild(details);
        grid.appendChild(div);
    });
    container.appendChild(grid);
}

function escapeHtml(str){ return String(str||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

document.addEventListener('DOMContentLoaded', fetchPedidos);
</script>
