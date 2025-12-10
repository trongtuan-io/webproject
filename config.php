<?php
$localhost = 'localhost';
$username = 'root';
$password = '';
$db = 'pw';
$conn = mysqli_connect($localhost, $username, $password, $db);

if(!$conn)
{
    die("Kết nối thất bại: " . mysqli_connect_error());
}
mysqli_set_charset($conn, 'utf8');
?>