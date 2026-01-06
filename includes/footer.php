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
        <?php
        // Si el usuario es cliente, cargar sus direcciones y exponerlas a JS
        $userAddresses = [];
        if (isset($_SESSION['id_usuario']) && isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'cliente') {
            require_once __DIR__ . '/../app/config/db.php';
            $stmt = $conn->prepare("SELECT * FROM direcciones WHERE id_usuario = ?");
            $stmt->execute([$_SESSION['id_usuario']]);
            $userAddresses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        ?>
        <script>const ADDRESSES = <?php echo json_encode($userAddresses ?: []); ?>;</script>

        <div class="cart-address">
            <label for="address-select">Dirección:</label>
            <select id="address-select" style="width:100%; margin:8px 0;"></select>
            <div style="display:flex; gap:8px; align-items:center; margin-bottom:8px;">
                <button id="add-address-toggle" class="btn ghost">Agregar dirección</button>
            </div>
            <form id="new-address-form" style="display:none; gap:8px;">
                <input id="addr-calle" placeholder="Calle" />
                <input id="addr-ciudad" placeholder="Ciudad" />
                <input id="addr-provincia" placeholder="Provincia" />
                <input id="addr-cp" placeholder="Código postal" />
                <input id="addr-pais" placeholder="País" />
                <div style="display:flex; gap:8px;">
                    <button type="button" id="save-address" class="btn primary">Guardar</button>
                    <button type="button" id="cancel-address" class="btn ghost">Cancelar</button>
                </div>
            </form>
        </div>

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
