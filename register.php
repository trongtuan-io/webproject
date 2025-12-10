<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Lấy thông tin người dùng từ form
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Email không hợp lệ!";
        exit();
    }

    // Kiểm tra độ dài mật khẩu (ví dụ: từ 6 ký tự trở lên)
    if (strlen($password) < 6) {
        echo "Mật khẩu phải có ít nhất 6 ký tự!";
        exit();
    }

    // Kiểm tra nếu email đã tồn tại
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "Email này đã được đăng ký!";
    } else {
        // Mã hóa mật khẩu
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Thêm người dùng vào cơ sở dữ liệu
        $stmt = $conn->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $email, $hashed_password);

        if ($stmt->execute()) {
            echo "Đăng ký thành công!";
        } else {
            echo "Lỗi đăng ký!";
        }
    }
}
?>

<form method="POST">
    Email: <input type="email" name="email" required><br>
    Mật khẩu: <input type="password" name="password" required><br>
    <button type="submit">Đăng ký</button>
</form>
