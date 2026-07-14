<?php
// 1. CẤU HÌNH KẾT NỐI DATABASE (Đồng nhất với db_conn.php)
$sname = "127.0.0.1";
$username = "root";
$password = "";
$db_name = "petshop_db";
$port = 3308;

try {
    $conn = new PDO(
        "mysql:host={$sname};port={$port};dbname={$db_name};charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die("Kết nối thất bại: " . $e->getMessage());
}

// 2. XỬ LÝ CẬP NHẬT TRẠNG THÁI ĐƠN HÀNG
$update_success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $order_id = intval($_POST['order_id']);
    $new_status = $_POST['status'];
    
    $stmt = $conn->prepare("UPDATE orders SET status = :status WHERE id = :id");
    if ($stmt->execute(['status' => $new_status, 'id' => $order_id])) {
        $update_success = true;
    }
}

// 3. THỐNG KÊ
$stats = $conn->query("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'Chờ xử lý' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'Đang giao' THEN 1 ELSE 0 END) as shipping,
    SUM(CASE WHEN status = 'Đã giao' THEN 1 ELSE 0 END) as completed
    FROM orders")->fetch();

// 4. LỌC & TÌM KIẾM
$where = [];
$params = [];
if (!empty($_GET['search'])) {
    $where[] = "(id = :search OR fullname LIKE :search_like)";
    $params['search'] = $_GET['search'];
    $params['search_like'] = '%' . $_GET['search'] . '%';
}
if (!empty($_GET['status_filter'])) {
    $where[] = "status = :status";
    $params['status'] = $_GET['status_filter'];
}

$sql = "SELECT * FROM orders" . ($where ? " WHERE " . implode(" AND ", $where) : "") . " ORDER BY id DESC";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();
?>

<!DOCTYPE HTML>
<html>
    <head>
        <meta charset="utf-8">
        <title>VKU Pet Shop - Admin Panel</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css">
    </head>
    <body class="bg-gray-50 p-8">
        <h1 class="text-2xl font-bold mb-6">Quản trị đơn hàng</h1>
        
        <?php if ($update_success): ?>
            <div class="bg-green-100 p-4 mb-4 rounded">Cập nhật thành công!</div>
        <?php endif; ?>

        <div class="grid grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-4 rounded shadow">Tổng: <?php echo $stats['total']; ?></div>
            <div class="bg-white p-4 rounded shadow">Chờ: <?php echo $stats['pending']; ?></div>
            <div class="bg-white p-4 rounded shadow">Giao: <?php echo $stats['shipping']; ?></div>
            <div class="bg-white p-4 rounded shadow">Hoàn thành: <?php echo $stats['completed']; ?></div>
        </div>

        <table class="w-full bg-white rounded shadow">
            <tr class="border-b">
                <th class="p-4">ID</th>
                <th class="p-4">Khách hàng</th>
                <th class="p-4">Tổng tiền</th>
                <th class="p-4">Trạng thái</th>
                <th class="p-4">Hành động</th>
            </tr>
            <?php foreach ($orders as $row): ?>
            <tr class="border-b">
                <td class="p-4">#00<?php echo $row['id']; ?></td>
                <td class="p-4"><?php echo htmlspecialchars($row['fullname'] ?? 'Khách'); ?></td>
                <td class="p-4"><?php echo number_format($row['total_amount'] ?? 0); ?>đ</td>
                <td class="p-4"><?php echo $row['status']; ?></td>
                <td class="p-4">
                    <form method="POST">
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                        <select name="status" onchange="this.form.submit()">
                            <option value="Chờ xử lý" <?php echo $row['status'] == 'Chờ xử lý' ? 'selected' : ''; ?>>Chờ xử lý</option>
                            <option value="Đã xác nhận" <?php echo $row['status'] == 'Đã xác nhận' ? 'selected' : ''; ?>>Đã xác nhận</option>
                            <option value="Đang giao" <?php echo $row['status'] == 'Đang giao' ? 'selected' : ''; ?>>Đang giao</option>
                            <option value="Đã giao" <?php echo $row['status'] == 'Đã giao' ? 'selected' : ''; ?>>Đã giao</option>
                        </select>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </body>
</html>