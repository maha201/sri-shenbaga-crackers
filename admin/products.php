<?php
$admin_title = 'Manage Products';
require_once __DIR__ . '/admin_header.php';
$db = getDB();

$msg = '';
$msg_type = 'success';

// DELETE
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    // Check if used in orders
    $used = $db->query("SELECT COUNT(*) FROM order_items WHERE product_id=$id")->fetch_row()[0];
    if ($used > 0) {
        $db->query("UPDATE products SET is_active=0 WHERE id=$id");
        $msg = 'Product deactivated (used in orders).';
    } else {
        $db->query("DELETE FROM products WHERE id=$id");
        $msg = 'Product deleted successfully.';
    }
}

// TOGGLE ACTIVE
if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $db->query("UPDATE products SET is_active = 1 - is_active WHERE id=$id");
    $msg = 'Product status updated.';
}

// ADD / EDIT
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id           = (int)($_POST['id'] ?? 0);
    $category_id  = (int)$_POST['category_id'];
    $name         = trim($_POST['name']);
    $sale_type    = trim($_POST['sale_type']);
    $actual_price = (float)$_POST['actual_price'];
    $discount_pct = min(100, max(0, (float)$_POST['discount_percent']));
    $is_active    = isset($_POST['is_active']) ? 1 : 0;
    $image        = '';

    // Handle image upload
    if (!empty($_FILES['image']['name'])) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
            $filename = 'prod_' . time() . '_' . rand(100,999) . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], dirname(__DIR__) . '/uploads/' . $filename);
            $image = $filename;
        }
    }

    if (!$name || !$actual_price || !$category_id) {
        $msg = 'Please fill all required fields.';
        $msg_type = 'error';
    } else {
        if ($id > 0) {
            // Update existing
            if ($image) {
                $stmt = $db->prepare("UPDATE products SET category_id=?,name=?,sale_type=?,actual_price=?,discount_percent=?,image=?,is_active=?,updated_at=NOW() WHERE id=?");
                $stmt->bind_param("issddsii", $category_id,$name,$sale_type,$actual_price,$discount_pct,$image,$is_active,$id);
            } else {
                $stmt = $db->prepare("UPDATE products SET category_id=?,name=?,sale_type=?,actual_price=?,discount_percent=?,is_active=?,updated_at=NOW() WHERE id=?");
                $stmt->bind_param("issdsdii", $category_id,$name,$sale_type,$actual_price,$discount_pct,$is_active,$id);
            }
            $stmt->execute();
            $msg = 'Product updated successfully!';
        } else {
            // Insert new
            $stmt = $db->prepare("INSERT INTO products (category_id,name,sale_type,actual_price,discount_percent,image,is_active) VALUES (?,?,?,?,?,?,?)");
            $stmt->bind_param("issddsi", $category_id,$name,$sale_type,$actual_price,$discount_pct,$image,$is_active);
            $stmt->execute();
            $msg = 'Product added successfully!';
        }
    }
}

// GET product for edit
$edit_product = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $edit_product = $db->query("SELECT * FROM products WHERE id=$edit_id")->fetch_assoc();
}

// GET products list (with search and filter)
$search = trim($_GET['search'] ?? '');
$filter_cat = (int)($_GET['cat'] ?? 0);
$where = ['1=1'];
if ($search) $where[] = "p.name LIKE '%" . $db->real_escape_string($search) . "%'";
if ($filter_cat) $where[] = "p.category_id = $filter_cat";
$where_sql = implode(' AND ', $where);

$products = $db->query("SELECT p.*, c.name as cat_name FROM products p JOIN categories c ON p.category_id=c.id WHERE $where_sql ORDER BY c.sort_order, p.name")->fetch_all(MYSQLI_ASSOC);
$categories = $db->query("SELECT * FROM categories ORDER BY sort_order")->fetch_all(MYSQLI_ASSOC);
?>

<?php if ($msg): ?>
<div class="alert alert-<?php echo $msg_type==='error'?'error':'success'; ?>">
    <i class="fas fa-<?php echo $msg_type==='error'?'exclamation-circle':'check-circle'; ?>"></i>
    <?php echo htmlspecialchars($msg); ?>
</div>
<?php endif; ?>

<!-- Add/Edit Form -->
<div class="card">
    <div class="card-header">
        <h2><?php echo $edit_product ? '<i class="fas fa-edit"></i> Edit Product' : '<i class="fas fa-plus"></i> Add New Product'; ?></h2>
        <?php if ($edit_product): ?><a href="<?php echo BASE_URL; ?>/admin/products.php" class="btn-sm btn-edit">+ Add New</a><?php endif; ?>
    </div>
    <div class="card-body">
    <form method="POST" enctype="multipart/form-data" class="admin-form">
        <?php if ($edit_product): ?><input type="hidden" name="id" value="<?php echo $edit_product['id']; ?>"><?php endif; ?>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:15px;">
            <div class="form-group">
                <label>Category <span style="color:red">*</span></label>
                <select name="category_id" required>
                    <option value="">Select Category</option>
                    <?php foreach($categories as $c): ?>
                    <option value="<?php echo $c['id']; ?>" <?php echo ($edit_product && $edit_product['category_id']==$c['id'])?'selected':''; ?>>
                        <?php echo htmlspecialchars($c['name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="grid-column:span 2;">
                <label>Product Name <span style="color:red">*</span></label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($edit_product['name']??''); ?>" placeholder="Product name" required>
            </div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:15px;">
            <div class="form-group">
                <label>Sale Type</label>
                <select name="sale_type">
                    <?php foreach(['Pkt','Box','1 Box','Roll','Piece','Set','Dozen'] as $st): ?>
                    <option value="<?php echo $st; ?>" <?php echo ($edit_product && $edit_product['sale_type']==$st)?'selected':''; ?>><?php echo $st; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Actual Price (MRP) ₹ <span style="color:red">*</span></label>
                <input type="number" step="0.01" min="0.01" name="actual_price" id="actual_price" value="<?php echo $edit_product['actual_price']??''; ?>" placeholder="e.g. 500.00" required>
            </div>
            <div class="form-group">
                <label>Discount % <span style="color:red">*</span></label>
                <input type="number" step="0.01" min="0" max="100" name="discount_percent" id="discount_percent" value="<?php echo $edit_product['discount_percent']??80; ?>" placeholder="80" required>
                <div class="discount-info" id="discount_info"></div>
            </div>
            <div class="form-group">
                <label>Product Image</label>
                <input type="file" name="image" accept="image/*">
                <?php if (!empty($edit_product['image'])): ?>
                <img src="<?php echo BASE_URL; ?>/uploads/<?php echo htmlspecialchars($edit_product['image']); ?>" style="width:50px;height:50px;object-fit:cover;border-radius:5px;margin-top:5px;">
                <?php endif; ?>
            </div>
        </div>
        <div class="form-group">
            <label><input type="checkbox" name="is_active" value="1" <?php echo (!isset($edit_product) || $edit_product['is_active'])?'checked':''; ?>> Active (visible to customers)</label>
        </div>
        <button type="submit" class="btn btn-red"><?php echo $edit_product ? '<i class="fas fa-save"></i> Update Product' : '<i class="fas fa-plus"></i> Add Product'; ?></button>
        <?php if ($edit_product): ?>
        <a href="<?php echo BASE_URL; ?>/admin/products.php" class="btn" style="background:#eee;color:#333;margin-left:10px;">Cancel</a>
        <?php endif; ?>
    </form>
    </div>
</div>

<!-- Products List -->
<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-box"></i> Products (<?php echo count($products); ?>)</h2>
        <form method="GET" style="display:flex;gap:10px;align-items:center;">
            <input type="text" name="search" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>" style="padding:7px 12px;border:1px solid #ddd;border-radius:6px;font-size:13px;">
            <select name="cat" style="padding:7px 12px;border:1px solid #ddd;border-radius:6px;font-size:13px;">
                <option value="">All Categories</option>
                <?php foreach($categories as $c): ?>
                <option value="<?php echo $c['id']; ?>" <?php echo $filter_cat==$c['id']?'selected':''; ?>><?php echo htmlspecialchars($c['name']); ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn-sm btn-view">Filter</button>
        </form>
    </div>
    <div style="overflow-x:auto;">
    <table class="admin-table">
        <thead>
            <tr><th>#</th><th>Product</th><th>Category</th><th>Sale Type</th><th>Actual Price</th><th>Discount</th><th>Offer Price</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php if (empty($products)): ?>
        <tr><td colspan="9" style="text-align:center;padding:30px;color:var(--gray);">No products found.</td></tr>
        <?php endif; ?>
        <?php foreach($products as $p):
            $disc_price = calcDiscountPrice($p['actual_price'], $p['discount_percent']);
        ?>
        <tr>
            <td><?php echo $p['id']; ?></td>
            <td>
                <div style="display:flex;align-items:center;gap:10px;">
                    <?php if (!empty($p['image']) && file_exists(dirname(__DIR__).'/uploads/'.$p['image'])): ?>
                    <img src="<?php echo BASE_URL; ?>/uploads/<?php echo htmlspecialchars($p['image']); ?>" style="width:40px;height:40px;object-fit:cover;border-radius:5px;">
                    <?php else: ?>
                    <div style="width:40px;height:40px;background:var(--primary);border-radius:5px;display:flex;align-items:center;justify-content:center;color:#fff;">🎆</div>
                    <?php endif; ?>
                    <span><?php echo htmlspecialchars($p['name']); ?></span>
                </div>
            </td>
            <td><?php echo htmlspecialchars($p['cat_name']); ?></td>
            <td><?php echo htmlspecialchars($p['sale_type']); ?></td>
            <td><span style="text-decoration:line-through;color:var(--gray);">₹<?php echo number_format($p['actual_price'],2); ?></span></td>
            <td><span class="discount-tag"><?php echo $p['discount_percent']; ?>% OFF</span></td>
            <td><strong style="color:var(--primary);">₹<?php echo number_format($disc_price,2); ?></strong></td>
            <td>
                <a href="?toggle=<?php echo $p['id']; ?>" onclick="return confirm('Toggle status?')">
                    <span class="badge badge-<?php echo $p['is_active']?'active':'inactive'; ?>"><?php echo $p['is_active']?'Active':'Inactive'; ?></span>
                </a>
            </td>
            <td style="white-space:nowrap;">
                <a href="?edit=<?php echo $p['id']; ?>" class="btn-sm btn-edit"><i class="fas fa-edit"></i></a>
                <a href="?delete=<?php echo $p['id']; ?>" class="btn-sm btn-delete" onclick="return confirm('Delete this product?')"><i class="fas fa-trash"></i></a>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
