<?php

$sname = "127.0.0.1";
$unmae = "root";
$password = "";
$db_name = "nhat_db";
$port = 3308;

$conn = mysqli_connect($sname, $unmae, $password, $db_name, $port);

if (!$conn) {
    echo "Kết nối thất bại!";
}