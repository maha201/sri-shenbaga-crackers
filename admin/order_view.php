<?php
$admin_title = 'Order Details';
require_once __DIR__ . '/admin_header.php';
$db = getDB();

$id = (int)($_GET['id'] ?? 0);
$order = $db->query("SELECT * FROM orders WHERE id=$id")->fetch_assoc();
if (!$order) {
    echo '<div class="alert alert-error">Order not found.</div>';
    require_once __DIR__ . '/admin_footer.php';
    exit;
}

$items = $db->query("SELECT oi.*, p.image FROM order_items oi LEFT JOIN products p ON oi.product_id=p.id WHERE oi.order_id=$id")->fetch_all(MYSQLI_ASSOC);

$msg = '';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $status = $db->real_escape_string($_POST['status']);
    $db->query("UPDATE orders SET status='$status' WHERE id=$id");
    $order['status'] = $status;
    $msg = 'Status updated!';
}

$statuses = ['pending','confirmed','processing','shipped','delivered','cancelled'];
?>

<?php if ($msg): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $msg; ?></div><?php endif; ?>

<div style="display:flex;gap:15px;align-items:center;margin-bottom:20px;">
    <a href="<?php echo BASE_URL; ?>/admin/orders.php" class="btn-sm btn-edit"><i class="fas fa-arrow-left"></i> Back to Orders</a>
    <span style="font-size:1.3rem;font-weight:700;"><?php echo htmlspecialchars($order['order_number']); ?></span>
    <span class="badge badge-<?php echo $order['status']; ?>" style="font-size:14px;padding:6px 16px;"><?php echo ucfirst($order['status']); ?></span>
    <a href="<?php echo BASE_URL; ?>/admin/invoice.php?id=<?php echo $order['id']; ?>" target="_blank"
       style="background:#c0392b;color:#fff;padding:7px 16px;border-radius:8px;text-decoration:none;font-size:13px;font-weight:600;margin-left:auto;">
       <i class="fas fa-file-invoice"></i> View / Print Invoice
    </a>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
    <!-- Customer Info -->
    <div class="card">
        <div class="card-header"><h2><i class="fas fa-user"></i> Customer Details</h2></div>
        <div class="card-body">
            <table style="width:100%;font-size:14px;">
                <tr><td style="padding:6px 0;color:var(--gray);width:130px;">Name</td><td><strong><?php echo htmlspecialchars($order['customer_name']); ?></strong></td></tr>
                <tr><td style="padding:6px 0;color:var(--gray);">Phone</td><td><a href="tel:<?php echo $order['customer_phone']; ?>" style="color:var(--primary);font-weight:700;"><?php echo htmlspecialchars($order['customer_phone']); ?></a></td></tr>
                <?php if ($order['customer_email']): ?><tr><td style="padding:6px 0;color:var(--gray);">Email</td><td><?php echo htmlspecialchars($order['customer_email']); ?></td></tr><?php endif; ?>
                <tr><td style="padding:6px 0;color:var(--gray);">Address</td><td><?php echo nl2br(htmlspecialchars($order['customer_address'])); ?></td></tr>
                <tr><td style="padding:6px 0;color:var(--gray);">City</td><td><?php echo htmlspecialchars($order['customer_city']); ?>, <?php echo htmlspecialchars($order['customer_state']); ?></td></tr>
                <tr><td style="padding:6px 0;color:var(--gray);">Pincode</td><td><?php echo htmlspecialchars($order['customer_pincode']); ?></td></tr>
                <?php if ($order['notes']): ?><tr><td style="padding:6px 0;color:var(--gray);">Notes</td><td><?php echo htmlspecialchars($order['notes']); ?></td></tr><?php endif; ?>
                <tr><td style="padding:6px 0;color:var(--gray);">Order Date</td><td><?php echo date('d M Y, H:i', strtotime($order['created_at'])); ?></td></tr>
            </table>
        </div>
    </div>

    <!-- Order Summary & Status -->
    <div>
        <div class="card" style="margin-bottom:20px;">
            <div class="card-header"><h2><i class="fas fa-receipt"></i> Order Summary</h2></div>
            <div class="card-body">
                <div class="summary-row"><span>Total MRP</span><span style="text-decoration:line-through;color:var(--gray);">₹<?php echo number_format($order['total_actual_price'],2); ?></span></div>
                <div class="summary-row discount"><span>Discount Amount</span><span>−₹<?php echo number_format($order['total_discount_amount'],2); ?></span></div>
                <div class="summary-row total"><span>Customer Pays</span><span>₹<?php echo number_format($order['total_price'],2); ?></span></div>
                <div style="background:rgba(39,174,96,0.1);border-radius:8px;padding:12px;margin-top:12px;text-align:center;color:var(--accent);font-weight:600;">
                    Customer saves ₹<?php echo number_format($order['total_discount_amount'],2); ?>!
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><h2><i class="fas fa-cog"></i> Update Status</h2></div>
            <div class="card-body">
                <form method="POST">
                    <div class="form-group">
                        <label>Order Status</label>
                        <select name="status" class="form-control" style="padding:10px;border:2px solid #ddd;border-radius:8px;width:100%;font-size:14px;">
                            <?php foreach($statuses as $s): ?>
                            <option value="<?php echo $s; ?>" <?php echo $order['status']===$s?'selected':''; ?>><?php echo ucfirst($s); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-red w-100" style="justify-content:center;"><i class="fas fa-save"></i> Update Status</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Order Items -->
<div class="card" style="margin-top:20px;">
    <div class="card-header"><h2><i class="fas fa-box"></i> Ordered Items (<?php echo count($items); ?>)</h2></div>
    <div style="overflow-x:auto;">
    <table class="admin-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Product</th>
                <th>Sale Type</th>
                <th>Actual Price</th>
                <th>Discount</th>
                <th>Offer Price</th>
                <th>Quantity</th>
                <th>Line Total (Offer)</th>
                <th>You Saved</th>
            </tr>
        </thead>
        <tbody>
        <?php $row_num = 1; foreach($items as $item):
            $actual_line = $item['actual_price'] * $item['quantity'];
            $saved_line = $actual_line - $item['total_price'];
        ?>
        <tr>
            <td><?php echo $row_num++; ?></td>
            <td>
                <div style="display:flex;align-items:center;gap:10px;">
                    <?php if (!empty($item['image']) && file_exists(dirname(__DIR__).'/uploads/'.$item['image'])): ?>
                    <img src="<?php echo BASE_URL; ?>/uploads/<?php echo htmlspecialchars($item['image']); ?>" style="width:40px;height:40px;object-fit:cover;border-radius:5px;">
                    <?php else: ?>
                    <div style="width:40px;height:40px;background:var(--primary);border-radius:5px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:18px;">🎆</div>
                    <?php endif; ?>
                    <span><?php echo htmlspecialchars($item['product_name']); ?></span>
                </div>
            </td>
            <td><?php echo htmlspecialchars($item['sale_type']); ?></td>
            <td><span style="text-decoration:line-through;color:var(--gray);">₹<?php echo number_format($item['actual_price'],2); ?></span></td>
            <td><span class="discount-tag"><?php echo $item['discount_percent']; ?>% OFF</span></td>
            <td style="color:var(--primary);font-weight:700;">₹<?php echo number_format($item['discount_price'],2); ?></td>
            <td><strong><?php echo $item['quantity']; ?></strong></td>
            <td><strong style="color:var(--primary);font-size:1.05rem;">₹<?php echo number_format($item['total_price'],2); ?></strong>
                <br><small style="color:var(--gray);"><?php echo $item['quantity']; ?> × ₹<?php echo number_format($item['discount_price'],2); ?></small>
            </td>
            <td style="color:var(--accent);font-weight:600;">₹<?php echo number_format($saved_line,2); ?></td>
        </tr>
        <?php endforeach; ?>
        <tr style="background:#f8f9fa;font-weight:700;">
            <td colspan="7" style="text-align:right;padding:12px;">TOTAL</td>
            <td style="color:var(--primary);font-size:1.1rem;">₹<?php echo number_format($order['total_price'],2); ?></td>
            <td style="color:var(--accent);">₹<?php echo number_format($order['total_discount_amount'],2); ?></td>
        </tr>
        </tbody>
    </table>
    </div>
</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
