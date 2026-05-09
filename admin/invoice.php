<?php
/**
 * Invoice Generator — Sri Shenbaga Crackers
 * Accessible: admin/invoice.php?id=ORDER_ID
 * Also works as customer-facing: invoice.php?token=ORDER_TOKEN (future-ready)
 */
require_once dirname(__DIR__) . '/includes/config.php';

// ── Auth: require admin login ─────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['admin_logged_in'])) {
    header('Location: ' . BASE_URL . '/admin/login.php');
    exit;
}

$db = getDB();
$id = (int)($_GET['id'] ?? 0);

$order = $db->prepare("SELECT * FROM orders WHERE id = ?");
$order->bind_param('i', $id);
$order->execute();
$order = $order->get_result()->fetch_assoc();

if (!$order) {
    die('<div style="font-family:sans-serif;text-align:center;padding:60px;color:#c0392b;"><h2>❌ Invoice Not Found</h2><a href="billing.php">← Back to Billing</a></div>');
}

$items_stmt = $db->prepare("
    SELECT oi.*, COALESCE(p.image,'') as image
    FROM order_items oi
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
    ORDER BY oi.id
");
$items_stmt->bind_param('i', $id);
$items_stmt->execute();
$items = $items_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// ── Recalculate totals accurately from line items ─────────────────────────────
$calc_mrp      = 0;
$calc_payable  = 0;
$calc_discount = 0;
foreach ($items as $item) {
    $line_mrp      = $item['actual_price']   * $item['quantity'];
    $line_payable  = $item['discount_price'] * $item['quantity'];
    $calc_mrp     += $line_mrp;
    $calc_payable += $line_payable;
    $calc_discount+= $line_mrp - $line_payable;
}
// Use calculated values (more accurate than stored totals if any rounding occurred)
$grand_mrp      = round($calc_mrp, 2);
$grand_discount = round($calc_discount, 2);
$grand_payable  = round($calc_payable, 2);
$savings_pct    = $grand_mrp > 0 ? round(($grand_discount / $grand_mrp) * 100, 1) : 0;

$status_colors = [
    'pending'    => ['bg'=>'#fff3cd','color'=>'#856404','border'=>'#ffc107'],
    'confirmed'  => ['bg'=>'#cfe2ff','color'=>'#084298','border'=>'#3498db'],
    'processing' => ['bg'=>'#e2d9f3','color'=>'#4a235a','border'=>'#9b59b6'],
    'shipped'    => ['bg'=>'#d1ecf1','color'=>'#0c5460','border'=>'#1abc9c'],
    'delivered'  => ['bg'=>'#d4edda','color'=>'#155724','border'=>'#27ae60'],
    'cancelled'  => ['bg'=>'#f8d7da','color'=>'#721c24','border'=>'#e74c3c'],
];
$sc = $status_colors[$order['status']] ?? ['bg'=>'#eee','color'=>'#555','border'=>'#aaa'];

$invoice_date = date('d F Y', strtotime($order['created_at']));
$invoice_time = date('h:i A', strtotime($order['created_at']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Invoice <?php echo htmlspecialchars($order['order_number']); ?> — Sri Shenbaga Crackers</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Segoe UI',Arial,sans-serif; background:#f0f2f5; color:#2c3e50; }

/* ── Action Bar (hidden on print) ── */
.action-bar {
    background:#2c3e50; color:#fff; padding:12px 30px;
    display:flex; justify-content:space-between; align-items:center;
}
.action-bar a, .action-bar button {
    color:#fff; text-decoration:none; padding:8px 18px; border-radius:8px;
    border:1px solid rgba(255,255,255,.3); background:transparent;
    cursor:pointer; font-size:13px; font-weight:600; margin-left:8px;
}
.action-bar .btn-print { background:#c0392b; border-color:#c0392b; }
.action-bar .btn-download { background:#27ae60; border-color:#27ae60; }

/* ── Invoice Wrapper ── */
.invoice-wrap { max-width:820px; margin:30px auto 50px; background:#fff; border-radius:16px; box-shadow:0 8px 40px rgba(0,0,0,.12); overflow:hidden; }

/* ── Header ── */
.inv-header { background:linear-gradient(135deg,#c0392b 0%,#922b21 100%); color:#fff; padding:35px 40px; }
.inv-header-inner { display:flex; justify-content:space-between; align-items:flex-start; }
.brand-name { font-size:1.8rem; font-weight:800; letter-spacing:-.5px; }
.brand-sub  { font-size:12px; opacity:.85; margin-top:3px; }
.brand-contact { font-size:12px; opacity:.8; margin-top:10px; line-height:1.8; }
.inv-title { text-align:right; }
.inv-title h1 { font-size:2rem; font-weight:300; letter-spacing:4px; text-transform:uppercase; opacity:.9; }
.inv-title .inv-num { font-size:1rem; font-weight:700; background:rgba(255,255,255,.2); padding:4px 14px; border-radius:20px; display:inline-block; margin-top:8px; }
.inv-title .inv-date { font-size:12px; opacity:.75; margin-top:6px; }

/* ── Status Badge ── */
.status-strip { padding:10px 40px; background:<?php echo $sc['bg']; ?>; border-bottom:2px solid <?php echo $sc['border']; ?>; display:flex; align-items:center; gap:10px; }
.status-badge { background:<?php echo $sc['border']; ?>; color:#fff; padding:4px 16px; border-radius:20px; font-size:12px; font-weight:700; text-transform:uppercase; }

/* ── Address Block ── */
.address-grid { display:grid; grid-template-columns:1fr 1fr; gap:20px; padding:30px 40px; border-bottom:2px solid #f0f0f0; }
.addr-box h3 { font-size:11px; color:#888; font-weight:700; text-transform:uppercase; letter-spacing:.8px; margin-bottom:8px; }
.addr-box p  { font-size:14px; line-height:1.7; color:#2c3e50; }
.addr-box strong { font-size:1rem; color:#c0392b; }

/* ── Items Table ── */
.items-section { padding:0 40px 30px; }
.items-section h3 { font-size:12px; color:#888; font-weight:700; text-transform:uppercase; letter-spacing:.8px; padding:20px 0 12px; border-top:2px solid #f0f0f0; }
table.inv-table { width:100%; border-collapse:collapse; font-size:13px; }
table.inv-table thead tr { background:#f8f9fa; }
table.inv-table th {
    padding:11px 12px; text-align:left; font-weight:700; color:#555;
    border-bottom:2px solid #eee; font-size:11px; text-transform:uppercase; letter-spacing:.5px;
}
table.inv-table td { padding:12px 12px; border-bottom:1px solid #f5f5f5; vertical-align:middle; }
table.inv-table tr:last-child td { border-bottom:none; }
table.inv-table tr:hover td { background:#fafafa; }
.text-right { text-align:right; }
.text-center { text-align:center; }
.disc-tag { background:#fff3e0; color:#e67e22; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; white-space:nowrap; }
.saved-amt { color:#27ae60; font-size:11px; }

/* ── Totals ── */
.totals-section { padding:0 40px 30px; display:flex; justify-content:flex-end; }
.totals-box { width:320px; }
.total-row { display:flex; justify-content:space-between; align-items:center; padding:9px 0; border-bottom:1px solid #f0f0f0; font-size:14px; }
.total-row:last-child { border-bottom:none; }
.total-row .lbl { color:#666; }
.total-row .val { font-weight:600; }
.total-row.disc .val { color:#e67e22; }
.total-row.grand { background:#f8f8f8; padding:14px 16px; border-radius:10px; margin-top:8px; border:2px solid #27ae60; }
.total-row.grand .lbl { font-weight:700; font-size:1rem; color:#2c3e50; }
.total-row.grand .val { font-weight:800; font-size:1.3rem; color:#27ae60; }
.savings-badge { background:rgba(39,174,96,.1); border:1px solid rgba(39,174,96,.3); border-radius:10px; padding:10px 16px; margin-top:12px; text-align:center; color:#27ae60; font-weight:700; font-size:14px; }

/* ── Notes / Footer ── */
.inv-footer { background:#f8f9fa; padding:25px 40px; border-top:2px solid #f0f0f0; display:grid; grid-template-columns:1fr 1fr; gap:20px; font-size:13px; }
.inv-footer h4 { font-size:11px; color:#888; font-weight:700; text-transform:uppercase; letter-spacing:.8px; margin-bottom:8px; }
.inv-footer p { color:#555; line-height:1.7; }
.inv-bottom { text-align:center; padding:15px; font-size:11px; color:#aaa; border-top:1px solid #eee; }

/* ── Watermark for cancelled ── */
<?php if ($order['status'] === 'cancelled'): ?>
.invoice-wrap { position:relative; }
.invoice-wrap::after {
    content:'CANCELLED'; position:absolute; top:50%; left:50%;
    transform:translate(-50%,-50%) rotate(-30deg);
    font-size:5rem; font-weight:900; color:rgba(231,76,60,.08);
    pointer-events:none; z-index:0; letter-spacing:10px; white-space:nowrap;
}
<?php endif; ?>

/* ── Print ── */
@media print {
    body { background:#fff; }
    .action-bar { display:none !important; }
    .invoice-wrap { box-shadow:none; border-radius:0; margin:0; max-width:100%; }
    @page { margin:15mm; size:A4; }
}
</style>
</head>
<body>

<!-- Action Bar -->
<div class="action-bar">
    <div>
        <a href="billing.php"><i class="fas fa-arrow-left"></i> Back to Billing</a>
        <a href="order_view.php?id=<?php echo $order['id']; ?>"><i class="fas fa-eye"></i> Order View</a>
    </div>
    <div>
        <button class="btn-download" onclick="downloadInvoice()"><i class="fas fa-download"></i> Download PDF</button>
        <button class="btn-print" onclick="window.print()"><i class="fas fa-print"></i> Print Invoice</button>
    </div>
</div>

<!-- Invoice -->
<div class="invoice-wrap" id="invoice-content">

    <!-- Header -->
    <div class="inv-header">
        <div class="inv-header-inner">
            <div>
                <div class="brand-name">🎆 <?php echo SITE_NAME; ?></div>
                <div class="brand-sub"><?php echo SITE_TAGLINE; ?></div>
                <div class="brand-contact">
                    <i class="fas fa-phone"></i> <?php echo SITE_PHONE1; ?> &nbsp;|&nbsp;
                    <i class="fas fa-phone"></i> <?php echo SITE_PHONE2; ?><br>
                    <i class="fas fa-envelope"></i> <?php echo SITE_EMAIL; ?><br>
                    <i class=\"fas fa-map-marker-alt\"></i> Near Sattur Tollgate, Sattur - 626203
                </div>
            </div>
            <div class="inv-title">
                <h1>Invoice</h1>
                <div class="inv-num"><?php echo htmlspecialchars($order['order_number']); ?></div>
                <div class="inv-date">
                    <i class="fas fa-calendar"></i> <?php echo $invoice_date; ?><br>
                    <i class="fas fa-clock"></i> <?php echo $invoice_time; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Strip -->
    <div class="status-strip">
        <i class="fas fa-info-circle" style="color:<?php echo $sc['color']; ?>;"></i>
        <span style="color:<?php echo $sc['color']; ?>;font-weight:600;font-size:13px;">Order Status:</span>
        <span class="status-badge"><?php echo ucfirst($order['status']); ?></span>
        <?php if ($order['status'] === 'delivered'): ?>
        <span style="color:#27ae60;font-size:12px;margin-left:8px;"><i class="fas fa-check-circle"></i> Payment Received</span>
        <?php elseif ($order['status'] === 'pending'): ?>
        <span style="color:#e67e22;font-size:12px;margin-left:8px;"><i class="fas fa-clock"></i> Payment Pending (COD / Bank Transfer)</span>
        <?php endif; ?>
    </div>

    <!-- Addresses -->
    <div class="address-grid">
        <div class="addr-box">
            <h3><i class="fas fa-store"></i> From (Seller)</h3>
            <p>
                <strong><?php echo SITE_NAME; ?></strong><br>
                Fireworks & Fancy Crackers<br>
                Near Sattur Tollgate, Sattur - 626203<br>
                Tamil Nadu, India
            </p>
        </div>
        <div class="addr-box">
            <h3><i class="fas fa-user"></i> Bill To (Customer)</h3>
            <p>
                <strong><?php echo htmlspecialchars($order['customer_name']); ?></strong><br>
                <?php echo nl2br(htmlspecialchars($order['customer_address'])); ?><br>
                <?php echo htmlspecialchars($order['customer_city']); ?>, <?php echo htmlspecialchars($order['customer_state']); ?> — <?php echo htmlspecialchars($order['customer_pincode']); ?><br>
                <i class="fas fa-phone" style="color:#c0392b;"></i> <?php echo htmlspecialchars($order['customer_phone']); ?>
                <?php if ($order['customer_email']): ?><br><i class="fas fa-envelope" style="color:#c0392b;"></i> <?php echo htmlspecialchars($order['customer_email']); ?><?php endif; ?>
            </p>
        </div>
    </div>

    <!-- Items -->
    <div class="items-section">
        <h3><i class="fas fa-box"></i> Ordered Items</h3>
        <table class="inv-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th class="text-center">Sale Type</th>
                    <th class="text-right">MRP (Unit)</th>
                    <th class="text-center">Discount</th>
                    <th class="text-right">Offer Price</th>
                    <th class="text-center">Qty</th>
                    <th class="text-right">Line Total</th>
                    <th class="text-right">You Save</th>
                </tr>
            </thead>
            <tbody>
            <?php $sn = 1; foreach ($items as $item):
                $line_mrp     = round($item['actual_price']   * $item['quantity'], 2);
                $line_payable = round($item['discount_price'] * $item['quantity'], 2);
                $line_saved   = round($line_mrp - $line_payable, 2);
            ?>
            <tr>
                <td style="color:#aaa;"><?php echo $sn++; ?></td>
                <td style="font-weight:600;"><?php echo htmlspecialchars($item['product_name']); ?></td>
                <td class="text-center" style="color:#666;"><?php echo htmlspecialchars($item['sale_type']); ?></td>
                <td class="text-right" style="text-decoration:line-through;color:#aaa;">₹<?php echo number_format($item['actual_price'],2); ?></td>
                <td class="text-center"><span class="disc-tag"><?php echo number_format($item['discount_percent'],0); ?>% OFF</span></td>
                <td class="text-right" style="color:#c0392b;font-weight:700;">₹<?php echo number_format($item['discount_price'],2); ?></td>
                <td class="text-center" style="font-weight:700;"><?php echo $item['quantity']; ?></td>
                <td class="text-right" style="font-weight:700;color:#2c3e50;">
                    ₹<?php echo number_format($line_payable,2); ?>
                    <?php if ($item['quantity'] > 1): ?>
                    <br><small style="color:#aaa;font-weight:400;"><?php echo $item['quantity']; ?> × ₹<?php echo number_format($item['discount_price'],2); ?></small>
                    <?php endif; ?>
                </td>
                <td class="text-right"><span class="saved-amt">₹<?php echo number_format($line_saved,2); ?></span></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Totals -->
    <div class="totals-section">
        <div class="totals-box">
            <div class="total-row">
                <span class="lbl">Subtotal (MRP)</span>
                <span class="val" style="text-decoration:line-through;color:#aaa;">₹<?php echo number_format($grand_mrp,2); ?></span>
            </div>
            <div class="total-row disc">
                <span class="lbl"><i class="fas fa-tag"></i> Discount (<?php echo $savings_pct; ?>%)</span>
                <span class="val">−₹<?php echo number_format($grand_discount,2); ?></span>
            </div>
            <div class="total-row">
                <span class="lbl"><i class="fas fa-truck"></i> Delivery Charges</span>
                <span class="val" style="color:#27ae60;">FREE</span>
            </div>
            <div class="total-row grand">
                <span class="lbl">Total Payable</span>
                <span class="val">₹<?php echo number_format($grand_payable,2); ?></span>
            </div>
            <div class="savings-badge">
                🎉 Customer Saves ₹<?php echo number_format($grand_discount,2); ?> on this order!
            </div>
        </div>
    </div>

    <!-- Notes & Payment Info -->
    <div class="inv-footer">
        <div>
            <h4><i class="fas fa-sticky-note"></i> Notes</h4>
            <p><?php echo $order['notes'] ? htmlspecialchars($order['notes']) : 'No special instructions.'; ?></p>

            <h4 style="margin-top:15px;"><i class="fas fa-credit-card"></i> Payment Terms</h4>
            <p>
                Payment via Cash on Delivery (COD) or Bank Transfer.<br>
                Our team will contact you to confirm payment details.<br>
                <strong>Phone:</strong> <?php echo SITE_PHONE1; ?> / <?php echo SITE_PHONE2; ?>
            </p>
        </div>
        <div>
            <h4><i class="fas fa-shield-alt"></i> Terms & Conditions</h4>
            <p>
                • All sales are final. No refunds on firecrackers.<br>
                • Prices are inclusive of all applicable taxes.<br>
                • Delivery within 3–5 business days after confirmation.<br>
                • Products must be used responsibly and legally.<br>
                • <strong><?php echo SITE_NAME; ?></strong> is not liable for misuse.
            </p>
        </div>
    </div>

    <div class="inv-bottom">
        Thank you for shopping with <strong><?php echo SITE_NAME; ?></strong>! 🎆 &nbsp;|&nbsp;
        Generated on <?php echo date('d M Y, h:i A'); ?> &nbsp;|&nbsp;
        Invoice: <?php echo htmlspecialchars($order['order_number']); ?>
    </div>
</div>

<script>
function downloadInvoice() {
    // Simple print-to-PDF fallback (browser native)
    var old = document.title;
    document.title = 'Invoice_<?php echo $order['order_number']; ?>';
    window.print();
    document.title = old;
}
</script>
</body>
</html>
