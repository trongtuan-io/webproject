<?php
$localhost = 'localhost';
$username = 'root';
$password = '';
$db = 'sign-up';

$conn = mysqli_connect($localhost, $username, $password, $db);
if(!$conn)
{
    die("Kết nối thất bại" . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8");
?>