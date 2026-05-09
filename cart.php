<?php
$page_title = 'Cart - Sri Shenbaga Crackers';
require_once 'includes/header.php';
$B = BASE_URL;

$cart = $_SESSION['cart'] ?? [];
$total_actual = $total_payable = 0;
foreach ($cart as $item) {
    $total_actual  += $item['actual_price']   * $item['qty'];
    $total_payable += $item['discount_price'] * $item['qty'];
}
$total_discount = $total_actual - $total_payable;
?>
<div class="cart-section">
    <div class="container">
        <h1 style="margin-bottom:25px;"><i class="fas fa-shopping-cart"></i> Your Cart
            <?php if (!empty($cart)): ?><span style="font-size:1rem;color:var(--gray);font-weight:400;"> (<?php echo count($cart); ?> items)</span><?php endif; ?>
        </h1>

        <?php if (empty($cart)): ?>
        <div class="cart-empty" style="background:#fff;border-radius:10px;box-shadow:0 4px 20px rgba(0,0,0,0.1);">
            <i class="fas fa-shopping-cart"></i>
            <h3>Your cart is empty!</h3>
            <p>Looks like you haven't added any crackers yet.</p>
            <a href="<?php echo $B; ?>/products.php" class="btn btn-red" style="margin-top:20px;"><i class="fas fa-store"></i> Shop Now</a>
        </div>
        <?php else: ?>
        <div class="cart-layout">
            <div class="cart-table-wrap">
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
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($cart as $pid => $item):
                        $line_price = $item['discount_price'] * $item['qty'];
                        $img_src = (!empty($item['image']) && file_exists(__DIR__.'/uploads/'.$item['image'])) ? $B.'/uploads/'.htmlspecialchars($item['image']) : '';
                    ?>
                    <tr>
                        <td>
                            <div class="product-img-cell">
                                <?php if ($img_src): ?>
                                <img src="<?php echo $img_src; ?>" alt="">
                                <?php else: ?>
                                <div class="img-placeholder">🎆</div>
                                <?php endif; ?>
                                <span class="product-name"><?php echo htmlspecialchars($item['name']); ?></span>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($item['sale_type']); ?></td>
                        <td><span class="price-actual"><?php echo formatPrice($item['actual_price']); ?></span></td>
                        <td><span class="discount-tag"><?php echo $item['discount_percent']; ?>% OFF</span></td>
                        <td><span class="price-discount"><?php echo formatPrice($item['discount_price']); ?></span></td>
                        <td>
                            <input type="number" class="qty-input cart-qty-input" value="<?php echo $item['qty']; ?>" min="1" max="999" data-product-id="<?php echo $pid; ?>" style="width:70px;">
                        </td>
                        <td><strong class="price-discount"><?php echo formatPrice($line_price); ?></strong>
                            <?php if ($item['qty'] > 1): ?>
                            <br><small style="color:var(--gray);"><?php echo $item['qty']; ?> &times; <?php echo formatPrice($item['discount_price']); ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="remove-btn" data-product-id="<?php echo $pid; ?>" title="Remove"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <div style="padding:15px;display:flex;gap:10px;justify-content:flex-end;border-top:1px solid #eee;">
                    <a href="<?php echo $B; ?>/products.php" class="btn btn-outline" style="border-color:var(--primary);color:var(--primary);"><i class="fas fa-arrow-left"></i> Continue Shopping</a>
                    <button onclick="clearCart()" style="background:#eee;border:none;padding:10px 20px;border-radius:6px;cursor:pointer;font-size:14px;"><i class="fas fa-trash"></i> Clear Cart</button>
                </div>
            </div>

            <div class="cart-summary">
                <h3><i class="fas fa-receipt"></i> Order Summary</h3>
                <div class="summary-row"><span>Items (<?php echo count($cart); ?>)</span><span><?php echo formatPrice($total_actual); ?></span></div>
                <div class="summary-row discount"><span><i class="fas fa-tag"></i> Total Discount</span><span>&minus; <?php echo formatPrice($total_discount); ?></span></div>
                <div class="summary-row"><span>Delivery</span><span style="color:var(--accent);font-weight:600;">FREE</span></div>
                <div class="summary-row total"><span>Total Payable</span><span><?php echo formatPrice($total_payable); ?></span></div>
                <div style="background:rgba(39,174,96,0.1);border-radius:8px;padding:12px;margin:15px 0;font-size:14px;color:var(--accent);font-weight:600;text-align:center;">
                    🎉 You save <?php echo formatPrice($total_discount); ?>!
                </div>
                <a href="<?php echo $B; ?>/checkout.php" style="display:block;">
                    <button class="checkout-btn"><i class="fas fa-credit-card"></i> Proceed to Checkout</button>
                </a>
                <div style="margin-top:12px;font-size:12px;color:var(--gray);text-align:center;">
                    <i class="fas fa-shield-alt"></i> 100% Secure &amp; Safe Shopping
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<script>
window.BASE_URL = '<?php echo addslashes(BASE_URL); ?>';
function clearCart(){
    if(!confirm('Clear all items from cart?')) return;
    fetch(window.BASE_URL+'/cart_action.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'action=clear'}).then(function(){location.reload();});
}
</script>
<?php require_once 'includes/footer.php'; ?>
