<?php
require_once __DIR__ . '/auth.php';
requireAdmin();
$B = BASE_URL;
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars($admin_title ?? 'Admin'); ?> — Admin Panel</title>
<link rel="stylesheet" href="<?php echo $B; ?>/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="admin-wrap">
<aside class="admin-sidebar" id="adminSidebar">
    <div class="admin-logo">
        <h2>🎆 Sri Shenbaga Admin</h2>
        <p>Welcome, <?php echo htmlspecialchars($_SESSION['admin_user'] ?? 'Admin'); ?></p>
    </div>
    <nav class="admin-nav">
        <div class="nav-divider">Main</div>
        <a href="<?php echo $B; ?>/admin/dashboard.php" class="<?php echo $current_page==='dashboard'?'active':''; ?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <div class="nav-divider">Catalog</div>
        <a href="<?php echo $B; ?>/admin/products.php" class="<?php echo $current_page==='products'?'active':''; ?>"><i class="fas fa-box"></i> Products</a>
        <a href="<?php echo $B; ?>/admin/categories.php" class="<?php echo $current_page==='categories'?'active':''; ?>"><i class="fas fa-tags"></i> Categories</a>
        <div class="nav-divider">Orders</div>
        <a href="<?php echo $B; ?>/admin/orders.php" class="<?php echo $current_page==='orders'?'active':''; ?>"><i class="fas fa-shopping-bag"></i> Orders</a>
        <a href="<?php echo $B; ?>/admin/billing.php" class="<?php echo $current_page==='billing'?'active':''; ?>"><i class="fas fa-file-invoice-dollar"></i> Billing</a>
        <div class="nav-divider">Settings</div>
        <a href="<?php echo $B; ?>/admin/discounts.php" class="<?php echo $current_page==='discounts'?'active':''; ?>"><i class="fas fa-percentage"></i> Bulk Discounts</a>
        <a href="<?php echo $B; ?>/admin/change_password.php" class="<?php echo $current_page==='change_password'?'active':''; ?>"><i class="fas fa-key"></i> Change Password</a>
        <div class="nav-divider">Site</div>
        <a href="<?php echo $B; ?>/" target="_blank"><i class="fas fa-globe"></i> View Website</a>
        <a href="<?php echo $B; ?>/admin/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </nav>
</aside>
<div class="admin-content">
<div class="admin-topbar">
    <div style="display:flex;align-items:center;gap:15px;">
        <button id="sidebarToggle" onclick="document.getElementById('adminSidebar').classList.toggle('open')" style="background:var(--primary);color:#fff;border:none;padding:8px 12px;border-radius:6px;cursor:pointer;font-size:16px;">&#9776;</button>
        <h1><?php echo htmlspecialchars($admin_title ?? 'Admin'); ?></h1>
    </div>
    <div style="display:flex;gap:15px;align-items:center;">
        <a href="<?php echo $B; ?>/" target="_blank" style="color:var(--gray);font-size:14px;"><i class="fas fa-globe"></i> View Site</a>
        <a href="<?php echo $B; ?>/admin/logout.php" style="color:var(--primary);font-size:14px;"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</div>
<div class="admin-main">
