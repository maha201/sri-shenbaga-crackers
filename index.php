<?php
$page_title = 'Sri Shenbaga Crackers - Buy Online at Affordable Prices';
require_once 'includes/header.php';
$db = getDB();
$B = BASE_URL;
$categories = $db->query("SELECT * FROM categories ORDER BY sort_order")->fetch_all(MYSQLI_ASSOC);
$cat_emojis = ['🔊','🌸','🚿','🌀','🌀','✏️','⚡','💣','🧒','🚀','🎆','🌠','🔥','✨','🎁'];
$cat_colors = ['#ff6b6b,#feca57','#48dbfb,#ff9ff3','#ff9f43,#ee5a24','#5f27cd,#00d2d3','#1dd1a1,#10ac84','#f368e0,#ff9ff3','#0652DD,#1289A7','#C4E538,#A3CB38','#FDA7DF,#D980FA','#58B19F,#2C3335','#ff6b6b,#feca57','#48dbfb,#ff9ff3','#ff9f43,#ee5a24','#5f27cd,#00d2d3','#1dd1a1,#10ac84'];
?>

<section class="hero">
    <div class="container">
        <div class="hero-content">
            <div class="hero-text">
                <h1>🎆 WELCOME TO<br><span>SIVAKASI CRACKERS</span></h1>
                <p>Premium quality fireworks from Sri Shenbaga Crackers, Sattur. Genuine products at affordable prices, safely delivered across India.</p>
                <div class="hero-buttons">
                    <a href="<?php echo $B; ?>/products.php" class="btn btn-primary"><i class="fas fa-store"></i> Shop Now</a>
                    <a href="<?php echo $B; ?>/about.php" class="btn btn-outline"><i class="fas fa-info-circle"></i> About Us</a>
                </div>
                <div class="hero-stats">
                    <div class="stat"><div class="stat-num">80%</div><div class="stat-label">Discount</div></div>
                    <div class="stat"><div class="stat-num">500+</div><div class="stat-label">Products</div></div>
                    <div class="stat"><div class="stat-num">100%</div><div class="stat-label">Quality</div></div>
                    <div class="stat"><div class="stat-num">Pan India</div><div class="stat-label">Delivery</div></div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="discount-banner">🎉 &nbsp; FLAT 80% OFF ON ALL PRODUCTS — FESTIVAL SPECIAL OFFER! &nbsp; 🎉</div>

<section class="section">
    <div class="container">
        <div class="section-title">
            <h2>Why Choose Us?</h2>
            <p>We offer the best quality crackers at unbeatable wholesale prices</p>
        </div>
        <div class="features-grid">
            <div class="feature-card"><div class="feature-icon">💰</div><h3>Genuine Price</h3><p>Quality products at economic wholesale prices. No hidden costs, no middlemen.</p></div>
            <div class="feature-card"><div class="feature-icon">🏆</div><h3>Best Quality</h3><p>100% genuine crackers from Sri Shenbaga Crackers. Quality &amp; trust are our promise.</p></div>
            <div class="feature-card"><div class="feature-icon">🛡️</div><h3>Safe To Use</h3><p>Crackers made from fine quality raw materials, meeting all safety standards.</p></div>
            <div class="feature-card"><div class="feature-icon">🚚</div><h3>Pan India Delivery</h3><p>Safe and secure delivery across India for all your orders, big or small.</p></div>
        </div>
    </div>
</section>

<section class="section section-alt">
    <div class="container">
        <div class="section-title">
            <h2>Our Categories</h2>
            <p>Explore our wide range of crackers for every occasion</p>
        </div>
        <div class="categories-grid">
            <?php foreach($categories as $idx => $cat): ?>
            <div class="category-card">
                <a href="<?php echo $B; ?>/products.php?cat=<?php echo $cat['id']; ?>">
                    <div class="cat-img" style="background:linear-gradient(135deg,<?php echo $cat_colors[$idx % count($cat_colors)]; ?>);">
                        <?php echo $cat_emojis[$idx % count($cat_emojis)]; ?>
                    </div>
                    <div class="cat-info"><h3><?php echo htmlspecialchars($cat['name']); ?></h3></div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-20">
            <a href="<?php echo $B; ?>/products.php" class="btn btn-red"><i class="fas fa-store"></i> View All Products</a>
        </div>
    </div>
</section>

<section class="section" style="background:linear-gradient(135deg,var(--primary),var(--primary-dark));color:#fff;text-align:center;">
    <div class="container">
        <h2 style="font-size:2rem;margin-bottom:15px;">🎆 Ready to Order?</h2>
        <p style="font-size:1.1rem;opacity:.9;margin-bottom:30px;">Shop now and get 80% discount on all products. Safe delivery across India!</p>
        <a href="<?php echo $B; ?>/products.php" class="btn btn-primary" style="font-size:1.1rem;"><i class="fas fa-shopping-cart"></i> Start Shopping</a>
        &nbsp;
        <a href="<?php echo $B; ?>/contact.php" class="btn btn-outline" style="font-size:1.1rem;"><i class="fas fa-phone"></i> Contact Us</a>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
