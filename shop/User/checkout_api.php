<?php
require_once '../../db_conn.php';
header('Content-Type: application/json');

// Đọc dữ liệu JSON gửi từ Frontend
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['cart']) || !isset($data['shipping'])) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
    exit;
}

$shipping = $data['shipping'];
$cart = $data['cart'];
$order_code = 'VKU-' . strtoupper(substr(md5(uniqid()), 0, 6)); // Tạo mã đơn ngẫu nhiên
$total_amount = 0;

// Tính tổng tiền ở Backend cho an toàn
foreach ($cart as $item) {
    // Đảm bảo price và qty là số
    $price = is_numeric($item['price']) ? (float)$item['price'] : 0;
    $qty = is_numeric($item['qty']) ? (int)$item['qty'] : 0;
    $total_amount += $price * $qty;
}

// Gộp địa chỉ
$full_address = $shipping['address'] . ', ' . $shipping['ward'] . ', ' . $shipping['district'] . ', ' . $shipping['province'];

try {
    $conn->beginTransaction();

    // 1. Lưu vào bảng orders
    $stmt = $conn->prepare("INSERT INTO orders (order_code, customer_name, customer_phone, shipping_address, total_amount, payment_method) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $order_code, 
        $shipping['fullName'], 
        $shipping['phone'], 
        $full_address, 
        $total_amount, 
        'Chuyển khoản VietQR'
    ]);
    
    $order_id = $conn->lastInsertId();

    // 2. Lưu vào bảng order_details
    $stmtDetail = $conn->prepare("INSERT INTO order_details (order_id, product_id, product_name, price, quantity) VALUES (?, ?, ?, ?, ?)");
    foreach ($cart as $id => $item) {
        // Đảm bảo price và qty là số
        $price = is_numeric($item['price']) ? (float)$item['price'] : 0;
        $qty = is_numeric($item['qty']) ? (int)$item['qty'] : 0;
        
        $stmtDetail->execute([
            $order_id,
            $id, // product_id
            $item['name'],
            $price,
            $qty
        ]);
    }

    $conn->commit();
    echo json_encode(['success' => true, 'order_code' => $order_code]);

} catch (PDOException $e) {
    $conn->rollBack();
    // Log lỗi chi tiết hơn ở môi trường production
    error_log("Checkout API Error: " . $e->getMessage()); 
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: Vui lòng thử lại sau.']);
}
?>