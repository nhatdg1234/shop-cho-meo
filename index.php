<!DOCTYPE html>
<html>
<head>
	<title>LOGIN API</title>
	<link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
     <form id="loginForm">
     	<h2>Đăng nhập</h2>

     	<?php if (isset($_GET['error'])) { ?>
     		<p class="error"><?php echo $_GET['error']; ?></p>
     	<?php } ?>

     	<p id="error-message" class="error" style="display: none;"></p>

     	<label>User Name</label>
     	<input type="text" id="uname" placeholder="User Name"><br>

     	<label>Password</label>
     	<input type="password" id="password" placeholder="Password"><br>

     	<button type="submit">Login</button>

        <a href="signup.php" class="ca">Đăng ký</a>
		<a href="./shop/index.html" class="ca">thoát</a>
     </form>

     <script>
        document.getElementById('loginForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const uname = document.getElementById('uname').value.trim();
            const password = document.getElementById('password').value;
            const errorDiv = document.getElementById('error-message');

            errorDiv.style.display = 'none';
            errorDiv.innerText = '';

            fetch('api/api_login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ uname: uname, password: password })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    window.location.href = data.redirect;
                } else {
                    errorDiv.style.display = 'block';
                    errorDiv.innerText = data.message;
                }
            })
            .catch(() => {
                errorDiv.style.display = 'block';
                errorDiv.innerText = 'Lỗi kết nối tới máy chủ!';
            });
        });
     </script>
</body>
</html>