<?php
$page_title = 'Products - Sri Shenbaga Crackers Price List';
require_once 'includes/header.php';
$db = getDB();
$B = BASE_URL;

$selected_cat = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;
$categories = $db->query("SELECT * FROM categories ORDER BY sort_order")->fetch_all(MYSQLI_ASSOC);

$cat_products = [];
foreach ($categories as $cat) {
    $cid = $cat['id'];
    $stmt = $db->prepare("SELECT * FROM products WHERE category_id=? AND is_active=1 ORDER BY name");
    $stmt->bind_param("i", $cid);
    $stmt->execute();
    $prods = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    if (!empty($prods)) $cat_products[$cid] = ['cat' => $cat, 'products' => $prods];
}
?>

<div class="products-section">
    <div class="container">
        <div class="products-header">
            <div>
                <h1>Price List</h1>
                <p style="color:var(--gray);margin-top:5px;">Upto 80% OFF on All Products!</p>
            </div>
            <div class="search-bar">
                <input type="text" id="productSearch" placeholder="Search products..." autocomplete="off">
                <button><i class="fas fa-search"></i></button>
            </div>
        </div>

        <div class="category-tabs">
            <button class="category-tab <?php echo $selected_cat==0?'active':''; ?>" data-cat-id="all">All</button>
            <?php foreach($categories as $cat): ?>
            <button class="category-tab <?php echo $selected_cat==$cat['id']?'active':''; ?>" data-cat-id="<?php echo $cat['id']; ?>">
                <?php echo htmlspecialchars($cat['name']); ?>
            </button>
            <?php endforeach; ?>
        </div>

        <?php if (empty($cat_products)): ?>
        <div class="no-products"><i class="fas fa-box-open" style="font-size:3rem;color:#ccc;display:block;margin-bottom:15px;"></i><p>No products available.</p></div>
        <?php endif; ?>

        <?php foreach($cat_products as $cid => $data):
            $cat = $data['cat'];
            $display = ($selected_cat == 0 || $selected_cat == $cid) ? '' : 'none';
        ?>
        <div class="product-category" data-cat-id="<?php echo $cid; ?>" style="display:<?php echo $display; ?>">
            <h2><?php echo htmlspecialchars($cat['name']); ?></h2>
            <table class="products-table">
                <thead>
                    <tr>
                        <th style="width:35%">Product</th>
                        <th>Sale Type</th>
                        <th>Actual Price</th>
                        <th>Discount</th>
                        <th>Offer Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach($data['products'] as $p):
                    $disc_price = calcDiscountPrice($p['actual_price'], $p['discount_percent']);
                    $img_src = (!empty($p['image']) && file_exists(__DIR__.'/uploads/'.$p['image'])) ? $B.'/uploads/'.htmlspecialchars($p['image']) : '';
                ?>
                <tr data-discount-price="<?php echo $disc_price; ?>">
                    <td>
                        <div class="product-img-cell">
                            <?php if ($img_src): ?>
                            <img src="<?php echo $img_src; ?>" alt="<?php echo htmlspecialchars($p['name']); ?>" loading="lazy">
                            <?php else: ?>
                            <div class="img-placeholder">🎆</div>
                            <?php endif; ?>
                            <span class="product-name"><?php echo htmlspecialchars($p['name']); ?></span>
                        </div>
                    </td>
                    <td><?php echo htmlspecialchars($p['sale_type']); ?></td>
                    <td><span class="price-actual"><?php echo formatPrice($p['actual_price']); ?></span></td>
                    <td><span class="discount-tag"><?php echo $p['discount_percent']; ?>% OFF</span></td>
                    <td><span class="price-discount"><?php echo formatPrice($disc_price); ?></span></td>
                    <td>
                        <div class="qty-control">
                            <button class="qty-btn" data-action="minus">&#8722;</button>
                            <input type="number" class="qty-input" value="1" min="1" max="999">
                            <button class="qty-btn" data-action="plus">+</button>
                        </div>
                    </td>
                    <td><span class="total-price"><?php echo formatPrice($disc_price); ?></span></td>
                    <td>
                        <button class="add-cart-btn" data-product-id="<?php echo $p['id']; ?>">
                            <i class="fas fa-cart-plus"></i> Add to Cart
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
