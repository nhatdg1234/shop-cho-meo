<?php
session_start();
include "db_conn.php";

if (isset($_POST['uname']) && isset($_POST['password'])) {
	function validate_text($data){
       $data = trim($data);
	   $data = htmlspecialchars($data);
	   return $data;
	}

	$uname = validate_text($_POST['uname']);
	$pass = trim($_POST['password']);

	if (empty($uname)) {
		header("Location: index.php?error=" . urlencode("User Name is required"));
	    exit();
	}else if(empty($pass)){
        header("Location: index.php?error=" . urlencode("Password is required"));
	    exit();
	}else{
        $stmt = $conn->prepare("SELECT id, username, customer_name, password, role FROM users WHERE username = ?");
        $stmt->execute([$uname]);
        $row = $stmt->fetch();

		if ($row && password_verify($pass, $row['password'])) {
            $_SESSION['user_name'] = $row['username'];
            $_SESSION['name'] = $row['customer_name'];
            $_SESSION['id'] = $row['id'];
            $_SESSION['role'] = $row['role'];

            if ($row['role'] === 'admin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: home.php");
            }
		    exit();
        }else{
			header("Location: index.php?error=" . urlencode("Incorect User name or password"));
	        exit();
		}
	}
	
}else{
	header("Location: index.php");
	exit();
}
