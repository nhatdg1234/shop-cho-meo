<?php
// Nhúng kết nối CSDL giống hệt cấu trúc của bạn
require_once '../../db_conn.php'; 

// ÉP PDO PHẢI BÁO LỖI NẾU SQL SAI (Dòng quan trọng vừa thêm)
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Đặt header trả về kiểu JSON
header('Content-Type: application/json');

// Lấy dữ liệu thô (JSON) từ JavaScript gửi lên
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data) {
    echo json_encode(['status' => 'error', 'message' => 'Không nhận được dữ liệu']);
    exit;
}

try {
    // Bắt đầu quá trình lưu (Transaction) để đảm bảo nếu lỗi thì hủy toàn bộ
    $conn->beginTransaction();

    // 1. LƯU VÀO BẢNG `orders`
    $customer = $data['customer'];
    
    // --- TỰ ĐỘNG TẠO MÃ ĐƠN HÀNG (ORDER CODE) ---
    // Tạo chuỗi ngẫu nhiên gồm 6 ký tự (Ví dụ: 5DD1BE) giống các đơn hàng cũ của bạn
    $random_string = strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));
    $order_code = "VKU-" . $random_string;
    // --------------------------------------------

    // Thêm cột order_code vào câu lệnh SQL
    $sql_order = "INSERT INTO orders (order_code, customer_name, customer_phone, shipping_address, payment_method, total_amount, order_status, created_at) 
                  VALUES (:order_code, :fullname, :phone, :address, :payment_method, :total, :order_status, NOW())";
    
    $stmt = $conn->prepare($sql_order);
    
    // Thêm :order_code và trạng thái mặc định vào mảng thực thi
    $stmt->execute([
        ':order_code' => $order_code,
        ':fullname' => $customer['fullname'],
        ':phone' => $customer['phone'],
        ':address' => $customer['address'],
        ':payment_method' => $data['payment_method'],
        ':total' => $data['total'],
        ':order_status' => 'Chờ xác nhận' // Đồng bộ trạng thái giống ảnh chụp database của bạn
    ]);

    // Lấy ID đơn hàng vừa tạo xong
    $order_id = $conn->lastInsertId();

    // 2. LƯU VÀO BẢNG `order_details` (Đã thêm chữ 's' cho khớp Database)
    $cartItems = $data['cart'];
    
    // Thêm cột product_name vào câu lệnh SQL
    $sql_detail = "INSERT INTO order_details (order_id, product_id, product_name, quantity, price) 
                   VALUES (:order_id, :product_id, :product_name, :quantity, :price)";
    $stmt_detail = $conn->prepare($sql_detail);

    // Vòng lặp lưu từng món hàng trong giỏ
    foreach ($cartItems as $product_id => $item) {
        $stmt_detail->execute([
            ':order_id' => $order_id,
            ':product_id' => $product_id,
            ':product_name' => $item['name'], // Lấy tên sản phẩm từ dữ liệu giỏ hàng
            ':quantity' => $item['qty'],
            ':price' => $item['price']
        ]);
    }

    // Xác nhận lưu vĩnh viễn vào CSDL
    $conn->commit();

    // Trả về thông báo thành công cho JavaScript
    echo json_encode(['status' => 'success', 'order_id' => $order_id, 'order_code' => $order_code]);

} catch (PDOException $e) {
    // Nếu có lỗi, hủy toàn bộ thay đổi
    $conn->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Lỗi Database: ' . $e->getMessage()]);
}
?>