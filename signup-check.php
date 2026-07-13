<?php
session_start();
include "db_conn.php";

if (isset($_POST['uname']) && isset($_POST['password'])
    && isset($_POST['name']) && isset($_POST['re_password']) && isset($_POST['email'])) {

	function validate_text($data){
       $data = trim($data);
	   $data = htmlspecialchars($data);
	   return $data;
	}

	$uname = validate_text($_POST['uname']);
	$pass = trim($_POST['password']);
	$re_pass = trim($_POST['re_password']);
	$name = validate_text($_POST['name']);
	$email = validate_text($_POST['email']);

	$user_data = 'uname=' . urlencode($uname) . '&name=' . urlencode($name) . '&email=' . urlencode($email);

	if (empty($uname)) {
		header("Location: signup.php?error=" . urlencode("User Name is required") . "&$user_data");
	    exit();
	}else if(empty($email)){
        header("Location: signup.php?error=" . urlencode("Email is required") . "&$user_data");
	    exit();
	}else if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        header("Location: signup.php?error=" . urlencode("Invalid email format") . "&$user_data");
	    exit();
	}else if(empty($pass)){
        header("Location: signup.php?error=" . urlencode("Password is required") . "&$user_data");
	    exit();
	}else if(empty($re_pass)){
        header("Location: signup.php?error=" . urlencode("Re Password is required") . "&$user_data");
	    exit();
	}else if(empty($name)){
        header("Location: signup.php?error=" . urlencode("Name is required") . "&$user_data");
	    exit();
	}else if(strlen($pass) < 6){
        header("Location: signup.php?error=" . urlencode("Mật khẩu phải từ 6 ký tự trở lên") . "&$user_data");
	    exit();
	}else if($pass !== $re_pass){
        header("Location: signup.php?error=" . urlencode("The confirmation password does not match") . "&$user_data");
	    exit();
	}else{
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$uname, $email]);

		if ($stmt->fetch()) {
			header("Location: signup.php?error=" . urlencode("Username or Email already exists") . "&$user_data");
	        exit();
		}else {
           $hashed_password = password_hash($pass, PASSWORD_BCRYPT);
           $stmt = $conn->prepare("INSERT INTO users(customer_name, email, username, password) VALUES(?, ?, ?, ?)");
           $result2 = $stmt->execute([$name, $email, $uname, $hashed_password]);

           if ($result2) {
            	 header("Location: signup.php?success=" . urlencode("Your account has been created successfully"));
	         exit();
           }else {
	           	header("Location: signup.php?error=" . urlencode("Unknown error occurred") . "&$user_data");
		        exit();
           }
		}
	}
	
}else{
	header("Location: signup.php");
	exit();
}
