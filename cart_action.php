<?php
require_once 'includes/config.php';
header('Content-Type: application/json');
$db = getDB();
$action = $_POST['action'] ?? '';
if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

function cartCount() { return array_sum(array_column($_SESSION['cart'], 'qty')); }

if ($action === 'add') {
    $pid = (int)($_POST['product_id'] ?? 0);
    $qty = max(1, (int)($_POST['qty'] ?? 1));
    $stmt = $db->prepare("SELECT * FROM products WHERE id=? AND is_active=1");
    $stmt->bind_param("i", $pid);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    if (!$product) { echo json_encode(['success'=>false,'message'=>'Product not found']); exit; }
    $disc_price = calcDiscountPrice($product['actual_price'], $product['discount_percent']);
    if (isset($_SESSION['cart'][$pid])) {
        $_SESSION['cart'][$pid]['qty'] += $qty;
    } else {
        $_SESSION['cart'][$pid] = [
            'product_id'=>$pid,'name'=>$product['name'],'sale_type'=>$product['sale_type'],
            'actual_price'=>$product['actual_price'],'discount_percent'=>$product['discount_percent'],
            'discount_price'=>$disc_price,'qty'=>$qty,'image'=>$product['image'],
        ];
    }
    echo json_encode(['success'=>true,'cart_count'=>cartCount()]);
    exit;
}
if ($action === 'remove') {
    unset($_SESSION['cart'][(int)($_POST['product_id']??0)]);
    echo json_encode(['success'=>true,'cart_count'=>cartCount()]);
    exit;
}
if ($action === 'update') {
    $pid = (int)($_POST['product_id']??0);
    $qty = max(1,(int)($_POST['qty']??1));
    if (isset($_SESSION['cart'][$pid])) $_SESSION['cart'][$pid]['qty'] = $qty;
    echo json_encode(['success'=>true,'cart_count'=>cartCount()]);
    exit;
}
if ($action === 'clear') {
    $_SESSION['cart'] = [];
    echo json_encode(['success'=>true,'cart_count'=>0]);
    exit;
}
echo json_encode(['success'=>false,'message'=>'Unknown action']);
