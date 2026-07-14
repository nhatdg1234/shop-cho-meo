<?php
// Nhúng kết nối CSDL của bạn
require_once 'db_conn.php'; 
header('Content-Type: application/json; charset=utf-8');

if (isset($_GET['order_id'])) {
    $order_id = intval($_GET['order_id']);
    
    try {
        // Truy vấn lấy các món hàng thuộc về order_id này
        $sql = "SELECT product_name, price, quantity FROM order_details WHERE order_id = :order_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $details = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['status' => 'success', 'data' => $details]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Lỗi CSDL: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Thiếu ID đơn hàng']);
}