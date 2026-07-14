<!DOCTYPE html>
<html>
<head>
    <title>SIGN UP</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <style>
        /* CSS làm đẹp cho thông báo lỗi/thành công */
        .msg {
            text-align: center;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            display: none; /* Mặc định ẩn */
            font-size: 14px;
            font-weight: 500;
        }
        .msg.error {
            background: #f8d7da;
            color: #842029;
            border: 1px solid #f5c2c7;
            display: block;
        }
        .msg.success {
            background: #d1e7dd;
            color: #0f5132;
            border: 1px solid #badbcc;
            display: block;
        }
    </style>
</head>
<body>
    <form id="registerForm">
        <h2>Đăng ký</h2>
        
        <div id="messageBox" class="msg"></div>

        <label>Name</label>
        <input type="text" name="name" id="name" placeholder="Name" required><br>

        <label>Email</label>
        <input type="email" name="email" id="email" placeholder="Email" required><br>

        <label>User Name</label>
        <input type="text" name="uname" id="uname" placeholder="User Name" required><br>

        <label>Password</label>
        <input type="password" name="password" id="password" placeholder="Password" required><br>

        <label>Xác nhận Password</label>
        <input type="password" name="re_password" id="re_password" placeholder="Re_Password" required><br>

        <button type="submit" id="submitBtn">Sign Up</button>
        <a href="index.php" class="ca">Đã có tài khoản? Đăng nhập</a>
    </form>

    <script>
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            e.preventDefault(); // Ngăn form tải lại trang truyền thống

            const btn = document.getElementById('submitBtn');
            const msgBox = document.getElementById('messageBox');
            
            // 1. Lấy dữ liệu từ các ô input
            const name = document.getElementById('name').value;
            const email = document.getElementById('email').value;
            const uname = document.getElementById('uname').value;
            const password = document.getElementById('password').value;
            const re_password = document.getElementById('re_password').value;

            // 2. Thay đổi trạng thái nút (Loading)
            btn.innerText = 'Đang xử lý...';
            btn.disabled = true;
            
            // Ẩn thông báo cũ bằng cách xóa class
            msgBox.className = 'msg'; 
            msgBox.style.display = 'none'; // Tạm ẩn lúc đang tải

            // 3. Gửi dữ liệu xuống API (api/api_register.php)
            fetch('api/api_register.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    name: name,
                    email: email,
                    uname: uname,
                    password: password,
                    re_password: re_password
                })
            })
            .then(response => response.json())
            .then(data => {
                // Nhận kết quả, mở khóa nút
                btn.innerText = 'Sign Up';
                btn.disabled = false;

                // Ép nó hiển thị lên lại
                msgBox.style.display = 'block'; 

                if (data.status === 'success') {
                    // Đăng ký thành công - Thêm Tích xanh ✅
                    msgBox.className = 'msg success';
                    msgBox.innerHTML = '✅ ' + data.message; 
                    document.getElementById('registerForm').reset();
                    
                    // Tự động chuyển hướng về trang đăng nhập sau 1.5 giây
                    setTimeout(() => {
                        window.location.href = 'index.php'; 
                    }, 1500);
                } else {
                    // Lỗi (Sai pass, trùng email...) - Thêm Dấu X đỏ ❌
                    msgBox.className = 'msg error';
                    msgBox.innerHTML = '❌ ' + data.message;
                }
            })
            .catch(error => {
                // Lỗi mạng hoặc server
                btn.innerText = 'Sign Up';
                btn.disabled = false;
                
                msgBox.style.display = 'block';
                msgBox.className = 'msg error';
                msgBox.innerHTML = '⚠️ Lỗi kết nối đến máy chủ!'; // Thêm icon Cảnh báo
                console.error('Error:', error);
            });
        });
    </script>
</body>
</html>