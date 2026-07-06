<?php 
//tao phien moi, luu thong tin
session_start();
//giu phien va xoa cac bien phien
session_unset();
//xoa phien
session_destroy();

header("Location: index.php");