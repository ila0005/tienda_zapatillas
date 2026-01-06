document.addEventListener('DOMContentLoaded', function() {
    const enlacesEliminar = document.querySelectorAll('a[href*="eliminar"]');
    enlacesEliminar.forEach(link => {
        link.addEventListener('click', function(e) {
            if(!confirm('¿Estás seguro de eliminar este registro?')) e.preventDefault();
        });
    });
    
    // Producto: click to expand and show full description
    const productos = document.querySelectorAll('.producto');
    function closeAllProductos() {
        productos.forEach(p => p.classList.remove('expanded'));
    }
    // Auto-expand if URL contains ?id=123
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('id')) {
        const idToOpen = urlParams.get('id');
        const el = document.getElementById('producto-' + idToOpen);
        if (el) {
            closeAllProductos();
            el.classList.add('expanded');
            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            const q = el.querySelector('.qty-input'); if (q) q.focus();
        }
    }
    productos.forEach(p => {
        p.addEventListener('click', function(e) {
            // Don't toggle when clicking a form control (buttons/inputs/links)
            if (e.target.closest('form') || e.target.tagName === 'A' || e.target.tagName === 'BUTTON' || e.target.tagName === 'INPUT') return;
            const isExpanded = p.classList.contains('expanded');
            closeAllProductos();
            if (!isExpanded) {
                p.classList.add('expanded');
                p.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    });

    // Close expanded card when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.producto')) {
            closeAllProductos();
        }
    });

    // Añadir al carrito en localStorage (usa la cantidad indicada si existe)
    const addButtons = document.querySelectorAll('.add-to-cart');
    addButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            // permisos: si no hay usuario o no es cliente, redirigir (misma lógica que antes)
            if (typeof USER === 'undefined' || !USER.logged || USER.tipo !== 'cliente') {
                window.location.href = 'index.php';
                return;
            }

            const id = btn.getAttribute('data-id');
            const nombre = btn.getAttribute('data-nombre');
            const precio = parseFloat(btn.getAttribute('data-precio')) || 0;
            const imagen = btn.getAttribute('data-imagen') || '';
            const stock = parseInt(btn.getAttribute('data-stock')) || 0;

            // try to read qty input inside same .producto
            let qty = 1;
            const prodEl = btn.closest('.producto');
            if (prodEl) {
                const qInput = prodEl.querySelector('.qty-input');
                if (qInput) {
                    const v = parseInt(qInput.value);
                    if (!isNaN(v) && v > 0) qty = v;
                }
            }
            if (stock && qty > stock) qty = stock;

            const raw = localStorage.getItem('carrito');
            let carrito = raw ? JSON.parse(raw) : {};

            if (carrito[id]) {
                carrito[id].cantidad = (carrito[id].cantidad || 0) + qty;
            } else {
                carrito[id] = { id_producto: id, nombre: nombre, precio: precio, cantidad: qty, imagen: imagen };
            }

            localStorage.setItem('carrito', JSON.stringify(carrito));
            // Open side drawer instead of redirecting
            renderCartDrawer();
            openCartDrawer();
        });
    });

    // CART DRAWER FUNCTIONS
    const cartDrawer = document.getElementById('cart-drawer');
    const cartItemsEl = document.getElementById('cart-items');
    const cartTotalEl = document.getElementById('cart-drawer-total');

    function renderCartDrawer() {
        const raw = localStorage.getItem('carrito');
        const cart = raw ? JSON.parse(raw) : {};
        cartItemsEl.innerHTML = '';
        let total = 0;
        const keys = Object.keys(cart);
        if (keys.length === 0) {
            cartItemsEl.innerHTML = '<div class="muted center">El carrito está vacío.</div>';
            cartTotalEl.textContent = '0 €';
            return;
        }
        keys.forEach(k => {
            const it = cart[k];
            const subtotal = (it.precio * it.cantidad) || 0;
            total += subtotal;
            const div = document.createElement('div');
            div.className = 'cart-item';
            div.innerHTML = `
                <img src="img/${it.imagen || ''}" alt="${escapeHtml(it.nombre)}">
                <div class="meta">
                  <h4>${escapeHtml(it.nombre)}</h4>
                  <small>${it.precio} € — Subtotal: ${subtotal.toFixed(2)} €</small>
                </div>
                <div class="controls">
                  <button class="qty-decrease" data-id="${k}">-</button>
                  <div class="qty">${it.cantidad}</div>
                  <button class="qty-increase" data-id="${k}">+</button>
                  <button class="remove-item" data-id="${k}">Eliminar</button>
                </div>
            `;
            cartItemsEl.appendChild(div);
        });
        cartTotalEl.textContent = total.toFixed(2) + ' €';
        // Attach handlers
        cartItemsEl.querySelectorAll('.qty-increase').forEach(b => b.addEventListener('click', function(){ changeQty(this.dataset.id, 1); }));
        cartItemsEl.querySelectorAll('.qty-decrease').forEach(b => b.addEventListener('click', function(){ changeQty(this.dataset.id, -1); }));
        cartItemsEl.querySelectorAll('.remove-item').forEach(b => b.addEventListener('click', function(){ removeItem(this.dataset.id); }));
    }

    function changeQty(id, delta) {
        const raw = localStorage.getItem('carrito');
        if (!raw) return;
        const cart = JSON.parse(raw);
        if (!cart[id]) return;
        cart[id].cantidad = Math.max(1, (cart[id].cantidad || 0) + delta);
        localStorage.setItem('carrito', JSON.stringify(cart));
        renderCartDrawer();
    }

    function removeItem(id) {
        const raw = localStorage.getItem('carrito');
        if (!raw) return;
        const cart = JSON.parse(raw);
        delete cart[id];
        localStorage.setItem('carrito', JSON.stringify(cart));
        renderCartDrawer();
    }

    function openCartDrawer() {
        if (!cartDrawer) return;
        cartDrawer.setAttribute('aria-hidden','false');
    }
    function closeCartDrawer() {
        if (!cartDrawer) return;
        cartDrawer.setAttribute('aria-hidden','true');
    }

    // Escape helper
    function escapeHtml(str){ return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

    // Open cart from header link
    const openCartLink = document.getElementById('open-cart');
    if (openCartLink) {
        openCartLink.addEventListener('click', function(e){ e.preventDefault(); renderCartDrawer(); openCartDrawer(); });
    }

    // Close handlers
    document.querySelectorAll('[data-action="close"]').forEach(el => el.addEventListener('click', closeCartDrawer));

    // Checkout button inside drawer
    const checkoutBtn = document.getElementById('checkout-btn');
    if (checkoutBtn) checkoutBtn.addEventListener('click', function(){
        if (!confirm('Confirmar compra?')) return;
        const raw = localStorage.getItem('carrito');
        if (!raw) { alert('Carrito vacío'); return; }
        fetch('confirmar_compra.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: raw ? JSON.stringify({carrito: JSON.parse(raw)}) : '{}' })
        .then(r=>r.json()).then(data=>{
            if (data.success) {
                localStorage.removeItem('carrito');
                closeCartDrawer();
                window.location.href = 'historial.php';
            } else {
                alert('Error: '+(data.message||''));
            }
        }).catch(err=>alert('Error de red: '+err));
    });

    // Initial render if cart drawer exists
    if (cartItemsEl) renderCartDrawer();
});
