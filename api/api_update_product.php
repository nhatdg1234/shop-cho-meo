<?php
session_start();
require_once "../db_conn.php";

header('Content-Type: application/json; charset=utf-8');

// 1. Kiểm tra phương thức và quyền
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Chỉ hỗ trợ phương thức POST."]);
    exit();
}
if (!isset($_SESSION['id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    echo json_encode(["status" => "error", "message" => "Không có quyền truy cập!"]);
    exit();
}

// 2. Nhận dữ liệu (có chứa file nên dùng $_POST thay vì php://input)
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$name = trim($_POST['product_name'] ?? '');
$price = trim($_POST['price'] ?? '');
$category = trim($_POST['category'] ?? '');
$description = trim($_POST['description'] ?? '');
$stock = trim($_POST['stock_quantity'] ?? '1');

if ($id <= 0 || $name === '' || $price === '' || $category === '') {
    echo json_encode(["status" => "error", "message" => "Vui lòng nhập đầy đủ Tên, Giá và Phân loại."]);
    exit();
}

if (!is_numeric($price) || $price < 0 || !is_numeric($stock) || $stock < 0) {
    echo json_encode(["status" => "error", "message" => "Giá hoặc tồn kho không hợp lệ!"]);
    exit();
}

try {
    $currentImageUrl = '';
    // Lấy URL ảnh hiện tại để xử lý nếu không upload ảnh mới
    $stmt_img = $conn->prepare("SELECT image_url FROM products WHERE id = ?");
    $stmt_img->execute([$id]);
    $product_data = $stmt_img->fetch(PDO::FETCH_ASSOC);
    if ($product_data) {
        $currentImageUrl = $product_data['image_url'];
    }

    $newImageUrl = $currentImageUrl; // Mặc định giữ ảnh cũ

    // 3. Kiểm tra xem người dùng có upload ảnh mới không
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
            $newImageUrl = $img_name; // Cập nhật ảnh mới
            // Tùy chọn: Xóa ảnh cũ nếu có
            if (!empty($currentImageUrl) && file_exists($target_dir . $currentImageUrl)) {
                @unlink($target_dir . $currentImageUrl);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Lỗi khi lưu file ảnh mới!"]);
            exit();
        }
    }

    // Cập nhật CÓ hoặc KHÔNG thay đổi ảnh
    $stmt = $conn->prepare("UPDATE products SET product_name=?, description=?, price=?, image_url=?, stock_quantity=?, category=? WHERE id=?");
    if ($stmt->execute([$name, $description, $price, $newImageUrl, $stock, $category, $id])) {
        echo json_encode(["status" => "success", "message" => "Cập nhật sản phẩm thành công!"]);
        exit();
    } else {
        echo json_encode(["status" => "error", "message" => "Không thể cập nhật sản phẩm."]);
        exit();
    }

} catch (PDOException $e) {
    error_log("Update Product Error: " . $e->getMessage());
    echo json_encode(["status" => "error", "message" => "Lỗi máy chủ khi cập nhật sản phẩm!"]);
    exit();
}
?>