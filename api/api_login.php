<?php
session_start();

// 1. Chỉ cho phép method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(["status" => "error", "message" => "Phương thức không được hỗ trợ. Vui lòng dùng POST."]);
    exit(); // Luôn dùng exit() sau khi trả về JSON
}

// 2. Lùi 1 cấp để gọi đúng file db_conn.php ở thư mục gốc
require_once "../db_conn.php";

// Đặt header báo dữ liệu trả về là JSON
header('Content-Type: application/json; charset=utf-8');

// Lấy dữ liệu (Hỗ trợ cả JSON body và Form-data)
$input = json_decode(file_get_contents('php://input'), true);
$uname = isset($input['uname']) ? trim($input['uname']) : (isset($_POST['uname']) ? trim($_POST['uname']) : '');
$pass = isset($input['password']) ? trim($input['password']) : (isset($_POST['password']) ? trim($_POST['password']) : '');

if (empty($uname) || empty($pass)) {
    echo json_encode(["status" => "error", "message" => "Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu!"]);
    exit();
}

try {
    $stmt = $conn->prepare("SELECT id, username, customer_name, password, role FROM users WHERE username = ?");
    $stmt->execute([$uname]);
    $row = $stmt->fetch();

    if ($row && password_verify($pass, $row['password'])) {
        // Lưu Session cho Web hiện tại
        $_SESSION['user_name'] = $row['username'];
        $_SESSION['name'] = $row['customer_name'];
        $_SESSION['id'] = $row['id'];
        $_SESSION['role'] = $row['role'];

        // 3. Đã chỉnh sửa: Redirect về homead.php theo đúng project hiện tại
        $redirectUrl = ($row['role'] === 'admin') ? 'admin_dashboard.php' : './shop/user/home.php';
        
        echo json_encode([
            "status" => "success", 
            "message" => "Đăng nhập thành công!", 
            "redirect" => $redirectUrl,
            "user" => ["username" => $row['username'], "role" => $row['role']]
        ]);
        exit();
    } else {
        echo json_encode(["status" => "error", "message" => "Tên đăng nhập hoặc mật khẩu không chính xác!"]);
        exit();
    }
} catch (PDOException $e) {
    // 2. CHE GIẤU LỖI SERVER TRÁNH LỘ THÔNG TIN NHẠY CẢM
    // Ghi log lỗi vào hệ thống (nếu cần cho admin xem)
    error_log("API Login Error: " . $e->getMessage()); 
    
    // Trả về câu thông báo chung chung cho Client
    echo json_encode(["status" => "error", "message" => "Lỗi hệ thống, vui lòng thử lại sau!"]);
    exit();
}
?>