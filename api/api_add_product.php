<?php
session_start();
require_once "../db_conn.php";

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Chỉ hỗ trợ POST."]);
    exit();
}

if (!isset($_SESSION['id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    echo json_encode(["status" => "error", "message" => "Không có quyền truy cập!"]);
    exit();
}

$name = trim($_POST['product_name'] ?? '');
$price = trim($_POST['price'] ?? '');
$category = trim($_POST['category'] ?? '');
$description = trim($_POST['description'] ?? '');
$stock = trim($_POST['stock_quantity'] ?? '1');

if ($name === '' || $price === '' || $category === '') {
    echo json_encode(["status" => "error", "message" => "Vui lòng nhập đầy đủ Tên, Giá và Phân loại."]);
    exit();
}

if (!is_numeric($price) || $price < 0 || !is_numeric($stock) || $stock < 0) {
    echo json_encode(["status" => "error", "message" => "Giá hoặc tồn kho không hợp lệ!"]);
    exit();
}

$image_url = "";
if (isset($_FILES['image_url']) && $_FILES['image_url']['error'] === UPLOAD_ERR_OK) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $fileType = mime_content_type($_FILES['image_url']['tmp_name']);

    if (!in_array($fileType, $allowedTypes, true)) {
        echo json_encode(["status" => "error", "message" => "Chỉ cho phép upload ảnh JPG, PNG, GIF, WEBP."]);
        exit();
    }

    $ext = pathinfo($_FILES['image_url']['name'], PATHINFO_EXTENSION);
    $img_name = time() . '_' . bin2hex(random_bytes(4)) . ($ext ? '.' . strtolower($ext) : '');
    $target_dir = "../shop/anh/";

    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    if (move_uploaded_file($_FILES['image_url']['tmp_name'], $target_dir . $img_name)) {
        $image_url = $img_name;
    } else {
        echo json_encode(["status" => "error", "message" => "Lỗi khi lưu file ảnh!"]);
        exit();
    }
} else {
    echo json_encode(["status" => "error", "message" => "Vui lòng tải lên ảnh sản phẩm!"]);
    exit();
}

try {
    $stmt = $conn->prepare("INSERT INTO products (product_name, description, price, image_url, stock_quantity, category) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$name, $description, $price, $image_url, $stock, $category])) {
        echo json_encode(["status" => "success", "message" => "Thêm thú cưng/sản phẩm mới thành công!"]);
        exit();
    }

    echo json_encode(["status" => "error", "message" => "Không thể thêm dữ liệu."]);
    exit();
} catch (PDOException $e) {
    error_log("Add Product Error: " . $e->getMessage());
    echo json_encode(["status" => "error", "message" => "Lỗi máy chủ khi thêm sản phẩm!"]);
    exit();
}
?>