<?php
require_once __DIR__ . '/config.php';
$db = getDB();
$cart = $_SESSION['cart'] ?? [];
$cart_count = array_sum(array_column($cart, 'qty'));
$B = BASE_URL;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title ?? SITE_NAME); ?></title>
    <meta name="description" content="Buy premium quality fireworks from Sri Shenbaga Crackers, Sattur. Fancy fireworks, safe crackers, Diwali gift boxes.">
    <link rel="stylesheet" href="<?php echo $B; ?>/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<!-- Top Bar -->
<div class="top-bar">
    <div class="container">
        <div class="top-bar-inner">
            <div class="contact-info">
                <span><i class="fas fa-phone"></i> <?php echo SITE_PHONE1; ?></span>
                <span><i class="fas fa-phone"></i> <?php echo SITE_PHONE2; ?></span>
                <span><i class="fas fa-envelope"></i> <?php echo SITE_EMAIL; ?></span>
            </div>
            <div class="top-right">
                <span class="discount-badge">🎉 80% OFF on All Products!</span>
            </div>
        </div>
    </div>
</div>

<!-- Navbar -->
<nav class="navbar">
    <div class="container nav-inner">
        <a href="<?php echo $B; ?>/index.php" class="brand">
            <div class="brand-logo">
                <span class="brand-icon">🎆</span>
                <div>
                    <div class="brand-name"><?php echo SITE_NAME; ?></div>
                    <div class="brand-sub"><?php echo SITE_TAGLINE; ?></div>
                </div>
            </div>
        </a>
        <button class="hamburger" id="hamburger"><i class="fas fa-bars"></i></button>
        <ul class="nav-links" id="navLinks">
            <?php
            $cur = basename($_SERVER['PHP_SELF']);
            $links = [
                'index.php'   => ['Home',     'fas fa-home'],
                'about.php'   => ['About Us', 'fas fa-info-circle'],
                'products.php'=> ['Products', 'fas fa-store'],
                'cart.php'    => ['Cart',     'fas fa-shopping-cart'],
                'contact.php' => ['Contact',  'fas fa-envelope'],
            ];
            foreach ($links as $file => [$label, $icon]):
                $active = $cur === $file ? 'active' : '';
            ?>
            <li>
                <a href="<?php echo $B; ?>/<?php echo $file; ?>" class="<?php echo $active; ?>">
                    <i class="<?php echo $icon; ?>"></i> <?php echo $label; ?>
                    <?php if ($file === 'cart.php' && $cart_count > 0): ?>
                    <span class="cart-badge"><?php echo $cart_count; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <?php endforeach; ?>
            <li><a href="<?php echo $B; ?>/admin/login.php"><i class="fas fa-lock"></i> Admin</a></li>
        </ul>
    </div>
</nav>
