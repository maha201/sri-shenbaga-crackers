<?php $B = BASE_URL; ?>
<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-col">
                <h3>🎆 <?php echo SITE_NAME; ?></h3>
                <p>Premium quality fireworks from Sri Shenbaga Crackers. Genuine products at wholesale prices, safely delivered across India.</p>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-whatsapp"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
            <div class="footer-col">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="<?php echo $B; ?>/index.php"><i class="fas fa-chevron-right"></i> Home</a></li>
                    <li><a href="<?php echo $B; ?>/about.php"><i class="fas fa-chevron-right"></i> About Us</a></li>
                    <li><a href="<?php echo $B; ?>/products.php"><i class="fas fa-chevron-right"></i> Products</a></li>
                    <li><a href="<?php echo $B; ?>/cart.php"><i class="fas fa-chevron-right"></i> Cart</a></li>
                    <li><a href="<?php echo $B; ?>/contact.php"><i class="fas fa-chevron-right"></i> Contact Us</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h3>Categories</h3>
                <ul>
                    <?php
                    $cats = getDB()->query("SELECT name FROM categories ORDER BY sort_order LIMIT 8");
                    while ($cat = $cats->fetch_assoc()):
                    ?>
                    <li><a href="<?php echo $B; ?>/products.php"><i class="fas fa-chevron-right"></i> <?php echo htmlspecialchars($cat['name']); ?></a></li>
                    <?php endwhile; ?>
                </ul>
            </div>
            <div class="footer-col">
                <h3>Contact Info</h3>
                <ul class="contact-list">
                    <li><i class="fas fa-phone"></i> <?php echo SITE_PHONE1; ?></li>
                    <li><i class="fas fa-phone"></i> <?php echo SITE_PHONE2; ?></li>
                    <li><i class="fas fa-envelope"></i> <?php echo SITE_EMAIL; ?></li>
                    <li><i class="fas fa-map-marker-alt"></i> <?php echo defined('SITE_ADDRESS') ? SITE_ADDRESS : 'Near Sattur Tollgate, Sattur - 626203'; ?></li>
                </ul>
                <div class="footer-badge"><span>🎉 80% OFF on All Items!</span></div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All Rights Reserved. | Designed with ❤️ for Sri Shenbaga Crackers</p>
        </div>
    </div>
</footer>
<script>window.BASE_URL = '<?php echo addslashes($B); ?>';</script>
<script src="<?php echo $B; ?>/js/main.js"></script>
</body>
</html>
