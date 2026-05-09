<?php
$admin_title = 'Manage Orders';
require_once __DIR__ . '/admin_header.php';
$db = getDB();

$msg = '';

// Update status
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['update_status'])) {
    $oid    = (int)$_POST['order_id'];
    $status = $db->real_escape_string($_POST['status']);
    $db->query("UPDATE orders SET status='$status' WHERE id=$oid");
    $msg = 'Order status updated!';
}

// Filters
$search     = trim($_GET['search']??'');
$filter_status = trim($_GET['status']??'');
$where = ['1=1'];
if ($search) $where[] = "(o.order_number LIKE '%".$db->real_escape_string($search)."%' OR o.customer_name LIKE '%".$db->real_escape_string($search)."%' OR o.customer_phone LIKE '%".$db->real_escape_string($search)."%')";
if ($filter_status) $where[] = "o.status='".$db->real_escape_string($filter_status)."'";
$where_sql = implode(' AND ', $where);

$orders = $db->query("SELECT o.*, COUNT(oi.id) as item_count FROM orders o LEFT JOIN order_items oi ON o.id=oi.order_id WHERE $where_sql GROUP BY o.id ORDER BY o.created_at DESC")->fetch_all(MYSQLI_ASSOC);

$statuses = ['pending','confirmed','processing','shipped','delivered','cancelled'];
?>

<?php if ($msg): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($msg); ?></div><?php endif; ?>

<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-shopping-bag"></i> Orders (<?php echo count($orders); ?>)</h2>
        <form method="GET" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
            <input type="text" name="search" placeholder="Search order/name/phone..." value="<?php echo htmlspecialchars($search); ?>" style="padding:7px 12px;border:1px solid #ddd;border-radius:6px;font-size:13px;width:200px;">
            <select name="status" style="padding:7px 12px;border:1px solid #ddd;border-radius:6px;font-size:13px;">
                <option value="">All Status</option>
                <?php foreach($statuses as $s): ?><option value="<?php echo $s; ?>" <?php echo $filter_status===$s?'selected':''; ?>><?php echo ucfirst($s); ?></option><?php endforeach; ?>
            </select>
            <button type="submit" class="btn-sm btn-view">Filter</button>
            <?php if ($search||$filter_status): ?><a href="<?php echo BASE_URL; ?>/admin/orders.php" class="btn-sm btn-edit">Clear</a><?php endif; ?>
        </form>
    </div>
    <div style="overflow-x:auto;">
    <table class="admin-table">
        <thead>
            <tr><th>Order #</th><th>Customer</th><th>Phone</th><th>City</th><th>Items</th><th>Actual MRP</th><th>Discount</th><th>Payable</th><th>Status</th><th>Date</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php if (empty($orders)): ?>
        <tr><td colspan="11" style="text-align:center;padding:40px;color:var(--gray);"><i class="fas fa-inbox" style="font-size:2rem;display:block;margin-bottom:10px;"></i> No orders found.</td></tr>
        <?php endif; ?>
        <?php foreach($orders as $o): ?>
        <tr>
            <td><strong><?php echo htmlspecialchars($o['order_number']); ?></strong></td>
            <td><?php echo htmlspecialchars($o['customer_name']); ?></td>
            <td><a href="tel:<?php echo $o['customer_phone']; ?>"><?php echo htmlspecialchars($o['customer_phone']); ?></a></td>
            <td><?php echo htmlspecialchars($o['customer_city']); ?></td>
            <td><?php echo $o['item_count']; ?> items</td>
            <td><span style="text-decoration:line-through;color:var(--gray);">₹<?php echo number_format($o['total_actual_price'],2); ?></span></td>
            <td style="color:var(--accent);font-weight:600;">−₹<?php echo number_format($o['total_discount_amount'],2); ?></td>
            <td><strong style="color:var(--primary);font-size:1rem;">₹<?php echo number_format($o['total_price'],2); ?></strong></td>
            <td>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="order_id" value="<?php echo $o['id']; ?>">
                    <select name="status" class="status-select" onchange="this.form.submit()">
                        <?php foreach($statuses as $s): ?>
                        <option value="<?php echo $s; ?>" <?php echo $o['status']===$s?'selected':''; ?>><?php echo ucfirst($s); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="hidden" name="update_status" value="1">
                </form>
            </td>
            <td style="font-size:12px;white-space:nowrap;"><?php echo date('d M Y H:i', strtotime($o['created_at'])); ?></td>
            <td>
                <a href="<?php echo BASE_URL; ?>/admin/order_view.php?id=<?php echo $o['id']; ?>" class="btn-sm btn-view"><i class="fas fa-eye"></i> View</a>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
