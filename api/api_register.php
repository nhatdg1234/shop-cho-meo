<?php
session_start();
// Lùi 1 cấp để gọi đúng file db_conn.php ở thư mục gốc
require_once "../db_conn.php"; 

// Khai báo trả về chuẩn JSON
header('Content-Type: application/json; charset=utf-8');

// Nhận dữ liệu JSON từ phía Frontend Javascript gửi lên
$data = json_decode(file_get_contents("php://input"), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Làm sạch dữ liệu
    $name = htmlspecialchars(trim($data['name'] ?? ''));
    $email = htmlspecialchars(trim($data['email'] ?? ''));
    $uname = htmlspecialchars(trim($data['uname'] ?? ''));
    $pass = trim($data['password'] ?? '');
    $re_pass = trim($data['re_password'] ?? '');

    // 1. Kiểm tra Validate cơ bản
    if (empty($name)) {
        echo json_encode(['status' => 'error', 'message' => 'Vui lòng nhập họ tên']);
        exit();
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['status' => 'error', 'message' => 'Email không hợp lệ hoặc bị trống']);
        exit();
    }
    if (empty($uname)) {
        echo json_encode(['status' => 'error', 'message' => 'Vui lòng nhập tên đăng nhập (Username)']);
        exit();
    }
    if (empty($pass) || strlen($pass) < 6) {
        echo json_encode(['status' => 'error', 'message' => 'Mật khẩu phải từ 6 ký tự trở lên']);
        exit();
    }
    if ($pass !== $re_pass) {
        echo json_encode(['status' => 'error', 'message' => 'Mật khẩu xác nhận không khớp!']);
        exit();
    }

    try {
        // 2. Kiểm tra xem Username hoặc Email đã tồn tại chưa
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$uname, $email]);

        if ($stmt->fetch()) {
            echo json_encode(['status' => 'error', 'message' => 'Tên đăng nhập hoặc Email này đã được sử dụng!']);
            exit();
        } else {
            // 3. Tiến hành mã hóa mật khẩu và lưu vào CSDL
            $hashed_password = password_hash($pass, PASSWORD_BCRYPT);
            
            // role mặc định là 'customer'
            $sql_insert = "INSERT INTO users(customer_name, email, username, password, role) VALUES(?, ?, ?, ?, 'customer')";
            $stmt_insert = $conn->prepare($sql_insert);
            $result = $stmt_insert->execute([$name, $email, $uname, $hashed_password]);

            if ($result) {
                echo json_encode(['status' => 'success', 'message' => 'Đăng ký thành công! Đang chuyển hướng...']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Đã có lỗi xảy ra trong quá trình lưu dữ liệu.']);
            }
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Lỗi kết nối CSDL: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Phương thức yêu cầu không hợp lệ.']);
}