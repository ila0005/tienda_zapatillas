document.addEventListener('DOMContentLoaded', function() {
    const enlacesEliminar = document.querySelectorAll('a[href*="eliminar"]');
    enlacesEliminar.forEach(link => {
        link.addEventListener('click', function(e) {
            if(!confirm('¿Estás seguro de eliminar este registro?')) e.preventDefault();
        });
    });
    
    // Expandir/contraer tarjetas de producto
    const productos = document.querySelectorAll('.producto');
    function closeAllProductos() {
        productos.forEach(p => p.classList.remove('expanded'));
    }
    
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
            if (e.target.closest('form') || e.target.tagName === 'A' || e.target.tagName === 'BUTTON' || e.target.tagName === 'INPUT') return;
            const isExpanded = p.classList.contains('expanded');
            closeAllProductos();
            if (!isExpanded) {
                p.classList.add('expanded');
                p.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    });

    // Cerrar tarjeta al clicar fuera
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.producto')) {
            closeAllProductos();
        }
    });

    // Añadir al carrito
    const addButtons = document.querySelectorAll('.add-to-cart');
    addButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            if (typeof USER === 'undefined' || !USER.logged || USER.tipo !== 'cliente') {
                window.location.href = 'index.php';
                return;
            }

            const id = btn.getAttribute('data-id');
            const nombre = btn.getAttribute('data-nombre');
            const precio = parseFloat(btn.getAttribute('data-precio')) || 0;
            const imagen = btn.getAttribute('data-imagen') || '';
            const stock = parseInt(btn.getAttribute('data-stock')) || 0;

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
            renderCartDrawer();
            openCartDrawer();
        });
    });

    // Funciones del cajón del carrito
    const cartDrawer = document.getElementById('cart-drawer');
    const cartItemsEl = document.getElementById('cart-items');
    const cartTotalEl = document.getElementById('cart-drawer-total');

    // Renderizar contenido del cajón
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
        // Añadir manejadores
        cartItemsEl.querySelectorAll('.qty-increase').forEach(b => b.addEventListener('click', function(){ changeQty(this.dataset.id, 1); }));
        cartItemsEl.querySelectorAll('.qty-decrease').forEach(b => b.addEventListener('click', function(){ changeQty(this.dataset.id, -1); }));
        cartItemsEl.querySelectorAll('.remove-item').forEach(b => b.addEventListener('click', function(){ removeItem(this.dataset.id); }));
    }
    // Cambiar cantidad de un ítem
    function changeQty(id, delta) {
        const raw = localStorage.getItem('carrito');
        if (!raw) return;
        const cart = JSON.parse(raw);
        if (!cart[id]) return;
        cart[id].cantidad = Math.max(1, (cart[id].cantidad || 0) + delta);
        localStorage.setItem('carrito', JSON.stringify(cart));
        renderCartDrawer();
    }
    // Eliminar ítem del carrito
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


    function escapeHtml(str){ return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

    // Abrir carrito desde enlace del header
    const openCartLink = document.getElementById('open-cart');
    if (openCartLink) {
        openCartLink.addEventListener('click', function(e){ e.preventDefault(); renderCartDrawer(); openCartDrawer(); });
    }

    // Cerrar cajón
    document.querySelectorAll('[data-action="close"]').forEach(el => el.addEventListener('click', closeCartDrawer));

    // Botón confirmar compra en el cajón
    const checkoutBtn = document.getElementById('checkout-btn');
    if (checkoutBtn) checkoutBtn.addEventListener('click', function(){
        if (!confirm('Confirmar compra?')) return;
        const raw = localStorage.getItem('carrito');
        if (!raw) { alert('Carrito vacío'); return; }
        const carrito = JSON.parse(raw);
        // obtener direccion seleccionada
        const selectedAddress = (typeof addressSelect !== 'undefined' && addressSelect) ? addressSelect.value : '';
        if (!selectedAddress) {
            // No permitir confirmar sin dirección
            alert('Debes seleccionar o crear una dirección antes de confirmar la compra.');
            if (newAddrForm) {
                newAddrForm.style.display = 'flex';
                const first = document.getElementById('addr-calle'); if (first) first.focus();
            }
            return;
        }

        fetch('confirmar_compra.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({carrito: carrito, id_direccion: selectedAddress}) })
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

    // Render inicial si existe el cajón
    if (cartItemsEl) renderCartDrawer();

        // Gestión de direcciones de usuario
        const addressSelect = document.getElementById('address-select');
        const addAddrBtn = document.getElementById('add-address-toggle');
        const newAddrForm = document.getElementById('new-address-form');
        const saveAddrBtn = document.getElementById('save-address');
        const cancelAddrBtn = document.getElementById('cancel-address');
        
        function populateAddresses() {
            if (!addressSelect) return;
            addressSelect.innerHTML = '';
            if (typeof ADDRESSES !== 'undefined' && Array.isArray(ADDRESSES) && ADDRESSES.length>0) {
                ADDRESSES.forEach(a=>{
                    const opt = document.createElement('option');
                    opt.value = a.id_direccion;
                    opt.textContent = a.calle + ' - ' + a.ciudad + ' (' + a.pais + ')';
                    addressSelect.appendChild(opt);
                });
            } else {
                const opt = document.createElement('option'); opt.value=''; opt.textContent='No hay direcciones guardadas'; addressSelect.appendChild(opt);
            }
        }
        populateAddresses();

        // Mostrar/ocultar formulario nueva dirección
        if (addAddrBtn && newAddrForm) {
            addAddrBtn.addEventListener('click', function(e){ e.preventDefault(); newAddrForm.style.display = newAddrForm.style.display === 'none' ? 'flex' : 'none'; });
        }
        if (cancelAddrBtn && newAddrForm) {
            cancelAddrBtn.addEventListener('click', function(e){ e.preventDefault(); newAddrForm.style.display = 'none'; });
        }
        if (saveAddrBtn) {
            saveAddrBtn.addEventListener('click', function(e){
                e.preventDefault();
                const payload = {
                    calle: document.getElementById('addr-calle').value || '',
                    ciudad: document.getElementById('addr-ciudad').value || '',
                    provincia: document.getElementById('addr-provincia').value || '',
                    codigo_postal: (document.getElementById('addr-cp').value || '').replace(/\D/g,''),
                    pais: document.getElementById('addr-pais').value || ''
                };
                fetch('direccion.php', {
                    method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload)
                }).then(r=>r.json()).then(data=>{
                    if (data.success) {
                        // añadir a ADDRESSES y seleccionar
                        ADDRESSES.push(data.direccion);
                        populateAddresses();
                        addressSelect.value = data.id_direccion;
                        newAddrForm.style.display = 'none';
                    } else alert('Error guardando dirección: '+(data.message||''));
                }).catch(err=>alert('Error de red: '+err));
            });
        
            // Eliminar dirección seleccionada
            const deleteAddrBtn = document.getElementById('delete-address-btn');
            if (deleteAddrBtn) {
                deleteAddrBtn.addEventListener('click', function(e){
                    e.preventDefault();
                    if (!addressSelect) return;
                    const id = addressSelect.value;
                    if (!id) { alert('No hay dirección seleccionada para eliminar.'); return; }
                    if (!confirm('Eliminar la dirección seleccionada?')) return;
                    fetch('direccion_delete.php', { method: 'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({id_direccion: id}) })
                    .then(r=>r.json()).then(data=>{
                        if (data.success) {
                            // quitar de ADDRESSES
                            if (Array.isArray(ADDRESSES)) {
                                const idx = ADDRESSES.findIndex(a=>String(a.id_direccion)===String(id));
                                if (idx !== -1) ADDRESSES.splice(idx,1);
                            }
                            populateAddresses();
                            alert('Dirección eliminada');
                        } else {
                            alert('Error eliminando dirección: '+(data.message||''));
                        }
                    }).catch(err=>alert('Error de red: '+err));
                });
            }
            // Forzar sólo dígitos en el campo código postal 
            const addrCp = document.getElementById('addr-cp');
            if (addrCp) {
                addrCp.addEventListener('input', function(){
                    const only = this.value.replace(/\D/g,'');
                    if (this.value !== only) this.value = only;
                });
            }
        }
});
