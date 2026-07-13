<?php
require_once '../../db_conn.php';

try {
    $sql = "
    -- 1. Bảng lưu thông tin chung của đơn hàng
    CREATE TABLE IF NOT EXISTS `orders` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `order_code` varchar(50) NOT NULL,
      `customer_name` varchar(100) NOT NULL,
      `customer_phone` varchar(20) NOT NULL,
      `shipping_address` text NOT NULL,
      `total_amount` decimal(10,2) NOT NULL,
      `payment_method` varchar(50) DEFAULT 'Chuyển khoản (VietQR)',
      `order_status` varchar(50) DEFAULT 'Chờ xác nhận',
      `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    -- 2. Bảng lưu chi tiết từng sản phẩm trong đơn hàng
    CREATE TABLE IF NOT EXISTS `order_details` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `order_id` int(11) NOT NULL,
      `product_id` int(11) NOT NULL,
      `product_name` varchar(255) NOT NULL,
      `price` decimal(10,2) NOT NULL,
      `quantity` int(11) NOT NULL,
      PRIMARY KEY (`id`),
      FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    $conn->exec($sql);
    echo "Tạo bảng thành công!";
} catch (PDOException $e) {
    echo "Lỗi: " . $e->getMessage();
}
?>