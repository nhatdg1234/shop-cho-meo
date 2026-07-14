<?php
// 1. CẤU HÌNH KẾT NỐI DATABASE
$db_host = "127.0.0.1";
$db_user = "root";
$db_pass = "";
$db_name = "petshop_db"; 
$db_port = 3308;

$conn = null;
try {
    $conn = new PDO(
        "mysql:host={$db_host};port={$db_port};dbname={$db_name};charset=utf8mb4",
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die("Kết nối thất bại: " . $e->getMessage());
}

$order = null;
$order_id_search = "";
$error_msg = "";

// 2. XỬ LÝ LẤY DỮ LIỆU ĐƠN HÀNG (Cho phép tìm theo cả ID và Mã VKU)
if (isset($_GET['order_id']) && !empty(trim($_GET['order_id']))) {
    $search_val = trim($_GET['order_id']);
    $order_id_search = $search_val; // Giữ lại chuỗi để hiển thị ô input
    
    // Bỏ dấu # nếu khách hàng lỡ gõ vào
    $clean_search = ltrim($search_val, '#');
    
    // Truy vấn linh hoạt: Tìm theo ID (số) HOẶC Order Code (chữ VKU...)
    $sql = "SELECT * FROM orders WHERE id = :search_id OR order_code = :search_code";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':search_id', intval($clean_search), PDO::PARAM_INT);
    $stmt->bindValue(':search_code', $clean_search, PDO::PARAM_STR);
    $stmt->execute();
    $order = $stmt->fetch();
    
    if (!$order) {
        $error_msg = "Không tìm thấy đơn hàng " . htmlspecialchars($search_val) . " trong hệ thống!";
    }
}
?>

<!DOCTYPE HTML>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>VKU Pet Shop - Theo dõi đơn hàng</title>
        
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
        
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.2.1/css/fontawesome.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.2.1/css/solid.min.css">
        
        <link rel="stylesheet" href="style.css">
        <link rel="stylesheet" href="css/mainstyle.css">
        <link rel="stylesheet" href="checkout-global.css">
        
        <script src="https://cdn.tailwindcss.com"></script>

        <style>
            body { font-family: 'Be Vietnam Pro', sans-serif !important; background-color: #f8f0d8 !important; }
            #MenuItems li { display: inline-block !important; }
            .main-container { margin-top: 40px; }
        </style>
    </head>
   
<body>
    <div class="header">
        <div class="giohang">
            <div class="navbar"> 
                <div class="logo">
                    <img src="./anh/anh_logo.png" width="125px">
                </div>
                <nav>
                    <ul id="MenuItems">
                        <li><a href="./home.php" style="color: #2F6FED;">Home</a> </li>
                        <li><a href="./mua_hang.php" style="color: #2F6FED;">Hàng hóa</a> </li>
                        <li><a href="./vechungtoi.html" style="color: #2F6FED;">Về chúng tôi</a> </li>
                        <li><a href="http://localhost/Login-registration-System-PHP-and-MYSQL/" style="color: #2F6FED;">Tài khoản</a> </li>
                    </ul>
                </nav>
                <img src="./anh/menu.png" class="menu-icon" onclick="menutoggle()">
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 pb-20 main-container">
        
        <div class="bg-white p-6 rounded-2xl shadow-sm mb-8">
            <h3 class="text-lg font-bold text-gray-800 mb-3 text-center sm:text-left">Tra cứu trạng thái đơn hàng của bạn</h3>
            <form method="GET" action="theodoidonhang.php" class="flex flex-col sm:flex-row gap-3">
                <input type="text" name="order_id" value="<?php echo htmlspecialchars($order_id_search); ?>" placeholder="Nhập mã đơn hàng (Ví dụ: VKU-C55401 hoặc 19)" required
                       class="flex-1 px-5 py-3 border border-gray-200 rounded-xl outline-none focus:border-[#ff8a00] transition-all text-sm">
                <button type="submit" class="bg-[#ff8a00] text-white px-6 py-3 rounded-xl font-medium hover:bg-[#e67a00] transition-all text-sm flex items-center justify-center gap-2">
                    <i class="fa-solid fa-magnifying-glass"></i> Kiểm tra ngay
                </button>
            </form>
            <?php if(!empty($error_msg)): ?>
                <p class="text-red-500 text-sm mt-3 font-medium"><i class="fa-solid fa-circle-exclamation mr-1"></i> <?php echo $error_msg; ?></p>
            <?php endif; ?>
        </div>

        <?php if ($order): 
            $current_status = $order['order_status'] ?? 'Chờ xử lý';
            
            if ($current_status === 'Chờ xác nhận') {
                $current_status = 'Chờ xử lý';
            }

            $step1 = true; 
            $step2 = ($current_status == 'Đã xác nhận' || $current_status == 'Đang giao' || $current_status == 'Đã giao');
            $step3 = ($current_status == 'Đang giao' || $current_status == 'Đã giao');
            $step4 = ($current_status == 'Đã giao');
        ?>
            <div class="bg-white rounded-[24px] p-8 md:p-10 shadow-[0_10px_40px_rgba(0,0,0,0.02)]">
                
                <div class="flex flex-col sm:flex-row justify-between items-center border-b border-gray-100 pb-6 mb-8 gap-2">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">
                            Chi tiết đơn hàng: 
                            <span class="text-[#ff8a00]">
                                #<?php echo !empty($order['order_code']) ? htmlspecialchars($order['order_code']) : '00'.$order['id']; ?>
                            </span>
                        </h2>
                        <p class="text-gray-400 text-xs mt-1">Ngày đặt: <?php echo isset($order['created_at']) ? date('d/m/Y H:i', strtotime($order['created_at'])) : date('d/m/Y'); ?></p>
                    </div>
                    <div class="bg-orange-50 text-[#ff8a00] font-bold px-4 py-2 rounded-xl text-sm border border-orange-100 animate-pulse">
                        Trạng thái hiện tại: <?php echo $current_status; ?>
                    </div>
                </div>

                <div class="flex flex-col md:flex-row justify-between items-start md:items-center px-4 mb-12 gap-8 md:gap-2 relative">
                    
                    <div class="flex md:flex-col items-center gap-4 md:gap-2 text-center flex-1 z-10 w-full md:w-auto">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center font-bold transition-all bg-[#ff8a00] text-white shadow-lg shadow-orange-200">
                            <i class="fa-solid fa-receipt"></i>
                        </div>
                        <div class="text-left md:text-center">
                            <p class="font-bold text-sm text-gray-800">Chờ xử lý</p>
                            <p class="text-xs text-gray-400">Shop đã nhận đơn</p>
                        </div>
                    </div>

                    <div class="hidden md:block flex-1 h-1 transition-all <?php echo $step2 ? 'bg-[#ff8a00]' : 'bg-gray-200'; ?> mx-2"></div>

                    <div class="flex md:flex-col items-center gap-4 md:gap-2 text-center flex-1 z-10 w-full md:w-auto">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center font-bold transition-all <?php echo $step2 ? 'bg-[#ff8a00] text-white shadow-lg shadow-orange-200' : 'bg-gray-100 text-gray-400'; ?>">
                            <i class="fa-solid fa-clipboard-check"></i>
                        </div>
                        <div class="text-left md:text-center">
                            <p class="font-bold text-sm <?php echo $step2 ? 'text-gray-800' : 'text-gray-400'; ?>">Đã xác nhận</p>
                            <p class="text-xs text-gray-400">Đã kiểm tra kho</p>
                        </div>
                    </div>

                    <div class="hidden md:block flex-1 h-1 transition-all <?php echo $step3 ? 'bg-[#ff8a00]' : 'bg-gray-200'; ?> mx-2"></div>

                    <div class="flex md:flex-col items-center gap-4 md:gap-2 text-center flex-1 z-10 w-full md:w-auto">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center font-bold transition-all <?php echo $step3 ? 'bg-[#ff8a00] text-white shadow-lg shadow-orange-200' : 'bg-gray-100 text-gray-400'; ?>">
                            <i class="fa-solid fa-truck-fast"></i>
                        </div>
                        <div class="text-left md:text-center">
                            <p class="font-bold text-sm <?php echo $step3 ? 'text-gray-800' : 'text-gray-400'; ?>">Đang giao</p>
                            <p class="text-xs text-gray-400">Shipper đang ship</p>
                        </div>
                    </div>

                    <div class="hidden md:block flex-1 h-1 transition-all <?php echo $step4 ? 'bg-[#ff8a00]' : 'bg-gray-200'; ?> mx-2"></div>

                    <div class="flex md:flex-col items-center gap-4 md:gap-2 text-center flex-1 z-10 w-full md:w-auto">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center font-bold transition-all <?php echo $step4 ? 'bg-[#ff8a00] text-white shadow-lg shadow-orange-200' : 'bg-gray-100 text-gray-400'; ?>">
                            <i class="fa-solid fa-box-open"></i>
                        </div>
                        <div class="text-left md:text-center">
                            <p class="font-bold text-sm <?php echo $step4 ? 'text-gray-800' : 'text-gray-400'; ?>">Đã giao</p>
                            <p class="text-xs text-gray-400">Nhận hàng thành công</p>
                        </div>
                    </div>

                </div>

                <div class="bg-[#fff9e9] rounded-2xl p-6 border border-orange-100">
                    <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2 text-base">
                        <i class="fa-solid fa-location-dot text-[#ff8a00]"></i> Thông tin giao nhận hàng
                    </h3>
                    <div class="space-y-3 text-sm text-gray-600">
                        <div class="flex justify-between border-b border-orange-50/50 pb-2">
                            <span>Người nhận hàng:</span>
                            <strong class="text-gray-800"><?php echo htmlspecialchars($order['customer_name'] ?? 'Khách hàng'); ?></strong>
                        </div>
                        <div class="flex justify-between border-b border-orange-50/50 pb-2">
                            <span>Số điện thoại:</span>
                            <strong class="text-gray-800"><?php echo htmlspecialchars($order['customer_phone'] ?? 'Chưa cập nhật'); ?></strong>
                        </div>
                        <div class="flex justify-between border-b border-orange-50/50 pb-2">
                            <span>Địa chỉ nhận hàng:</span>
                            <strong class="text-gray-800 text-right max-w-xs md:max-w-md"><?php echo htmlspecialchars($order['shipping_address'] ?? 'Chưa cập nhật'); ?></strong>
                        </div>
                        <div class="flex justify-between pt-1">
                            <span>Tổng tiền thanh toán:</span>
                            <strong class="text-[#ff8a00] text-lg font-bold"><?php echo number_format($order['total_amount'] ?? 0, 0, ',', '.'); ?>đ</strong>
                        </div>
                    </div>
                </div>

            </div>
        <?php else: ?>
            <div class="bg-white rounded-[24px] p-12 shadow-sm text-center text-gray-400">
                <i class="fa-solid fa-magnifying-glass-location text-5xl text-orange-200 mb-4"></i>
                <p class="text-sm">Vui lòng nhập mã số đơn hàng ở ô phía trên để xem hành trình giao hàng bạn nhé!</p>
            </div>
        <?php endif; ?>

    </div>

    <script>
        var MenuItems = document.getElementById("MenuItems");
        if(MenuItems) { MenuItems.style.maxHeight = "0px"; }
        function menutoggle(){
            if(MenuItems.style.maxHeight == "0px"){
                MenuItems.style.maxHeight = "200px";
            } else {
                MenuItems.style.maxHeight = "0px";
            }
        }
    </script>
</body>
</html>