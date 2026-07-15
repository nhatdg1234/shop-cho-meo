<?php
// Thêm file kết nối Database
require_once "../../db_conn.php"; 

// Lấy duy nhất 1 sản phẩm đang được bật is_spotlight = 1
$stmt_offer = $conn->prepare("SELECT * FROM products WHERE is_spotlight = 1 LIMIT 1");
$stmt_offer->execute();
$offer = $stmt_offer->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE HTML>
<html lang="vi">
    <head>
        
        <meta charset="utf-8">
        <link rel="icon" href="./anh/anh_dai_dien_trang.png" type="image/x-icon">
        <!--chinh cho dine thoai nho hon 600-->
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>VKU Pet Shop</title>
        <link rel="stylesheet" href="style.css">
        <link rel="stylesheet" href="product_card.css">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@500;700;800;900&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.2.1/css/fontawesome.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.2.1/css/solid.min.css">
    </head>
   
<body>
    <div class="header">

    
    <div class="giohang">
        <div class="navbar"> <!-- thanh bang chon-->
            <div class="logo">
                <img src="./anh/anh_logo.png" width="125px">
            </div>
            <nav>
                <ul id="MenuItems">
                    <li><a href="./home.php">Home</a> </li>
                    <li><a href="./mua_hang.php">Hàng hóa</a> </li>
                    <li><a href="./vechungtoi.html">Về chúng tôi</a> </li>
                    <li><a href="http://localhost/Login-registration-System-PHP-and-MYSQL/">Tài khoản</a> </li>
                </ul>
            </nav>
            
            <img src="./anh/menu.png" class="menu-icon" onclick="menutoggle()">
        </div>
        <div class="row">
            <div class="col-2">
<h1>Trao niềm hạnh phúc mới<br> cho cuộc sống của bạn!</h1>
<p>Hạnh phúc không chỉ ở những điều lớn lao mang lại,<br> mà nó còn xuất phát từ những điều nhỏ nhặc, gia đình, bạn bè, và cả thú nuôi.</p>
<a href="#khu-vuc-san-pham" class="btn">Khám phá ngay &#9755;</a>
            </div>
            <div class="col-2">
<img src="./anh/anh_dai_dien_trang.png" >
            </div>
        </div>
    </div>
</div>



<!------  tinh nang danh muc---->    



<div class="page-layout">
    <aside class="left-menu" aria-label="Danh mục sản phẩm">
        <div class="left-menu__header"><i class="fa-solid fa-paw"></i> Danh mục</div>
        <ul class="left-menu__list">
            <li class="menu-parent">
                <button type="button" class="menu-parent__title" aria-expanded="false">
                    <i class="fa-solid fa-dog fa-icon"></i>
                    Chó
                    <i class="fa-solid fa-chevron-down fa-chevron"></i>
                </button>
                <ul class="left-menu-item">
                    <li><a href="home.php?category=Chó - Giống mới về">Giống mới về</a></li>
                    <li><a href="home.php?category=Chó - Giống lớn">Giống lớn</a></li>
                    <li><a href="home.php?category=Chó Shiba">Chó Shiba</a></li>
                    <li><a href="home.php?category=Chó Phốc">Chó Phốc</a></li>
                </ul>
            </li>

            <li class="menu-parent">
                <button type="button" class="menu-parent__title" aria-expanded="false">
                    <i class="fa-solid fa-cat fa-icon"></i>
                    Mèo
                    <i class="fa-solid fa-chevron-down fa-chevron"></i>
                </button>
                <ul class="left-menu-item">
                    <li><a href="home.php?category=Mèo - Giống mới về">Giống mới về</a></li>
                    <li><a href="home.php?category=Mèo đen">Mèo đen</a></li>
                    <li><a href="home.php?category=Mèo lai">Mèo lai</a></li>
                    <li><a href="home.php?category=Mèo nước ngoài">Mèo nước ngoài</a></li>
                </ul>
            </li>

            <li><a class="menu-single" href="home.php?category=Vật nuôi đang lớn"><i class="fa-solid fa-seedling"></i> Vật nuôi đang lớn</a></li>
            <li><a class="menu-single accent" href="home.php?category=Flash sale"><i class="fa-solid fa-bolt"></i> Flash sale</a></li>
            <li><a class="menu-single accent" href="home.php?category=Hot pet"><i class="fa-solid fa-fire"></i> Hot pet</a></li>
            <li><a class="menu-single" href="home.php?category=Bộ sưu tập"><i class="fa-solid fa-layer-group"></i> Bộ sưu tập</a></li>
            <li><a class="menu-single" href="home.php?category=Thức ăn"><i class="fa-solid fa-bowl-food"></i> Thức ăn</a></li>
            <li><a class="menu-single" href="home.php?category=Vật dụng nuôi"><i class="fa-solid fa-suitcase-medical"></i> Vật dụng nuôi</a></li>
        </ul>
    </aside>

    <div class="content-area">
        <div class="categories">
        <div class="small-contaianer">
            <!-- removed image row per request -->
        </div>
        <!----tinh nang san pham--->
<div class="product-card-area" id="khu-vuc-san-pham">

                        <div class="cartegory-boloc row">
                                <select name="" id="">
                                        <option value="">Sắp xếp</option>
                                        <option value="">Giá từ cao đến thấp</option>
                                        <option value="">Giá từ thấp đến cao</option> 
                
                                </select>
                        </div>
            
<br>
<br>


                <div class="demo-topbar">
                    <div class="cart-icon" id="cartIcon">
                        🛍️
                        <span class="cart-icon__badge" id="cartBadge">0</span>
                    </div>
                </div>

                <div class="toast" id="toast"></div>

                <div class="grid">
<?php
// 1. Kết nối CSDL thông qua đường dẫn chính xác của dự án
require_once '../../db_conn.php'; 

try {
    $filter_title = "Tất cả sản phẩm";
    $params = [];
    
    // Câu truy vấn mặc định lấy toàn bộ sản phẩm
    $sql = "SELECT * FROM products";

    // 2. Chỉ thực hiện lọc chính xác khi có tham số 'category' truyền lên URL
    if (isset($_GET['category']) && !empty(trim($_GET['category']))) {
        $category = trim($_GET['category']);
        $sql .= " WHERE category = :category";
        $params[':category'] = $category;
        $filter_title = "Danh mục: " . htmlspecialchars($category);
    }

    $sql .= " ORDER BY id DESC";

    // 3. Thực thi câu lệnh SQL bằng PDO
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();

    // Hiển thị tiêu đề danh mục hiện tại để người dùng dễ nhận biết
    // echo "<h2 style='width:100%; text-align:left; margin-bottom: 20px; color:#ff8a00; font-size:1.4rem; font-weight:700;'>" . $filter_title . "</h2>";

    if (count($products) > 0) {
        foreach ($products as $row) {
            $formatted_price = number_format($row['price'], 0, ',', '.');
?>
            <article class="product-card" data-id="<?= $row['id'] ?>" data-name="<?= htmlspecialchars($row['product_name']) ?>" data-price="<?= $row['price'] ?>" data-image="<?= htmlspecialchars($row['image_url']) ?>">
                <div class="product-card__media">
                    <button class="product-card__fav" aria-label="Yêu thích">♥</button>
                    <img src="./anh/<?= htmlspecialchars($row['image_url']) ?>" alt="<?= htmlspecialchars($row['product_name']) ?>">
                </div>
                <div class="product-card__body">
                    <div class="product-card__brand">VKU Pet Shop</div>
                    <h3 class="product-card__title"><?= htmlspecialchars($row['product_name']) ?></h3>
                    <div class="product-card__price">
                        <span class="product-card__price-current"><?= $formatted_price ?>₫</span>
                    </div>
                    <button class="product-card__btn"><span class="btn-icon">🛒</span> Add to Cart</button>
                </div>
            </article>
<?php
        }
    } else {
        echo "<p style='text-align:center; width:100%; color:#666; padding: 40px 0;'>Không tìm thấy sản phẩm nào thuộc danh mục này.</p>";
    }
} catch (PDOException $e) {
    echo "<p style='text-align:center; width:100%; color:red;'>Lỗi hệ thống: Không thể tải danh sách sản phẩm.</p>";
}
?>
                </div>
                <div style="text-align: center; margin-top: 30px;"></div>
                    <a href="./mua_hang.php" class="btn">Khám phá thêm &#9755;</a>
                </div>
            </div>
        <!-- KHU VỰC ĐẶC QUYỀN (SPOTLIGHT PRO) -->
        <?php if ($offer): 
            $price_formatted = number_format($offer['price'], 0, ',', '.');
            $old_price_formatted = number_format($offer['price'] * 1.2, 0, ',', '.'); 
            $save_formatted = number_format(($offer['price'] * 1.2) - $offer['price'], 0, ',', '.');
        ?>
        <div class="spotlight-pro">
            <div class="spotlight-glass">
                <div class="sl-media">
                    <div class="sl-blob"></div>
                    <img src="./anh/<?= htmlspecialchars($offer['image_url']) ?>" class="sl-pet-img" alt="<?= htmlspecialchars($offer['product_name']) ?>">
                    
                    <div class="sl-sticker sticker-paw">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M12,8.5c-1.5,0-2.8-1.2-2.8-2.8S10.5,3,12,3s2.8,1.2,2.8,2.8S13.5,8.5,12,8.5z M6.8,11.2c-1.4,0-2.5-1.1-2.5-2.5s1.1-2.5,2.5-2.5 s2.5,1.1,2.5,2.5S8.2,11.2,6.8,11.2z M17.2,11.2c-1.4,0-2.5-1.1-2.5-2.5s1.1-2.5,2.5-2.5s2.5,1.1,2.5,2.5S18.6,11.2,17.2,11.2z M12,21 c-4.5,0-7-3.5-7-6.5c0-2.5,2-4.5,4.5-4.5c1.2,0,2.3,0.5,3.1,1.4c0.8-0.9,1.9-1.4,3.1-1.4c2.5,0,4.5,2,4.5,4.5C19,17.5,16.5,21,12,21z"/></svg>
                    </div>
                    <div class="sl-sticker sticker-sparkle">✨</div>
                </div>
                
                <div class="sl-content">
                    <span class="sl-badge">🔥 Bé cưng độc quyền duy nhất</span>
                    <h2 class="sl-title"><?= htmlspecialchars($offer['product_name']) ?></h2>
                    <p class="sl-desc"><?= htmlspecialchars($offer['description'] ?? 'Sản phẩm đặc quyền đang được săn đón nhất tại hệ thống.') ?></p>
                    
                    <div class="sl-pricing">
                        <div class="sl-price-wrap">
                            <span class="sl-price-old"><?= $old_price_formatted ?>đ</span>
                            <span class="sl-price-current"><?= $price_formatted ?>đ</span>
                        </div>
                        <div class="sl-save-badge">Tiết kiệm <?= $save_formatted ?>đ</div>
                    </div>
                    
                    <a href="giohang.html" class="sl-cta" onclick="ruocBeNgay(event, '<?= $offer['id'] ?>', '<?= htmlspecialchars($offer['product_name']) ?>', <?= $offer['price'] ?>, '<?= htmlspecialchars($offer['image_url']) ?>')">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M12,8.5c-1.5,0-2.8-1.2-2.8-2.8S10.5,3,12,3s2.8,1.2,2.8,2.8S13.5,8.5,12,8.5z M6.8,11.2c-1.4,0-2.5-1.1-2.5-2.5s1.1-2.5,2.5-2.5 s2.5,1.1,2.5,2.5S8.2,11.2,6.8,11.2z M17.2,11.2c-1.4,0-2.5-1.1-2.5-2.5s1.1-2.5,2.5-2.5s2.5,1.1,2.5,2.5S18.6,11.2,17.2,11.2z M12,21 c-4.5,0-7-3.5-7-6.5c0-2.5,2-4.5,4.5-4.5c1.2,0,2.3,0.5,3.1,1.4c0.8-0.9,1.9-1.4,3.1-1.4c2.5,0,4.5,2,4.5,4.5C19,17.5,16.5,21,12,21z"/></svg>
                        <span>Rước Bé Ngay</span>
                        <span class="sl-cta-arrow">→</span>
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>
<!---kiem tra don hang-->
<div class="testimonial">
    <div class="small-contaianer">
        <div class="row">
            <div class="col-3">
               
<p class="danhgia">chó shiba mua ở đây thật sự dễ thương</p>
<img src="./anh/nguoi1.jpg">
<h3>Hoang Phuong</h3>
            </div>

            <div class="col-3">
              
<p class="danhgia">Chó rất dễ thương và phục vụ rất nhiệt tình</p>
<img src="./anh/nguoi2.jpg">
<h3>TraNam</h3>

            </div>

            <div class="col-3">
                
<p class="danhgia">Giá phải chăng</p>
<img src="./anh/nguoi3.jpg">
<h3>Thi Hung</h3>

            </div>

        </div>
    </div>
</div>
<!---giống loài-->
<div class="loai">
    <div class="small-contaianer">
        <div class="row">
            <div class="col-5">
                <img src="./anh/anh_logo.png">
            </div>
        </div>
    </div>
</div>
<!---phan cuoi-->

<div class="footer">
    <div class="container">
        <div class="row">
            <div class="footer-col-1">
                <h3>Tải ứng dụng ngay!</h3>
                <p>Tải ứng dụng về android và ios cho điện thoại.</p>
                <div class="app-logo">
                    <a href="./error.html"><img src="./anh/pngwing.com_(1).png"></a>
                    <a href="./error.html"><img src="./anh/pngwing.com_(2).png"></a>
                </div>
            </div>
            <div class="footer-col-2">
                <img src="./anh/anh_logo.png">
                <p>Chúng tôi luôn vì hạnh phúc mọi người.</p>
            </div>
            <div class="footer-col-3">
                <h3>Link bổ ích</h3>
               <ul>
                <a href=""><li>Phiếu mua hàng</li></a>
                <a href=""><li>Chính sách hoàn trả</li></a>
                <a href=""><li>Tham gia tiếp thị</li></a>
               </ul>
            </div>
            <div class="footer-col-4">
                <h3>Theo dõi chúng tôi</h3>
               <ul>
                <a href="https://facebook.com"><li>Facebook</li></a>
                <a href="https://x.com"><li>Twitter</li></a>
                <a href="https://instagram.com"><li>Instagram</li></a>
               </ul>
            </div>
        </div>
        <hr>
        <p class="copyright">&copy;Copyright 2023 - VKU-er</p>
</div>
</div>

                <div class="cart-overlay" id="cartOverlay"></div>

<div class="cart-drawer" id="cartDrawer">
    <div class="cart-drawer__header">
        <h3>🛍️ Giỏ hàng của bạn</h3>
        <button type="button" class="cart-drawer__close" id="closeCart">&times;</button>
    </div>
    
    <div class="cart-drawer__content" id="cartDrawerContent">
        <div class="cart-item">
            <img src="https://images.unsplash.com/photo-1543466835-00a7907e9de1?w=600" alt="Chó Corgi">
            <div class="cart-item-details">
                <h4>Chó Corgi thuần chủng</h4>
                <p>500.000đ</p>
            </div>
            <button type="button" class="cart-item__remove" aria-label="Xóa sản phẩm">&times;</button>
        </div>
    </div>
    
    <div class="cart-drawer__footer">
        <div class="cart-drawer__total">
            <span>Tổng tiền:</span>
            <span class="total-price" id="cartTotal">500.000đ</span>
        </div>
        <button type="button" class="btn-checkout" onclick="window.location.href='giohang.html'">Thanh toán ngay</button>
        <button type="button" class="btn-continue" id="continueShopping">Tiếp tục mua sắm</button>
    </div>
</div>

<script src="product_card.js"></script>
<script src="sidebar.js"></script>

<script>
function ruocBeNgay(event) {
    // Ngăn chặn chuyển trang ngay lập tức để xử lý thêm hàng vào giỏ trước
    event.preventDefault(); 

    // 1. Định nghĩa thông tin bé cưng độc quyền (Khớp hoàn toàn cấu trúc dữ liệu của bạn)
    const spotlightPet = {
        id: "spotlight-tuxedo",
        name: "Mèo Tuxedo Quý Phái",
        price: 5200000,
        image: "meopng.PNG",
        quantity: 1
    };

    // 2. Lấy giỏ hàng hiện tại từ localStorage (Giống cách các file product_card.js thường vận hành)
    let cart = JSON.parse(localStorage.getItem('cart')) || [];

    // 3. Kiểm tra xem bé mèo này đã có trong giỏ hàng chưa
    let existingItem = cart.find(item => item.id === spotlightPet.id);

    if (existingItem) {
        // Nếu có rồi thì tăng số lượng lên 1
        existingItem.quantity += 1;
    } else {
        // Nếu chưa có thì thêm mới vào mảng giỏ hàng
        cart.push(spotlightPet);
    }

    // 4. Lưu lại giỏ hàng mới vào localStorage
    localStorage.setItem('cart', JSON.stringify(cart));

    // 5. Chuyển hướng thẳng tới trang thanh toán giống hệt nút btn-checkout!
    window.location.href = 'giohang.html';
}
</script>

</body>
</html>
