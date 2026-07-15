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

// Kiểm tra xem sản phẩm này có được đặt làm Offer đặc quyền hay không
// Kích hoạt khi Category là "offer" HOẶC Admin tích chọn checkbox "is_spotlight" từ Form gửi lên
$is_spotlight = ($category === 'offer' || (isset($_POST['is_spotlight']) && $_POST['is_spotlight'] == '1'));

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

    $ext = strtolower(pathinfo($_FILES['image_url']['name'], PATHINFO_EXTENSION));
    $target_dir = "../shop/User/anh/";

    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    // Thiết lập tên file ảnh mới
    if ($is_spotlight) {
        // Với sản phẩm Offer đặc quyền, ÉP BUỘC lưu đuôi file .png để giữ được độ trong suốt (Transparent background)
        $img_name = 'spotlight_' . time() . '_' . bin2hex(random_bytes(4)) . '.png';
    } else {
        $img_name = time() . '_' . bin2hex(random_bytes(4)) . ($ext ? '.' . $ext : '');
    }

    $target_file = $target_dir . $img_name;
    $tmp_file = $_FILES['image_url']['tmp_name'];

    if ($is_spotlight) {
        // ==========================================
        // TIẾN TRÌNH 1: GỌI AI BẰNG EXEC (ĐÃ ĐỒNG BỘ MODULE PIP CHUẨN)
        // ==========================================
        $ai_success = false;

       // 1. Trỏ về lại file python gốc ở ổ D
        $python_cmd = "D:\\python\\python.exe";

        // 2. Chạy trực tiếp bằng mã Python nhúng, ép buộc chạy bằng CPU (CPUExecutionProvider) để tránh lỗi thiếu DLL của card đồ họa
        $python_code = "from rembg import remove, new_session; from PIL import Image; session = new_session('u2net', providers=['CPUExecutionProvider']); input_img = Image.open(r" . var_export($tmp_file, true) . "); output_img = remove(input_img, session=session); output_img.save(r" . var_export($target_file, true) . ")";
        $command = $python_cmd . " -c " . escapeshellarg($python_code) . " 2>&1";

        // 3. Thực thi lệnh hệ thống
        $ket_qua = shell_exec($command);
        file_put_contents('log_ai.txt', "--- LOG CHẠY AI TÁCH NỀN BẰNG PYTHON CODE ---\n" . $ket_qua);

        // Kiểm tra xem file ảnh PNG trong suốt đã được AI tạo ra thành công chưa
        if (file_exists($target_file) && filesize($target_file) > 0) {
            $image_url = $img_name;
            $ai_success = true;
        }

        // ==========================================
        // TIẾN TRÌNH 2 (FALLBACK): KHI AI GẶP SỰ CỐ
        // ==========================================

        // ==========================================
        // TIẾN TRÌNH 2 (FALLBACK): KHI AI GẶP SỰ CỐ
        // Tự động chuyển đổi file JPG gốc thành file PNG
        // ==========================================
        if (!$ai_success) {
            // Kiểm tra xem máy chủ có bật thư viện GD và ảnh tải lên có định dạng JPG/JPEG không
            if (($ext === 'jpg' || $ext === 'jpeg') && extension_loaded('gd')) {
                $image = @imagecreatefromjpeg($tmp_file);
                if ($image) {
                    // Cấu hình lưu kênh trong suốt (Alpha channel) cho tệp PNG đầu ra
                    imagealphablending($image, false);
                    imagesavealpha($image, true);
                    
                    if (@imagepng($image, $target_file)) {
                        $image_url = $img_name;
                    }
                    imagedestroy($image);
                }
            }

            // Nếu không thể chuyển đổi hoặc định dạng ban đầu đã là PNG, copy thô và lưu thành file .png
            if (empty($image_url)) {
                if (@move_uploaded_file($tmp_file, $target_file)) {
                    $image_url = $img_name;
                } else {
                    echo json_encode(["status" => "error", "message" => "Lỗi lưu file ảnh Spotlight khi fallback!"]);
                    exit();
                }
            }
            
            // Ghi nhật ký máy chủ để lập trình viên biết AI đang không hoạt động
            error_log("Rembg AI Background Removal failed. Fallback to normal PNG conversion applied.");
        }
    } else {
        // ==========================================
        // TIẾN TRÌNH 3: LƯU SẢN PHẨM THƯỜNG (Không dùng AI)
        // ==========================================
        if (@move_uploaded_file($tmp_file, $target_file)) {
            $image_url = $img_name;
        } else {
            echo json_encode(["status" => "error", "message" => "Lỗi khi lưu file ảnh thường!"]);
            exit();
        }
    }
} else {
    echo json_encode(["status" => "error", "message" => "Vui lòng tải lên ảnh sản phẩm!"]);
    exit();
}

try {
    // Sử dụng Database Transaction để đảm bảo tính nhất quán dữ liệu
    $conn->beginTransaction();

    if ($is_spotlight) {
        // Tắt toàn bộ thuộc tính Spotlight (is_spotlight = 0) của các sản phẩm cũ
        $conn->query("UPDATE products SET is_spotlight = 0");
    }

    // Chèn sản phẩm mới vào Database kèm trường is_spotlight
    $stmt = $conn->prepare("INSERT INTO products (product_name, description, price, image_url, stock_quantity, category, is_spotlight) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$name, $description, $price, $image_url, $stock, $category, $is_spotlight ? 1 : 0])) {
        $conn->commit();
        
        $msg = "Thêm thú cưng/sản phẩm mới thành công!";
        if ($is_spotlight) {
            $msg .= " Bé cưng này đã được thiết lập độc quyền làm Offer nổi bật trên trang chủ và xử lý AI tách nền thành công.";
        }
        echo json_encode(["status" => "success", "message" => $msg]);
        exit();
    }

    $conn->rollBack();
    echo json_encode(["status" => "error", "message" => "Không thể thêm dữ liệu vào Database."]);
    exit();
} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log("Add Product Error: " . $e->getMessage());
    echo json_encode(["status" => "error", "message" => "Lỗi máy chủ khi lưu sản phẩm!"]);
    exit();
}
?>