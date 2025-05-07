    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Cat Store</h3>
                    <p>Tu tienda de gatos de confianza. Encuentra tu compañero felino perfecto.</p>
                </div>

                <div class="footer-section">
                    <h3>Enlaces</h3>
                    <ul>
                        <li><a href="/views/store/index.php">Inicio</a></li>
                        <li><a href="/views/profile/user_info.php">Mi Perfil</a></li>
                        <li><a href="/views/cart/checkout.php">Carrito</a></li>
                        <?php if (isAuthenticated() && $_SESSION['user_id'] === 'javial'): ?>
                            <li><a href="/views/admin/products.php">Administración</a></li>
                        <?php endif; ?>
                    </ul>
                </div>

                <div class="footer-section">
                    <h3>Contacto</h3>
                    <p><i class="fas fa-envelope"></i> info@catstore.com</p>
                    <p><i class="fas fa-phone"></i> +34 123 456 789</p>
                    <p><i class="fas fa-map-marker-alt"></i> Calle Felina, 123, Valencia</p>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> Cat Store. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <?php if (isset($extraJs) && is_array($extraJs)): ?>
        <?php foreach ($extraJs as $js): ?>
            <script src="<?php echo $js; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    </body>

    </html>