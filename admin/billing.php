<?php
$admin_title = 'Billing & Invoice';
require_once __DIR__ . '/admin_header.php';
$db = getDB();

// ── Filters ──────────────────────────────────────────────────────────────────
$search   = trim($_GET['search']   ?? '');
$status   = trim($_GET['status']   ?? '');
$date_from= trim($_GET['date_from']?? '');
$date_to  = trim($_GET['date_to']  ?? '');
$page     = max(1, (int)($_GET['page'] ?? 1));
$per_page = 15;
$offset   = ($page - 1) * $per_page;

$where = ['1=1'];
$params = [];
$types  = '';

if ($search !== '') {
    $where[] = '(o.order_number LIKE ? OR o.customer_name LIKE ? OR o.customer_phone LIKE ?)';
    $like = '%' . $search . '%';
    $params = array_merge($params, [$like, $like, $like]);
    $types .= 'sss';
}
if ($status !== '') {
    $where[] = 'o.status = ?';
    $params[] = $status;
    $types   .= 's';
}
if ($date_from !== '') {
    $where[] = 'DATE(o.created_at) >= ?';
    $params[] = $date_from;
    $types   .= 's';
}
if ($date_to !== '') {
    $where[] = 'DATE(o.created_at) <= ?';
    $params[] = $date_to;
    $types   .= 's';
}

$whereSQL = implode(' AND ', $where);

// Count
$cnt_stmt = $db->prepare("SELECT COUNT(*) FROM orders o WHERE $whereSQL");
if ($types) $cnt_stmt->bind_param($types, ...$params);
$cnt_stmt->execute();
$total_orders = $cnt_stmt->get_result()->fetch_row()[0];
$total_pages  = max(1, ceil($total_orders / $per_page));

// Orders
$q = "SELECT o.* FROM orders o WHERE $whereSQL ORDER BY o.created_at DESC LIMIT ? OFFSET ?";
$stmt = $db->prepare($q);
$fetch_types  = $types . 'ii';
$fetch_params = array_merge($params, [$per_page, $offset]);
$stmt->bind_param($fetch_types, ...$fetch_params);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// ── Summary Stats (same filters, no pagination) ───────────────────────────────
$sum_stmt = $db->prepare("
    SELECT
        COUNT(*) as total_count,
        COALESCE(SUM(total_actual_price),0)    as sum_mrp,
        COALESCE(SUM(total_discount_amount),0) as sum_discount,
        COALESCE(SUM(total_price),0)           as sum_payable
    FROM orders o WHERE $whereSQL
");
if ($types) $sum_stmt->bind_param($types, ...$params);
$sum_stmt->execute();
$stats = $sum_stmt->get_result()->fetch_assoc();

$statuses_list = ['pending','confirmed','processing','shipped','delivered','cancelled'];
$status_colors  = [
    'pending'    => '#f39c12',
    'confirmed'  => '#3498db',
    'processing' => '#9b59b6',
    'shipped'    => '#1abc9c',
    'delivered'  => '#27ae60',
    'cancelled'  => '#e74c3c',
];
?>

<!-- ── Billing Stats ─────────────────────────────────────────────────────── -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:15px;margin-bottom:25px;">
    <?php
    $cards = [
        ['icon'=>'fas fa-file-invoice','label'=>'Total Invoices','val'=>number_format($stats['total_count']),'color'=>'#c0392b'],
        ['icon'=>'fas fa-tags','label'=>'Total MRP','val'=>'₹'.number_format($stats['sum_mrp'],2),'color'=>'#8e44ad'],
        ['icon'=>'fas fa-percentage','label'=>'Total Discount','val'=>'₹'.number_format($stats['sum_discount'],2),'color'=>'#e67e22'],
        ['icon'=>'fas fa-rupee-sign','label'=>'Total Payable','val'=>'₹'.number_format($stats['sum_payable'],2),'color'=>'#27ae60'],
    ];
    foreach ($cards as $c): ?>
    <div style="background:#fff;border-radius:12px;padding:18px;box-shadow:0 4px 15px rgba(0,0,0,.08);border-left:4px solid <?php echo $c['color']; ?>;">
        <div style="display:flex;align-items:center;gap:12px;">
            <div style="width:44px;height:44px;border-radius:10px;background:<?php echo $c['color']; ?>20;display:flex;align-items:center;justify-content:center;">
                <i class="<?php echo $c['icon']; ?>" style="color:<?php echo $c['color']; ?>;font-size:18px;"></i>
            </div>
            <div>
                <div style="font-size:11px;color:#888;text-transform:uppercase;letter-spacing:.5px;"><?php echo $c['label']; ?></div>
                <div style="font-size:1.25rem;font-weight:700;color:#2c3e50;"><?php echo $c['val']; ?></div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- ── Filters ───────────────────────────────────────────────────────────── -->
<div style="background:#fff;border-radius:12px;padding:20px;box-shadow:0 4px 15px rgba(0,0,0,.08);margin-bottom:20px;">
    <form method="GET" style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end;">
        <div style="flex:1;min-width:180px;">
            <label style="font-size:12px;color:#666;font-weight:600;display:block;margin-bottom:5px;">🔍 Search</label>
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                placeholder="Order No / Name / Phone"
                style="width:100%;padding:9px 12px;border:2px solid #eee;border-radius:8px;font-size:14px;box-sizing:border-box;">
        </div>
        <div style="min-width:140px;">
            <label style="font-size:12px;color:#666;font-weight:600;display:block;margin-bottom:5px;">📋 Status</label>
            <select name="status" style="width:100%;padding:9px 12px;border:2px solid #eee;border-radius:8px;font-size:14px;">
                <option value="">All Status</option>
                <?php foreach ($statuses_list as $s): ?>
                <option value="<?php echo $s; ?>" <?php echo $status===$s?'selected':''; ?>><?php echo ucfirst($s); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="min-width:140px;">
            <label style="font-size:12px;color:#666;font-weight:600;display:block;margin-bottom:5px;">📅 From Date</label>
            <input type="date" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>"
                style="width:100%;padding:9px 12px;border:2px solid #eee;border-radius:8px;font-size:14px;">
        </div>
        <div style="min-width:140px;">
            <label style="font-size:12px;color:#666;font-weight:600;display:block;margin-bottom:5px;">📅 To Date</label>
            <input type="date" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>"
                style="width:100%;padding:9px 12px;border:2px solid #eee;border-radius:8px;font-size:14px;">
        </div>
        <div style="display:flex;gap:8px;">
            <button type="submit" style="background:var(--primary,#c0392b);color:#fff;border:none;padding:9px 20px;border-radius:8px;cursor:pointer;font-size:14px;font-weight:600;">
                <i class="fas fa-filter"></i> Filter
            </button>
            <a href="billing.php" style="background:#eee;color:#555;border:none;padding:9px 16px;border-radius:8px;cursor:pointer;font-size:14px;font-weight:600;text-decoration:none;">
                <i class="fas fa-times"></i> Reset
            </a>
        </div>
    </form>
</div>

<!-- ── Billing Table ──────────────────────────────────────────────────────── -->
<div style="background:#fff;border-radius:12px;box-shadow:0 4px 15px rgba(0,0,0,.08);overflow:hidden;">
    <div style="display:flex;justify-content:space-between;align-items:center;padding:18px 20px;border-bottom:2px solid #f0f0f0;">
        <h2 style="margin:0;font-size:1.1rem;"><i class="fas fa-file-invoice-dollar" style="color:var(--primary,#c0392b);"></i> Invoice List
            <span style="font-size:13px;color:#888;font-weight:400;"> — <?php echo $total_orders; ?> records</span>
        </h2>
        <button onclick="window.print()" style="background:#2c3e50;color:#fff;border:none;padding:8px 16px;border-radius:8px;cursor:pointer;font-size:13px;">
            <i class="fas fa-print"></i> Print List
        </button>
    </div>
    <div style="overflow-x:auto;">
    <table style="width:100%;border-collapse:collapse;font-size:13px;">
        <thead>
            <tr style="background:#f8f9fa;">
                <th style="padding:12px 16px;text-align:left;color:#555;font-weight:700;white-space:nowrap;border-bottom:2px solid #eee;">#</th>
                <th style="padding:12px 16px;text-align:left;color:#555;font-weight:700;border-bottom:2px solid #eee;">Order No</th>
                <th style="padding:12px 16px;text-align:left;color:#555;font-weight:700;border-bottom:2px solid #eee;">Customer</th>
                <th style="padding:12px 16px;text-align:left;color:#555;font-weight:700;border-bottom:2px solid #eee;">Date</th>
                <th style="padding:12px 16px;text-align:right;color:#555;font-weight:700;border-bottom:2px solid #eee;">MRP</th>
                <th style="padding:12px 16px;text-align:right;color:#555;font-weight:700;border-bottom:2px solid #eee;">Discount</th>
                <th style="padding:12px 16px;text-align:right;color:#555;font-weight:700;border-bottom:2px solid #eee;">Discount%</th>
                <th style="padding:12px 16px;text-align:right;color:#555;font-weight:700;border-bottom:2px solid #eee;">Payable</th>
                <th style="padding:12px 16px;text-align:center;color:#555;font-weight:700;border-bottom:2px solid #eee;">Status</th>
                <th style="padding:12px 16px;text-align:center;color:#555;font-weight:700;border-bottom:2px solid #eee;">Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($orders)): ?>
            <tr><td colspan="10" style="text-align:center;padding:40px;color:#aaa;">No invoices found.</td></tr>
        <?php endif; ?>
        <?php $row_num = $offset + 1; foreach ($orders as $o):
            $disc_pct = $o['total_actual_price'] > 0
                ? round(($o['total_discount_amount'] / $o['total_actual_price']) * 100, 1)
                : 0;
            $color = $status_colors[$o['status']] ?? '#888';
        ?>
        <tr style="border-bottom:1px solid #f0f0f0;transition:background .15s;" onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background=''">
            <td style="padding:12px 16px;color:#aaa;"><?php echo $row_num++; ?></td>
            <td style="padding:12px 16px;font-weight:700;color:#c0392b;"><?php echo htmlspecialchars($o['order_number']); ?></td>
            <td style="padding:12px 16px;">
                <div style="font-weight:600;"><?php echo htmlspecialchars($o['customer_name']); ?></div>
                <div style="color:#888;font-size:11px;"><?php echo htmlspecialchars($o['customer_phone']); ?></div>
            </td>
            <td style="padding:12px 16px;white-space:nowrap;color:#666;"><?php echo date('d M Y', strtotime($o['created_at'])); ?><br><span style="font-size:11px;color:#aaa;"><?php echo date('h:i A', strtotime($o['created_at'])); ?></span></td>
            <td style="padding:12px 16px;text-align:right;text-decoration:line-through;color:#aaa;">₹<?php echo number_format($o['total_actual_price'],2); ?></td>
            <td style="padding:12px 16px;text-align:right;color:#e67e22;font-weight:600;">−₹<?php echo number_format($o['total_discount_amount'],2); ?></td>
            <td style="padding:12px 16px;text-align:center;">
                <span style="background:#fff3e0;color:#e67e22;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:700;"><?php echo $disc_pct; ?>%</span>
            </td>
            <td style="padding:12px 16px;text-align:right;font-weight:700;font-size:1rem;color:#27ae60;">₹<?php echo number_format($o['total_price'],2); ?></td>
            <td style="padding:12px 16px;text-align:center;">
                <span style="background:<?php echo $color; ?>20;color:<?php echo $color; ?>;padding:4px 12px;border-radius:20px;font-size:11px;font-weight:700;text-transform:uppercase;"><?php echo $o['status']; ?></span>
            </td>
            <td style="padding:12px 16px;text-align:center;white-space:nowrap;">
                <a href="invoice.php?id=<?php echo $o['id']; ?>" target="_blank"
                   style="background:#c0392b;color:#fff;padding:5px 12px;border-radius:6px;text-decoration:none;font-size:12px;font-weight:600;margin-right:4px;">
                   <i class="fas fa-file-invoice"></i> Invoice
                </a>
                <a href="order_view.php?id=<?php echo $o['id']; ?>"
                   style="background:#3498db;color:#fff;padding:5px 12px;border-radius:6px;text-decoration:none;font-size:12px;font-weight:600;">
                   <i class="fas fa-eye"></i> View
                </a>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
        <?php if (!empty($orders)): ?>
        <tfoot>
            <tr style="background:#f0f8f0;font-weight:700;">
                <td colspan="4" style="padding:14px 16px;text-align:right;font-size:13px;color:#555;">Page Total:</td>
                <td style="padding:14px 16px;text-align:right;color:#888;text-decoration:line-through;">
                    ₹<?php echo number_format(array_sum(array_column($orders,'total_actual_price')),2); ?>
                </td>
                <td style="padding:14px 16px;text-align:right;color:#e67e22;">
                    −₹<?php echo number_format(array_sum(array_column($orders,'total_discount_amount')),2); ?>
                </td>
                <td></td>
                <td style="padding:14px 16px;text-align:right;color:#27ae60;font-size:1.05rem;">
                    ₹<?php echo number_format(array_sum(array_column($orders,'total_price')),2); ?>
                </td>
                <td colspan="2"></td>
            </tr>
            <?php if ($total_orders > $per_page): ?>
            <tr style="background:#fef9f0;">
                <td colspan="4" style="padding:12px 16px;text-align:right;font-size:13px;color:#e67e22;font-weight:700;">All <?php echo $total_orders; ?> Orders Grand Total:</td>
                <td style="padding:12px 16px;text-align:right;color:#aaa;text-decoration:line-through;">₹<?php echo number_format($stats['sum_mrp'],2); ?></td>
                <td style="padding:12px 16px;text-align:right;color:#e67e22;font-weight:700;">−₹<?php echo number_format($stats['sum_discount'],2); ?></td>
                <td></td>
                <td style="padding:12px 16px;text-align:right;color:#27ae60;font-weight:700;font-size:1.1rem;">₹<?php echo number_format($stats['sum_payable'],2); ?></td>
                <td colspan="2"></td>
            </tr>
            <?php endif; ?>
        </tfoot>
        <?php endif; ?>
    </table>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div style="display:flex;justify-content:center;align-items:center;gap:6px;padding:20px;">
        <?php
        $qs = $_GET;
        for ($p = 1; $p <= $total_pages; $p++):
            $qs['page'] = $p;
            $href = 'billing.php?' . http_build_query($qs);
            $active = $p === $page;
        ?>
        <a href="<?php echo $href; ?>" style="padding:7px 14px;border-radius:8px;text-decoration:none;font-size:13px;font-weight:600;
            background:<?php echo $active ? '#c0392b' : '#f0f0f0'; ?>;
            color:<?php echo $active ? '#fff' : '#555'; ?>;"><?php echo $p; ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<style>
@media print {
    .admin-sidebar, .admin-topbar, form, .admin-content > div:last-child a, button { display: none !important; }
    body { background: #fff; }
    .admin-content { margin: 0; padding: 0; }
}
</style>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
