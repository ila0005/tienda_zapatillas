<footer style="text-align:center; padding:10px; background:#333; color:white; margin-top:20px;">
    &copy; 2026 SneakersILA
</footer>
<?php
// Cargar script principal con ruta correcta según contexto (admin vs public)
if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'admin') {
    $scriptPath = '../public/js/main.js';
} else {
    $scriptPath = 'js/main.js';
}
?>
<!-- Cart drawer (client-side) -->
<div id="cart-drawer" class="cart-drawer" aria-hidden="true">
    <div class="cart-drawer__backdrop" data-action="close"></div>
    <aside class="cart-drawer__panel" role="dialog" aria-label="Carrito">
        <button class="cart-drawer__close" aria-label="Cerrar" data-action="close">×</button>
        <h3>Tu carrito</h3>
        <div id="cart-items" class="cart-items"></div>
        <div class="cart-drawer__footer">
            <div class="cart-total">Total: <span id="cart-drawer-total">0 €</span></div>
            <div class="cart-actions"><button id="checkout-btn" class="btn primary">Confirmar compra</button></div>
        </div>
    </aside>
</div>

<script src="<?= $scriptPath ?>"></script>
</body>
</html>
