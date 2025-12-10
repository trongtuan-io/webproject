<?php
session_start();
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Kiểm tra tài khoản
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['role'] = $user['role'];  // Lưu vai trò người dùng vào session

            // Kiểm tra nếu người dùng là admin
            if ($user['role'] == 'admin') {
                // Chuyển hướng đến trang admin
                header("Location: admin.php");
            } else {
                // Chuyển hướng đến trang dashboard cho người dùng bình thường
                header("Location: dashboard.php");
            }
            exit;
        } else {
            echo "Mật khẩu sai!";
        }
    } else {
        echo "Tài khoản không tồn tại!";
    }
}
?>

<form method="POST">
    Email: <input type="email" name="email" required><br>
    Mật khẩu: <input type="password" name="password" required><br>
    <button type="submit">Đăng nhập</button>
</form>
