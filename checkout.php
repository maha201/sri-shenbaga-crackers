<?php
$page_title = 'Checkout - Sri Shenbaga Crackers';
require_once 'includes/header.php';
$db = getDB();
$B = BASE_URL;

define('MIN_ORDER_AMOUNT', 3000);

$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) { header('Location: ' . $B . '/cart.php'); exit; }

$total_actual = $total_payable = 0;
foreach ($cart as $item) {
    $total_actual  += $item['actual_price']   * $item['qty'];
    $total_payable += $item['discount_price'] * $item['qty'];
}
$total_discount   = $total_actual - $total_payable;
$min_order_met    = $total_payable >= MIN_ORDER_AMOUNT;
$amount_remaining = max(0, MIN_ORDER_AMOUNT - $total_payable);

$errors = [];
$success = false;
$order_number = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Server-side minimum order check
    if (!$min_order_met) {
        $errors[] = 'Minimum order amount is ₹' . number_format(MIN_ORDER_AMOUNT, 2) . '. Please add more items.';
    }
    $name    = trim($_POST['customer_name'] ?? '');
    $phone   = trim($_POST['customer_phone'] ?? '');
    $email   = trim($_POST['customer_email'] ?? '');
    $address = trim($_POST['customer_address'] ?? '');
    $city    = trim($_POST['customer_city'] ?? '');
    $state   = trim($_POST['customer_state'] ?? 'Tamil Nadu');
    $pincode = trim($_POST['customer_pincode'] ?? '');
    $notes   = trim($_POST['notes'] ?? '');

    if (!$name)    $errors[] = 'Full name is required';
    if (!preg_match('/^\d{10}$/', preg_replace('/[\s\-]/','', $phone))) $errors[] = 'Valid 10-digit phone required';
    if (!$address) $errors[] = 'Delivery address is required';
    if (!$city)    $errors[] = 'City is required';
    if (!preg_match('/^\d{6}$/', $pincode)) $errors[] = 'Valid 6-digit pincode required';

    if (empty($errors)) {
        $order_number = generateOrderNumber();
        $db->begin_transaction();
        try {
            $stmt = $db->prepare("INSERT INTO orders (order_number,customer_name,customer_phone,customer_email,customer_address,customer_city,customer_state,customer_pincode,total_actual_price,total_discount_amount,total_price,notes) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param("ssssssssddds",$order_number,$name,$phone,$email,$address,$city,$state,$pincode,$total_actual,$total_discount,$total_payable,$notes);
            $stmt->execute();
            $order_id = $db->insert_id;

            $istmt = $db->prepare("INSERT INTO order_items (order_id,product_id,product_name,sale_type,actual_price,discount_percent,discount_price,quantity,total_price) VALUES (?,?,?,?,?,?,?,?,?)");
            foreach ($cart as $item) {
                $line_total = $item['discount_price'] * $item['qty'];
                $istmt->bind_param("iissdddid",$order_id,$item['product_id'],$item['name'],$item['sale_type'],$item['actual_price'],$item['discount_percent'],$item['discount_price'],$item['qty'],$line_total);
                $istmt->execute();
            }
            $db->commit();
            $_SESSION['cart'] = [];
            $success = true;
        } catch (Exception $ex) {
            $db->rollback();
            $errors[] = 'Order failed: ' . $ex->getMessage();
        }
    }
}
?>
<div class="checkout-section">
    <div class="container">
        <?php if ($success): ?>
        <div class="form-card order-success">
            <div class="success-icon"><i class="fas fa-check-circle"></i></div>
            <h2>Order Placed Successfully! 🎉</h2>
            <p style="color:var(--gray);font-size:1.1rem;">Thank you! We will contact you shortly to confirm your order.</p>
            <div class="order-number-box">
                <p>Your Order Number</p>
                <div class="num"><?php echo htmlspecialchars($order_number); ?></div>
                <p style="font-size:13px;color:var(--gray);margin-top:5px;">Save this number for reference</p>
            </div>
            <div style="background:var(--light);border-radius:10px;padding:20px;margin:20px auto;max-width:400px;text-align:left;">
                <p><strong><i class="fas fa-phone"></i> We'll call you at:</strong> <?php echo htmlspecialchars($_POST['customer_phone']??''); ?></p>
                <p style="margin-top:8px;"><strong><i class="fas fa-money-bill"></i> Total Amount:</strong> <span style="color:var(--primary);font-size:1.2rem;font-weight:700;"><?php echo formatPrice($total_payable); ?></span></p>
            </div>
            <a href="<?php echo $B; ?>/products.php" class="btn btn-red"><i class="fas fa-store"></i> Continue Shopping</a>
        </div>
        <?php else: ?>
        <h1 style="margin-bottom:25px;"><i class="fas fa-credit-card"></i> Checkout</h1>

        <?php if (!$min_order_met): ?>
        <!-- Minimum Order Warning Banner -->
        <div style="background:linear-gradient(135deg,#fff3cd,#ffeaa7);border:2px solid #f39c12;border-radius:12px;padding:18px 24px;margin-bottom:25px;display:flex;align-items:center;gap:15px;">
            <div style="font-size:2rem;">⚠️</div>
            <div>
                <div style="font-weight:700;font-size:1rem;color:#856404;">Minimum Order Amount: ₹<?php echo number_format(MIN_ORDER_AMOUNT,2); ?></div>
                <div style="font-size:14px;color:#78590b;margin-top:4px;">
                    Your current total is <strong><?php echo formatPrice($total_payable); ?></strong>. 
                    Add items worth <strong><?php echo formatPrice($amount_remaining); ?></strong> more to place your order.
                </div>
                <a href="<?php echo $B; ?>/products.php" style="display:inline-block;margin-top:10px;background:#f39c12;color:#fff;padding:7px 18px;border-radius:8px;text-decoration:none;font-size:13px;font-weight:600;">
                    <i class="fas fa-plus-circle"></i> Add More Items
                </a>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
        <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo implode(' &nbsp;|&nbsp; ', array_map('htmlspecialchars',$errors)); ?></div>
        <?php endif; ?>

        <div class="checkout-grid">
            <form method="POST" class="form-card admin-form">
                <h2><i class="fas fa-user"></i> Delivery Details</h2>
                <div class="form-row">
                    <div class="form-group">
                        <label>Full Name <span>*</span></label>
                        <input type="text" name="customer_name" value="<?php echo htmlspecialchars($_POST['customer_name']??''); ?>" placeholder="Your full name" required>
                    </div>
                    <div class="form-group">
                        <label>Phone Number <span>*</span></label>
                        <input type="tel" name="customer_phone" value="<?php echo htmlspecialchars($_POST['customer_phone']??''); ?>" placeholder="10-digit mobile" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="customer_email" value="<?php echo htmlspecialchars($_POST['customer_email']??''); ?>" placeholder="Optional">
                </div>
                <div class="form-group">
                    <label>Delivery Address <span>*</span></label>
                    <textarea name="customer_address" placeholder="House No, Street, Area..." required><?php echo htmlspecialchars($_POST['customer_address']??''); ?></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>City <span>*</span></label>
                        <input type="text" name="customer_city" value="<?php echo htmlspecialchars($_POST['customer_city']??''); ?>" placeholder="City" required>
                    </div>
                    <div class="form-group">
                        <label>State</label>
                        <input type="text" name="customer_state" value="<?php echo htmlspecialchars($_POST['customer_state']??'Tamil Nadu'); ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label>Pincode <span>*</span></label>
                    <input type="text" name="customer_pincode" value="<?php echo htmlspecialchars($_POST['customer_pincode']??''); ?>" placeholder="6-digit pincode" maxlength="6" required>
                </div>
                <div class="form-group">
                    <label>Special Instructions</label>
                    <textarea name="notes" placeholder="Any special notes..."><?php echo htmlspecialchars($_POST['notes']??''); ?></textarea>
                </div>

                <!-- Minimum order note -->
                <div style="background:#fff8e1;border:1px solid #ffe082;border-radius:8px;padding:12px 15px;margin-bottom:16px;font-size:13px;color:#5d4037;display:flex;align-items:center;gap:8px;">
                    <i class="fas fa-info-circle" style="color:#f39c12;font-size:16px;"></i>
                    <span><strong>Note:</strong> Minimum order amount is <strong>₹<?php echo number_format(MIN_ORDER_AMOUNT,2); ?></strong></span>
                </div>

                <?php if ($min_order_met): ?>
                <button type="submit" class="btn btn-red w-100" style="font-size:1.1rem;padding:16px;justify-content:center;">
                    <i class="fas fa-check-circle"></i> Place Order &mdash; <?php echo formatPrice($total_payable); ?>
                </button>
                <?php else: ?>
                <button type="button" disabled
                    style="width:100%;padding:16px;font-size:1.1rem;background:#ccc;color:#888;border:none;border-radius:8px;cursor:not-allowed;display:flex;align-items:center;justify-content:center;gap:8px;">
                    <i class="fas fa-lock"></i> Place Order (Add ₹<?php echo number_format($amount_remaining,2); ?> more to unlock)
                </button>
                <?php endif; ?>
                <p style="text-align:center;margin-top:12px;font-size:13px;color:var(--gray);"><i class="fas fa-shield-alt"></i> Your information is 100% secure</p>
            </form>

            <div>
                <div class="cart-summary">
                    <h3><i class="fas fa-receipt"></i> Order Summary</h3>
                    <?php foreach ($cart as $item): ?>
                    <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:10px;padding-bottom:10px;border-bottom:1px solid #f0f0f0;">
                        <div>
                            <div style="font-weight:600;"><?php echo htmlspecialchars($item['name']); ?></div>
                            <div style="color:var(--gray);">Qty: <?php echo $item['qty']; ?> &times; <?php echo formatPrice($item['discount_price']); ?></div>
                        </div>
                        <div style="font-weight:700;color:var(--primary);"><?php echo formatPrice($item['discount_price'] * $item['qty']); ?></div>
                    </div>
                    <?php endforeach; ?>
                    <div class="summary-row"><span>Subtotal (MRP)</span><span><?php echo formatPrice($total_actual); ?></span></div>
                    <div class="summary-row discount"><span>Total Discount</span><span>&minus; <?php echo formatPrice($total_discount); ?></span></div>
                    <div class="summary-row"><span>Delivery</span><span style="color:var(--accent);font-weight:600;">FREE</span></div>
                    <div class="summary-row total"><span>Total Payable</span><span><?php echo formatPrice($total_payable); ?></span></div>

                    <?php if (!$min_order_met): ?>
                    <!-- Progress bar toward minimum order -->
                    <div style="margin-top:15px;">
                        <div style="display:flex;justify-content:space-between;font-size:12px;color:var(--gray);margin-bottom:6px;">
                            <span>Min. Order Progress</span>
                            <span><?php echo formatPrice($total_payable); ?> / <?php echo formatPrice(MIN_ORDER_AMOUNT); ?></span>
                        </div>
                        <div style="height:8px;background:#f0f0f0;border-radius:20px;overflow:hidden;">
                            <div style="height:100%;width:<?php echo min(100, round(($total_payable / MIN_ORDER_AMOUNT) * 100)); ?>%;background:linear-gradient(90deg,#f39c12,#e67e22);border-radius:20px;transition:width .4s;"></div>
                        </div>
                        <div style="font-size:12px;color:#e67e22;font-weight:600;margin-top:6px;text-align:center;">
                            Add <?php echo formatPrice($amount_remaining); ?> more to unlock ordering
                        </div>
                    </div>
                    <?php else: ?>
                    <div style="background:rgba(39,174,96,0.1);border-radius:8px;padding:12px;margin-top:12px;text-align:center;font-size:14px;color:var(--accent);font-weight:600;">
                        🎉 You save <?php echo formatPrice($total_discount); ?>!
                    </div>
                    <?php endif; ?>
                </div>

                <div style="background:#fff;border-radius:10px;padding:20px;box-shadow:0 4px 20px rgba(0,0,0,0.1);margin-top:20px;">
                    <h4 style="margin-bottom:12px;"><i class="fas fa-info-circle"></i> Payment Info</h4>
                    <p style="font-size:13px;color:var(--gray);">We accept payment on delivery or bank transfer. Our team will call you to confirm payment details.</p>
                    <div style="margin-top:15px;">
                        <p style="font-size:14px;"><i class="fas fa-phone" style="color:var(--primary);"></i> <strong><?php echo SITE_PHONE1; ?></strong></p>
                        <p style="font-size:14px;margin-top:5px;"><i class="fas fa-phone" style="color:var(--primary);"></i> <strong><?php echo SITE_PHONE2; ?></strong></p>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>
