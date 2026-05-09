<?php
$admin_title = 'Dashboard';
require_once __DIR__ . '/admin_header.php';
$db = getDB();

$total_orders = $db->query("SELECT COUNT(*) FROM orders")->fetch_row()[0];
$pending_orders = $db->query("SELECT COUNT(*) FROM orders WHERE status='pending'")->fetch_row()[0];
$total_revenue = $db->query("SELECT SUM(total_price) FROM orders WHERE status != 'cancelled'")->fetch_row()[0] ?? 0;
$total_products = $db->query("SELECT COUNT(*) FROM products WHERE is_active=1")->fetch_row()[0];
$total_cats = $db->query("SELECT COUNT(*) FROM categories")->fetch_row()[0];

$recent_orders = $db->query("SELECT o.*, COUNT(oi.id) as item_count FROM orders o LEFT JOIN order_items oi ON o.id=oi.order_id GROUP BY o.id ORDER BY o.created_at DESC LIMIT 10")->fetch_all(MYSQLI_ASSOC);
?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-shopping-bag"></i></div>
        <div class="stat-info"><h3><?php echo $total_orders; ?></h3><p>Total Orders</p></div>
    </div>
    <div class="stat-card orange">
        <div class="stat-icon"><i class="fas fa-clock"></i></div>
        <div class="stat-info"><h3><?php echo $pending_orders; ?></h3><p>Pending Orders</p></div>
    </div>
    <div class="stat-card green">
        <div class="stat-icon"><i class="fas fa-rupee-sign"></i></div>
        <div class="stat-info"><h3>₹<?php echo number_format($total_revenue, 0); ?></h3><p>Total Revenue</p></div>
    </div>
    <div class="stat-card blue">
        <div class="stat-icon"><i class="fas fa-box"></i></div>
        <div class="stat-info"><h3><?php echo $total_products; ?></h3><p>Active Products</p></div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-shopping-bag"></i> Recent Orders</h2>
        <a href="<?php echo BASE_URL; ?>/admin/orders.php" class="btn-sm btn-view">View All</a>
    </div>
    <div style="overflow-x:auto;">
    <table class="admin-table">
        <thead>
            <tr><th>Order #</th><th>Customer</th><th>Phone</th><th>Items</th><th>Total</th><th>Status</th><th>Date</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php if (empty($recent_orders)): ?>
        <tr><td colspan="8" style="text-align:center;color:var(--gray);padding:30px;">No orders yet.</td></tr>
        <?php endif; ?>
        <?php foreach ($recent_orders as $o): ?>
        <tr>
            <td><strong><?php echo htmlspecialchars($o['order_number']); ?></strong></td>
            <td><?php echo htmlspecialchars($o['customer_name']); ?></td>
            <td><?php echo htmlspecialchars($o['customer_phone']); ?></td>
            <td><?php echo $o['item_count']; ?> items</td>
            <td><strong style="color:var(--primary);">₹<?php echo number_format($o['total_price'],2); ?></strong></td>
            <td><span class="badge badge-<?php echo $o['status']; ?>"><?php echo ucfirst($o['status']); ?></span></td>
            <td style="font-size:13px;"><?php echo date('d M Y', strtotime($o['created_at'])); ?></td>
            <td><a href="<?php echo BASE_URL; ?>/admin/order_view.php?id=<?php echo $o['id']; ?>" class="btn-sm btn-view"><i class="fas fa-eye"></i></a></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
