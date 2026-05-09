<?php
$admin_title = 'Bulk Discount Settings';
require_once __DIR__ . '/admin_header.php';
$db = getDB();
$msg = '';

// Apply global discount to all products
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_global'])) {
    $disc = min(100, max(0, (float)$_POST['global_discount']));
    $cat_id = (int)($_POST['cat_id'] ?? 0);
    if ($cat_id > 0) {
        $db->query("UPDATE products SET discount_percent=$disc WHERE category_id=$cat_id");
        $msg = "Discount updated to $disc% for selected category.";
    } else {
        $db->query("UPDATE products SET discount_percent=$disc");
        $msg = "Global discount updated to $disc% for ALL products.";
    }
}

// Update individual category discount
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_category'])) {
    $cat_id = (int)$_POST['cat_id_single'];
    $disc   = min(100, max(0, (float)$_POST['cat_discount']));
    $db->query("UPDATE products SET discount_percent=$disc WHERE category_id=$cat_id");
    $msg = "Discount updated to $disc% for category.";
}

$categories = $db->query("SELECT c.*, ROUND(AVG(p.discount_percent),2) as avg_disc, COUNT(p.id) as prod_count FROM categories c LEFT JOIN products p ON c.id=p.category_id AND p.is_active=1 GROUP BY c.id ORDER BY c.sort_order")->fetch_all(MYSQLI_ASSOC);
$global_avg = $db->query("SELECT ROUND(AVG(discount_percent),2) FROM products WHERE is_active=1")->fetch_row()[0] ?? 80;
?>

<?php if ($msg): ?>
<div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($msg); ?></div>
<?php endif; ?>

<!-- Global Bulk Update -->
<div class="card">
    <div class="card-header"><h2><i class="fas fa-percentage"></i> Bulk Discount Update</h2></div>
    <div class="card-body">
        <p style="color:var(--gray);margin-bottom:20px;">Current global average discount: <strong style="color:var(--primary);"><?php echo $global_avg; ?>%</strong></p>
        <form method="POST" class="admin-form" onsubmit="return confirm('Apply this discount to all/selected products?')">
            <input type="hidden" name="apply_global" value="1">
            <div style="display:grid;grid-template-columns:1fr 1fr auto;gap:15px;align-items:end;">
                <div class="form-group" style="margin:0;">
                    <label>Discount % to Apply</label>
                    <input type="number" name="global_discount" value="80" min="0" max="100" step="0.01" required>
                </div>
                <div class="form-group" style="margin:0;">
                    <label>Apply to Category (leave blank for ALL)</label>
                    <select name="cat_id">
                        <option value="">All Categories</option>
                        <?php foreach($categories as $c): ?>
                        <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-red"><i class="fas fa-bolt"></i> Apply</button>
            </div>
        </form>
    </div>
</div>

<!-- Per-Category Discounts -->
<div class="card">
    <div class="card-header"><h2><i class="fas fa-tags"></i> Per-Category Discount</h2></div>
    <div style="overflow-x:auto;">
    <table class="admin-table">
        <thead><tr><th>Category</th><th>Products</th><th>Current Avg Discount</th><th>Set New Discount</th></tr></thead>
        <tbody>
        <?php foreach($categories as $c): ?>
        <tr>
            <td><strong><?php echo htmlspecialchars($c['name']); ?></strong></td>
            <td><?php echo $c['prod_count']; ?> products</td>
            <td><span class="discount-tag"><?php echo $c['avg_disc'] ?? 0; ?>% OFF</span></td>
            <td>
                <form method="POST" style="display:flex;gap:8px;align-items:center;">
                    <input type="hidden" name="update_category" value="1">
                    <input type="hidden" name="cat_id_single" value="<?php echo $c['id']; ?>">
                    <input type="number" name="cat_discount" value="<?php echo $c['avg_disc'] ?? 80; ?>" min="0" max="100" step="0.01" style="width:80px;padding:6px;border:1px solid #ddd;border-radius:5px;">
                    <button type="submit" class="btn-sm btn-edit">Apply</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>
<?php require_once __DIR__ . '/admin_footer.php'; ?>
