<!DOCTYPE HTML>
<html>
    <head>
        <meta charset="utf-8">
        <!--chinh cho dine thoai nho hon 600-->
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>VKU Pet Shop</title>
        <link rel="stylesheet" href="style.css">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.2.1/css/fontawesome.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.2.1/css/solid.min.css">
        <link rel="stylesheet" href="css/mainstyle.css">
        <link rel="stylesheet" href="product_card.css">
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
        
    </div>
</div>
    <!----tinh nang san pham--->
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
                    <li><a href="cartegory.html">Giống mới về</a></li>
                    <li><a href="cartegory.html">Giống lớn</a></li>
                    <li><a href="cartegory.html">Chó Shiba</a></li>
                    <li><a href="cartegory.html">Chó phoc</a></li>
                </ul>
            </li>
            <li class="menu-parent">
                <button type="button" class="menu-parent__title" aria-expanded="false">
                    <i class="fa-solid fa-cat fa-icon"></i>
                    Mèo
                    <i class="fa-solid fa-chevron-down fa-chevron"></i>
                </button>
                <ul class="left-menu-item">
                    <li><a href="cartegory.html">Giống mới về</a></li>
                    <li><a href="cartegory.html">Mèo đen</a></li>
                    <li><a href="cartegory.html">Mèo lai</a></li>
                    <li><a href="cartegory.html">Mèo nước ngoài</a></li>
                </ul>
            </li>
            <li><a class="menu-single" href="#"><i class="fa-solid fa-seedling"></i> Vật nuôi đang lớn</a></li>
            <li><a class="menu-single accent" href="#"><i class="fa-solid fa-bolt"></i> Flash sale</a></li>
            <li><a class="menu-single accent" href="#"><i class="fa-solid fa-fire"></i> Hot pet</a></li>
            <li><a class="menu-single" href="#"><i class="fa-solid fa-layer-group"></i> Bộ sưu tập</a></li>
            <li><a class="menu-single" href="#"><i class="fa-solid fa-bowl-food"></i> Thức ăn</a></li>
            <li><a class="menu-single" href="#"><i class="fa-solid fa-suitcase-medical"></i> Vật dụng nuôi</a></li>
        </ul>
    </aside>

    <div class="content-area">
      <div class="product-card-area">

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
// 1. Cần lùi 2 cấp (../../) để ra thư mục gốc gọi đúng file db_conn.php của dự án
require_once '../../db_conn.php'; 

try {
    // 2. Sử dụng PDO truy vấn đồng bộ với toàn bộ hệ thống
    $stmt = $conn->query("SELECT * FROM products ORDER BY id DESC");
    $products = $stmt->fetchAll();

    if (count($products) > 0) {
        foreach ($products as $row) {
            // Định dạng giá tiền chuẩn VNĐ
            $formatted_price = number_format($row['price'], 0, ',', '.');
?>
            <article class="product-card" data-id="<?= $row['id'] ?>" data-name="<?= htmlspecialchars($row['product_name']) ?>" data-price="<?= $row['price'] ?>" data-image="<?= htmlspecialchars($row['image_url']) ?>">
                <div class="product-card__media">
                    <button class="product-card__fav" aria-label="Yêu thích">♥</button>
                    
                    <img src="../anh/<?= htmlspecialchars($row['image_url']) ?>" alt="<?= htmlspecialchars($row['product_name']) ?>">
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
        echo "<p style='text-align:center; width:100%; color:#666;'>Hiện tại cửa hàng chưa có thú cưng hoặc sản phẩm nào.</p>";
    }
} catch (PDOException $e) {
    // Hỗ trợ kiểm tra lỗi nếu kết nối hoặc câu lệnh SQL có vấn đề
    echo "<p style='text-align:center; width:100%; color:red;'>Lỗi hệ thống: Không thể tải danh sách sản phẩm.</p>";
}
?>
        </div>
      </div>
    </div>
  </div>

          <!-- KHUYEN NGHI--->
   
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
                    <img src="./anh/pngwing.com_(1).png">
                    <img src="./anh/pngwing.com_(2).png">
                </div>
            </div>
            <div class="footer-col-2">
                <img src="./anh/anh_logo.png">
                <p>Chúng tôi luôn vì hạnh phúc mọi người.</p>
            </div>
            <div class="footer-col-3">
                <h3>Link bổ ích</h3>
               <ul>
                <li>Phiếu mua hàng</li>
                <li>Chính sách hoàn trả</li>
                <li>Tham gia tiếp thị</li>
               </ul>
            </div>
            <div class="footer-col-4">
                <h3>Theo dõi chúng tôi</h3>
               <ul>
                <li>Facebook</li>
                <li>Twitter</li>
                <li>instagram</li>
               </ul>
            </div>
        </div>
        <hr>
        <p class="copyright">&copy;Copyright 2023 - VKU-er</p>
    </div>
</div>

<!---hieu ung cho menu-->
<script>
    var MenuItems = document.getElementById("MenuItems");
    MenuItems.style.maxHeight = "0px";
    function menutoggle(){
    
        if(MenuItems.style.maxHeight=="0px")
    {
        MenuItems.style.maxHeight = "200px";
    }
    else
    {
        MenuItems.style.maxHeight="0px";
    }
    }
</script>
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
</body>
</html>
