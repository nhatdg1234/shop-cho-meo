<?php
session_start();
require_once "../db_conn.php";

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Chỉ hỗ trợ phương thức POST."]);
    exit();
}

if (!isset($_SESSION['id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    echo json_encode(["status" => "error", "message" => "Không có quyền truy cập!"]);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$id = isset($input['id']) ? intval($input['id']) : 0;

if ($id <= 0) {
    echo json_encode(["status" => "error", "message" => "ID sản phẩm không hợp lệ!"]);
    exit();
}

try {
    $stmt = $conn->prepare("SELECT image_url FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        echo json_encode(["status" => "error", "message" => "Không tìm thấy sản phẩm cần xóa!"]);
        exit();
    }

    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    if ($stmt->execute([$id])) {
        $imagePath = "../shop/anh/" . $product['image_url'];
        if (!empty($product['image_url']) && file_exists($imagePath)) {
            @unlink($imagePath);
        }

        echo json_encode(["status" => "success", "message" => "Đã xóa sản phẩm thành công!"]);
        exit();
    }

    echo json_encode(["status" => "error", "message" => "Không thể xóa sản phẩm."]);
    exit();
} catch (PDOException $e) {
    error_log("Delete Product Error: " . $e->getMessage());
    echo json_encode(["status" => "error", "message" => "Lỗi hệ thống khi xóa!"]);
    exit();
}
?>