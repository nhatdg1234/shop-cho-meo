<?php
require_once "db_conn.php";

try {
    $sql = "ALTER TABLE products ADD COLUMN is_spotlight TINYINT(1) DEFAULT 0";
    $conn->exec($sql);
    echo "Thêm cột is_spotlight thành công!";
} catch (PDOException $e) {
    // Nếu cột đã tồn tại thì không báo lỗi nghiêm trọng
    if ($e->getCode() == '42S21') {
        echo "Cột is_spotlight đã tồn tại.";
    } else {
        echo "Lỗi: " . $e->getMessage();
    }
}
?>