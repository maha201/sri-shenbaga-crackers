<?php
$admin_title = 'Manage Categories';
require_once __DIR__ . '/admin_header.php';
$db = getDB();
$msg = '';

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $count = $db->query("SELECT COUNT(*) FROM products WHERE category_id=$id")->fetch_row()[0];
    if ($count > 0) {
        $msg = "Cannot delete: $count products exist in this category.";
    } else {
        $db->query("DELETE FROM categories WHERE id=$id");
        $msg = 'Category deleted.';
    }
}

$edit_cat = null;
if (isset($_GET['edit'])) {
    $edit_cat = $db->query("SELECT * FROM categories WHERE id=".(int)$_GET['edit'])->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $id   = (int)($_POST['id']??0);
    $name = trim($_POST['name']??'');
    $sort = (int)($_POST['sort_order']??0);
    if (!$name) { $msg = 'Name required.'; }
    elseif ($id>0) {
        $stmt = $db->prepare("UPDATE categories SET name=?,sort_order=? WHERE id=?");
        $stmt->bind_param("sii",$name,$sort,$id);
        $stmt->execute();
        $msg = 'Category updated!';
        $edit_cat = null;
    } else {
        $stmt = $db->prepare("INSERT INTO categories (name,sort_order) VALUES (?,?)");
        $stmt->bind_param("si",$name,$sort);
        $stmt->execute();
        $msg = 'Category added!';
    }
}

$categories = $db->query("SELECT c.*, COUNT(p.id) as prod_count FROM categories c LEFT JOIN products p ON c.id=p.category_id AND p.is_active=1 GROUP BY c.id ORDER BY c.sort_order,c.name")->fetch_all(MYSQLI_ASSOC);
?>
<?php if ($msg): ?><div class="alert alert-<?php echo str_contains($msg,'Cannot')?'error':'success'; ?>"><i class="fas fa-info-circle"></i> <?php echo htmlspecialchars($msg); ?></div><?php endif; ?>

<div class="card">
    <div class="card-header">
        <h2><?php echo $edit_cat?'<i class="fas fa-edit"></i> Edit Category':'<i class="fas fa-plus"></i> Add Category'; ?></h2>
        <?php if ($edit_cat): ?><a href="<?php echo BASE_URL; ?>/admin/categories.php" class="btn-sm btn-edit">+ Add New</a><?php endif; ?>
    </div>
    <div class="card-body">
    <form method="POST" class="admin-form">
        <?php if ($edit_cat): ?><input type="hidden" name="id" value="<?php echo $edit_cat['id']; ?>"><?php endif; ?>
        <div style="display:grid;grid-template-columns:1fr 200px auto;gap:15px;align-items:end;">
            <div class="form-group" style="margin:0">
                <label>Category Name <span style="color:red">*</span></label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($edit_cat['name']??''); ?>" placeholder="e.g. Sound Crackers" required>
            </div>
            <div class="form-group" style="margin:0">
                <label>Sort Order</label>
                <input type="number" name="sort_order" value="<?php echo $edit_cat['sort_order']??0; ?>" min="0">
            </div>
            <div>
                <button type="submit" class="btn btn-red"><?php echo $edit_cat?'<i class="fas fa-save"></i> Update':'<i class="fas fa-plus"></i> Add'; ?></button>
                <?php if ($edit_cat): ?><a href="<?php echo BASE_URL; ?>/admin/categories.php" class="btn" style="background:#eee;color:#333;margin-left:8px;">Cancel</a><?php endif; ?>
            </div>
        </div>
    </form>
    </div>
</div>

<div class="card">
    <div class="card-header"><h2><i class="fas fa-tags"></i> Categories (<?php echo count($categories); ?>)</h2></div>
    <div style="overflow-x:auto;">
    <table class="admin-table">
        <thead><tr><th>#</th><th>Category Name</th><th>Sort Order</th><th>Products</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach($categories as $c): ?>
        <tr>
            <td><?php echo $c['id']; ?></td>
            <td><?php echo htmlspecialchars($c['name']); ?></td>
            <td><?php echo $c['sort_order']; ?></td>
            <td><span class="badge badge-active"><?php echo $c['prod_count']; ?> products</span></td>
            <td>
                <a href="?edit=<?php echo $c['id']; ?>" class="btn-sm btn-edit"><i class="fas fa-edit"></i> Edit</a>
                <a href="?delete=<?php echo $c['id']; ?>" class="btn-sm btn-delete" onclick="return confirm('Delete category?')"><i class="fas fa-trash"></i> Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
