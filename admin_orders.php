<?php
// Bật hiển thị lỗi để dễ dàng kiểm soát
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 1. NHÚNG KẾT NỐI PDO ĐỒNG NHẤT VỚI DỰ ÁN
require_once 'db_conn.php'; 

// Kiểm tra và bảo đảm biến $conn kết nối PDO hoạt động tốt
if (!isset($conn) || $conn === null) {
    die("Lỗi nghiêm trọng: Biến kết nối CSDL \$conn không tồn tại hoặc chưa được khởi tạo từ db_conn.php!");
}

$update_success = false;
$error_msg = "";

// 2. XỬ LÝ CẬP NHẬT TRẠNG THÁI ĐƠN HÀNG (SỬ DỤNG order_status VÀ id)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $order_id = intval($_POST['order_id']);
    $new_status = $_POST['status'];
    
    try {
        $sql_update = "UPDATE orders SET order_status = :order_status WHERE id = :id";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bindParam(':order_status', $new_status, PDO::PARAM_STR);
        $stmt_update->bindParam(':id', $order_id, PDO::PARAM_INT);
        
        if ($stmt_update->execute()) {
            $update_success = true;
        }
    } catch (PDOException $e) {
        $error_msg = "Lỗi cập nhật: " . $e->getMessage();
    }
}

// 3. TÍNH TOÁN CÁC CHỈ SỐ THỐNG KÊ (SỬ DỤNG order_status)
try {
    $total_orders = $conn->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    // Đếm cả 'Chờ xác nhận' (mặc định CSDL) và 'Chờ xử lý'
    $pending_orders = $conn->query("SELECT COUNT(*) FROM orders WHERE order_status = 'Chờ xử lý' OR order_status = 'Chờ xác nhận'")->fetchColumn();
    $shipping_orders = $conn->query("SELECT COUNT(*) FROM orders WHERE order_status = 'Đang giao'")->fetchColumn();
    $completed_orders = $conn->query("SELECT COUNT(*) FROM orders WHERE order_status = 'Đã giao'")->fetchColumn();
} catch (PDOException $e) {
    $total_orders = $pending_orders = $shipping_orders = $completed_orders = 0;
    $error_msg = "Lỗi tính toán thống kê: " . $e->getMessage();
}

// 4. XỬ LÝ LỌC & TÌM KIẾM ĐƠN HÀNG
$search_query = "";
$filter_status = "";
$where_clauses = [];
$params = [];

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = trim($_GET['search']);
    $search_query = $search; // Giữ nguyên để hiển thị lại trên ô input
    
    // Xử lý thông minh: Loại bỏ dấu # ở đầu nếu Admin có gõ vào để tìm kiếm chính xác hơn
    $clean_search = ltrim($search, '#');

    // Bổ sung thêm điều kiện OR order_code LIKE :search_code
    $where_clauses[] = "(id = :search_id OR customer_name LIKE :search_name OR order_code LIKE :search_code)";
    
    $params[':search_id'] = intval($clean_search); // Tìm theo ID (số)
    $params[':search_name'] = "%$search%";         // Tìm theo tên khách hàng
    $params[':search_code'] = "%$clean_search%";   // Tìm theo Mã đơn hàng (chữ & số)
}

if (isset($_GET['status_filter']) && !empty($_GET['status_filter'])) {
    $status_filter = $_GET['status_filter'];
    
    // Gộp "Chờ xử lý" và "Chờ xác nhận" làm một
    if ($status_filter === 'Chờ xử lý' || $status_filter === 'Chờ xác nhận') {
        $filter_status = 'Chờ xử lý'; // Chuẩn hóa lại biến hiển thị cho dropdown
        $where_clauses[] = "(order_status = 'Chờ xử lý' OR order_status = 'Chờ xác nhận')";
    } else {
        $filter_status = $status_filter;
        $where_clauses[] = "order_status = :status_filter";
        $params[':status_filter'] = $status_filter;
    }
}

$where_sql = "";
if (count($where_clauses) > 0) {
    $where_sql = "WHERE " . implode(" AND ", $where_clauses);
}

// Lấy danh sách đơn hàng đổ vào bảng
try {
    $sql_orders = "SELECT * FROM orders $where_sql ORDER BY id DESC";
    $stmt_orders = $conn->prepare($sql_orders);
    $stmt_orders->execute($params);
    $orders_list = $stmt_orders->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $orders_list = [];
    $error_msg = "Lỗi tải danh sách: " . $e->getMessage();
}
?>

<!DOCTYPE HTML>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>VKU Pet Shop - Hệ thống Quản trị Đơn hàng</title>
        
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
        
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.2.1/css/fontawesome.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.2.1/css/solid.min.css">
        <script src="https://cdn.tailwindcss.com"></script>
        <style>
            body { font-family: 'Be Vietnam Pro', sans-serif !important; background-color: #f3f4f6 !important; }
        </style>
    </head>
   
<body class="bg-gray-50 min-h-screen">
    <header class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 py-4 sm:px-6 lg:px-8 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <img src="./shop/anh/anh_logo.png" width="100px" alt="VKU Logo">
                <span class="h-6 w-px bg-gray-300"></span>
                <h1 class="text-lg font-bold text-gray-800">Trang Quản Trị Hệ Thống (Admin Panel)</h1>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-sm text-gray-500">Xin chào, <strong class="text-orange-500">Quản lý viên</strong></span>
                <a href="home.php" class="text-xs text-gray-400 hover:text-gray-600 border border-gray-200 rounded-lg px-3 py-1.5 transition-all">Quay lại Website</a>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <?php if ($update_success): ?>
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl flex items-center gap-3 shadow-sm">
                <i class="fa-solid fa-circle-check text-xl text-emerald-500"></i>
                <div>
                    <strong class="font-bold">Thành công!</strong>
                    <span class="text-sm block sm:inline">Trạng thái đơn hàng đã được đồng bộ đến trang theo dõi của khách.</span>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($error_msg)): ?>
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl flex items-center gap-3 shadow-sm">
                <i class="fa-solid fa-circle-exclamation text-xl text-red-500"></i>
                <div>
                    <strong class="font-bold">Lưu ý hệ thống:</strong>
                    <span class="text-sm block sm:inline"><?php echo htmlspecialchars($error_msg); ?></span>
                </div>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <a href="admin_orders.php" class="group bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:border-blue-400 hover:shadow-md hover:-translate-y-1 transition-all duration-300 flex items-center justify-between cursor-pointer">
                <div>
                    <p class="text-xs text-gray-400 font-medium uppercase tracking-wider group-hover:text-blue-500 transition-colors">Tổng đơn hàng</p>
                    <h3 class="text-2xl font-bold text-gray-800 mt-1"><?php echo $total_orders; ?></h3>
                </div>
                <div class="w-12 h-12 bg-blue-50 text-blue-500 rounded-xl flex items-center justify-center text-lg group-hover:bg-blue-500 group-hover:text-white transition-colors"><i class="fa-solid fa-boxes-stacked"></i></div>
            </a>
            
            <a href="admin_orders.php?status_filter=Chờ xử lý" class="group bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:border-amber-400 hover:shadow-md hover:-translate-y-1 transition-all duration-300 flex items-center justify-between cursor-pointer">
                <div>
                    <p class="text-xs text-gray-400 font-medium uppercase tracking-wider group-hover:text-amber-500 transition-colors">Chờ xử lý</p>
                    <h3 class="text-2xl font-bold text-amber-500 mt-1"><?php echo $pending_orders; ?></h3>
                </div>
                <div class="w-12 h-12 bg-amber-50 text-amber-500 rounded-xl flex items-center justify-center text-lg group-hover:bg-amber-500 group-hover:text-white transition-colors"><i class="fa-solid fa-clock-rotate-left"></i></div>
            </a>
            
            <a href="admin_orders.php?status_filter=Đang giao" class="group bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:border-indigo-400 hover:shadow-md hover:-translate-y-1 transition-all duration-300 flex items-center justify-between cursor-pointer">
                <div>
                    <p class="text-xs text-gray-400 font-medium uppercase tracking-wider group-hover:text-indigo-500 transition-colors">Đang giao hàng</p>
                    <h3 class="text-2xl font-bold text-indigo-500 mt-1"><?php echo $shipping_orders; ?></h3>
                </div>
                <div class="w-12 h-12 bg-indigo-50 text-indigo-500 rounded-xl flex items-center justify-center text-lg group-hover:bg-indigo-500 group-hover:text-white transition-colors"><i class="fa-solid fa-truck-fast"></i></div>
            </a>
            
            <a href="admin_orders.php?status_filter=Đã giao" class="group bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:border-emerald-400 hover:shadow-md hover:-translate-y-1 transition-all duration-300 flex items-center justify-between cursor-pointer">
                <div>
                    <p class="text-xs text-gray-400 font-medium uppercase tracking-wider group-hover:text-emerald-500 transition-colors">Đã hoàn thành</p>
                    <h3 class="text-2xl font-bold text-emerald-500 mt-1"><?php echo $completed_orders; ?></h3>
                </div>
                <div class="w-12 h-12 bg-emerald-50 text-emerald-500 rounded-xl flex items-center justify-center text-lg group-hover:bg-emerald-500 group-hover:text-white transition-colors"><i class="fa-solid fa-circle-check"></i></div>
            </a>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 mb-8">
            <form method="GET" action="admin_orders.php" class="flex flex-col md:flex-row gap-4 items-end">
                <div class="flex-1 w-full">
                    <label class="block text-xs font-semibold text-gray-500 mb-2">Tìm kiếm đơn hàng</label>
                    <div class="relative">
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="Tìm theo Mã đơn hoặc Tên khách..."
                               class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl outline-none focus:border-orange-500 text-sm">
                        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    </div>
                </div>
                
                <div class="w-full md:w-64">
                    <label class="block text-xs font-semibold text-gray-500 mb-2">Lọc theo trạng thái</label>
                    <select name="status_filter" onchange="this.form.submit()" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl outline-none focus:border-orange-500 text-sm bg-white">
                        <option value="">-- Tất cả trạng thái --</option>
                        <option value="Chờ xử lý" <?php echo $filter_status == 'Chờ xử lý' ? 'selected' : ''; ?>>Chờ xử lý (Đơn mới)</option>
                        <option value="Đã xác nhận" <?php echo $filter_status == 'Đã xác nhận' ? 'selected' : ''; ?>>Đã xác nhận</option>
                        <option value="Đang giao" <?php echo $filter_status == 'Đang giao' ? 'selected' : ''; ?>>Đang giao</option>
                        <option value="Đã giao" <?php echo $filter_status == 'Đã giao' ? 'selected' : ''; ?>>Đã giao (Hoàn thành)</option>
                    </select>
                </div>

                <div class="flex gap-2 w-full md:w-auto">
                    <button type="submit" class="flex-1 md:flex-none bg-orange-500 text-white px-6 py-2.5 rounded-xl font-bold text-sm hover:bg-orange-600 transition-all flex items-center justify-center gap-2">
                        <i class="fa-solid fa-filter"></i> Áp dụng
                    </button>
                    <a href="admin_orders.php" class="bg-gray-100 text-gray-600 px-4 py-2.5 rounded-xl font-medium text-sm hover:bg-gray-200 transition-all flex items-center justify-center">
                        Nhập lại
                    </a>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-left text-sm text-gray-600">
                    <thead class="bg-gray-50 text-gray-500 font-bold uppercase text-[11px] tracking-wider">
                        <tr>
                            <th class="px-6 py-4">Mã đơn</th>
                            <th class="px-6 py-4">Khách hàng</th>
                            <th class="px-6 py-4">Địa chỉ giao hàng</th>
                            <th class="px-6 py-4">Tổng tiền</th>
                            <th class="px-6 py-4">Trạng thái hiện tại</th>
                            <th class="px-6 py-4">Hành động cập nhật</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        <?php if (count($orders_list) > 0): ?>
                            <?php foreach ($orders_list as $row): 
                                $fullname = $row['customer_name'] ?? 'Khách hàng';
                                $phone = $row['customer_phone'] ?? 'Không có SĐT';
                                $address = $row['shipping_address'] ?? 'Chưa cập nhật địa chỉ';
                                $total_amount = $row['total_amount'] ?? 0;
                                $status = $row['order_status'] ?? 'Chờ xử lý';
                                // Ép các đơn cũ có chữ "Chờ xác nhận" hiển thị thành "Chờ xử lý"
                                if ($status == 'Chờ xác nhận') {
                                    $status = 'Chờ xử lý';
                                }
                                
                                $badge_class = "bg-amber-50 text-amber-600 border-amber-100";
                                if ($status == 'Đã xác nhận') $badge_class = "bg-blue-50 text-blue-600 border-blue-100";
                                if ($status == 'Đang giao') $badge_class = "bg-indigo-50 text-indigo-600 border-indigo-100";
                                if ($status == 'Đã giao') $badge_class = "bg-emerald-50 text-emerald-600 border-emerald-100";
                            ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-800">#00<?php echo $row['id']; ?></div>
                                        <?php if(!empty($row['order_code'])): ?>
                                            <div class="text-xs text-[#ff8a00] font-medium mt-1">
                                                <?php echo htmlspecialchars($row['order_code']); ?>
                                            </div>
                                        <?php endif; ?>
                                        <button onclick="viewOrderDetails(<?php echo $row['id']; ?>, '<?php echo !empty($row['order_code']) ? $row['order_code'] : '00'.$row['id']; ?>')" 
                                                class="mt-2 text-xs text-blue-500 hover:text-blue-700 bg-blue-50 px-2 py-1 rounded-md transition flex items-center gap-1">
                                            <i class="fa-solid fa-eye"></i> Xem sản phẩm
                                        </button>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-800"><?php echo htmlspecialchars($fullname); ?></div>
                                        <div class="text-xs text-gray-400 mt-0.5"><i class="fa-solid fa-phone text-[10px]"></i> <?php echo htmlspecialchars($phone); ?></div>
                                    </td>
                                    <td class="px-6 py-4 max-w-xs truncate">
                                        <span class="text-xs" title="<?php echo htmlspecialchars($address); ?>"><?php echo htmlspecialchars($address); ?></span>
                                    </td>
                                    <td class="px-6 py-4 font-bold text-orange-500">
                                        <?php echo number_format($total_amount, 0, ',', '.'); ?>đ
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border <?php echo $badge_class; ?>">
                                            <?php echo $status; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <form method="POST" action="admin_orders.php" class="flex items-center gap-2">
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                                            
                                            <select name="status" onchange="this.form.submit()" class="px-2 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs font-medium focus:outline-none focus:border-orange-500">
                                                <option value="Chờ xử lý" <?php echo $status == 'Chờ xử lý' ? 'selected' : ''; ?>>Chờ xử lý</option>
                                                <option value="Đã xác nhận" <?php echo $status == 'Đã xác nhận' ? 'selected' : ''; ?>>Đã xác nhận</option>
                                                <option value="Đang giao" <?php echo $status == 'Đang giao' ? 'selected' : ''; ?>>Đang giao</option>
                                                <option value="Đã giao" <?php echo $status == 'Đã giao' ? 'selected' : ''; ?>>Đã giao</option>
                                            </select>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                                    <div class="flex flex-col items-center justify-center">
                                        <i class="fa-solid fa-folder-open text-4xl text-gray-200 mb-3"></i>
                                        <p class="text-sm font-medium">Không tìm thấy đơn hàng nào khớp với yêu cầu!</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <div id="orderModal" class="fixed inset-0 z-50 hidden bg-black bg-opacity-50 flex justify-center items-center transition-opacity">
        <div class="bg-white rounded-2xl shadow-xl w-11/12 md:w-2/3 lg:w-1/2 max-h-[90vh] overflow-hidden flex flex-col transform transition-transform scale-100">
            <div class="flex justify-between items-center p-5 border-b border-gray-100 bg-gray-50">
                <h3 class="text-lg font-bold text-gray-800">
                    Sản phẩm đơn <span id="modalOrderCode" class="text-[#ff8a00]"></span>
                </h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-red-500 text-2xl focus:outline-none">&times;</button>
            </div>
            
            <div class="p-5 overflow-y-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                            <th class="p-3 rounded-tl-lg rounded-bl-lg">Tên sản phẩm</th>
                            <th class="p-3 text-center">Số lượng</th>
                            <th class="p-3 text-right">Đơn giá</th>
                            <th class="p-3 text-right rounded-tr-lg rounded-br-lg">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody id="modalTableBody" class="text-sm text-gray-700">
                        </tbody>
                </table>
            </div>
            
            <div class="p-5 border-t border-gray-100 bg-gray-50 flex justify-between items-center">
                <button onclick="closeModal()" class="px-5 py-2 text-sm text-gray-600 bg-white border border-gray-200 rounded-xl hover:bg-gray-100">Đóng</button>
                <div class="text-base text-gray-600">
                    Tổng cộng: <strong id="modalTotal" class="text-xl text-[#ff8a00] ml-2">0đ</strong>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Hàm định dạng tiền tệ VNĐ
        const formatMoney = (amount) => new Intl.NumberFormat('vi-VN').format(amount) + 'đ';

        function viewOrderDetails(orderId, orderCode) {
            // Hiển thị Modal và chữ Loading
            document.getElementById('modalOrderCode').innerText = '#' + orderCode;
            document.getElementById('orderModal').classList.remove('hidden');
            document.getElementById('modalTableBody').innerHTML = '<tr><td colspan="4" class="text-center p-6 text-gray-400"><i class="fa-solid fa-spinner fa-spin mr-2"></i> Đang tải dữ liệu...</td></tr>';
            document.getElementById('modalTotal').innerText = '0đ';

            // Gọi API lấy dữ liệu
            fetch(`api_order_details.php?order_id=${orderId}`)
                .then(response => response.json())
                .then(res => {
                    if (res.status === 'success') {
                        let html = '';
                        let total = 0;
                        
                        if (res.data.length === 0) {
                            html = '<tr><td colspan="4" class="text-center p-6 text-gray-400">Đơn hàng này chưa có dữ liệu sản phẩm trong DB.</td></tr>';
                        } else {
                            res.data.forEach(item => {
                                let thanhtien = item.price * item.quantity;
                                total += thanhtien;
                                html += `<tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                                    <td class="p-3 font-medium">${item.product_name}</td>
                                    <td class="p-3 text-center bg-gray-50/50">${item.quantity}</td>
                                    <td class="p-3 text-right">${formatMoney(item.price)}</td>
                                    <td class="p-3 text-right font-bold text-gray-800">${formatMoney(thanhtien)}</td>
                                </tr>`;
                            });
                        }
                        
                        document.getElementById('modalTableBody').innerHTML = html;
                        document.getElementById('modalTotal').innerText = formatMoney(total);
                    } else {
                        document.getElementById('modalTableBody').innerHTML = `<tr><td colspan="4" class="text-center p-4 text-red-500">Lỗi: ${res.message}</td></tr>`;
                    }
                })
                .catch(err => {
                    document.getElementById('modalTableBody').innerHTML = `<tr><td colspan="4" class="text-center p-4 text-red-500">Lỗi kết nối máy chủ!</td></tr>`;
                });
        }

        // Hàm đóng Modal
        function closeModal() {
            document.getElementById('orderModal').classList.add('hidden');
        }

        // Click ra ngoài khoảng đen để đóng Modal
        document.getElementById('orderModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>
</body>
</html>
