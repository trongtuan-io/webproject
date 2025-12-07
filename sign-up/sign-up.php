<?php
    require 'config.php';

    $error   = "";
    $success = "";

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signup'])) {
        $email            = trim($_POST['email'] ?? "");
        $password         = $_POST['password'] ?? "";
        $confirm_password = $_POST['confirm_password'] ?? "";

        // Validate dữ liệu
        if ($email === '' || $password === '' || $confirm_password === '') {
            $error = "Vui lòng điền đầy đủ thông tin.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Email không hợp lệ.";
        } elseif (strlen($password) < 6) {
            $error = "Mật khẩu phải có ít nhất 6 ký tự.";
        } elseif ($password !== $confirm_password) {
            $error = "Mật khẩu xác nhận không khớp.";
        } else {
            // Kiểm tra kết nối DB
            if (!isset($conn)) {
                $error = "Không tìm thấy kết nối cơ sở dữ liệu. Kiểm tra lại file config.php.";
            } else {
                // Kiểm tra email đã tồn tại chưa (bảng: user, cột: email)
                $stmt = $conn->prepare("SELECT email FROM user WHERE email = ? LIMIT 1");
                if ($stmt) {
                    $stmt->bind_param("s", $email);
                    $stmt->execute();
                    $stmt->store_result();

                    if ($stmt->num_rows > 0) {
                        $error = "Email đã được sử dụng. Vui lòng chọn email khác.";
                    } else {
                        $stmt->close();

                        // Mã hoá mật khẩu
                        $hash = password_hash($password, PASSWORD_BCRYPT);

                        // Thêm user vào DB (bảng: user)
                        $stmt = $conn->prepare("INSERT INTO user (email, password) VALUES (?, ?)");
                        if ($stmt) {
                            $stmt->bind_param("ss", $email, $hash);

                            if ($stmt->execute()) {
                                // Đăng ký thành công
                                // Nếu chỉ muốn hiện thông báo trên trang hiện tại:
                                $success = "Đăng ký thành công! Bạn có thể đăng nhập.";
                                sleep(5);
                                // Còn nếu muốn chuyển sang trang login:
                                header("Location: ../login/login.php");
                                exit;
                            } else {
                                $error = "Có lỗi khi tạo tài khoản. Vui lòng thử lại.";
                            }

                            $stmt->close();
                        } else {
                            $error = "Không chuẩn bị được câu lệnh INSERT.";
                        }
                    }
                } else {
                    $error = "Không chuẩn bị được câu lệnh SELECT.";
                }
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign up</title>
    <link rel="stylesheet" href="sign-up.css">
</head>
<body>
    <main class="auth">
        <div class="auth_content">
            <div class="auth_content_inner">
                <h1>Sign up</h1>
                <div class="auth_desc"></div>

                <!-- Hiển thị thông báo -->
                <?php if ($error): ?>
                    <div class="alert alert-error" style = "color: red;">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success" style = "color: green;">
                        <?= htmlspecialchars($success) ?>
                    </div>
                <?php endif; ?>

                <form action="" class="auth_form" method="post">
                    <div class="form_group">
                        <div class="form_text_input">
                            <input
                                type="email"
                                name="email"
                                placeholder="Email"
                                class="form_input"
                                value="<?= isset($email) ? htmlspecialchars($email) : '' ?>"
                            >
                            <img src="" alt="" class="form_input_icon">
                        </div>
                    </div>

                    <div class="form_group">
                        <div class="form_text_input">
                            <input
                                type="password"
                                name="password"
                                placeholder="Password"
                                class="form_input"
                            >
                            <img src="" alt="" class="form_input_icon">
                        </div>
                    </div>

                    <div class="form_group">
                        <div class="form_text_input">
                            <input
                                type="password"
                                name="confirm_password"
                                placeholder="Confirm password"
                                class="form_input"
                            >
                            <img src="" alt="" class="form_input_icon">
                        </div>
                    </div>

                    <div class="form_group">
                        <a href="" class="forgot_password">Forgot your password?</a>
                    </div>

                    <div class="form_group auth_btn-group">
                        <button class="btn_sign_up" name="signup">Sign up</button>
                    </div>
                </form>

                <p class="auth_text">
                    Bạn đã có tài khoản?
                    <a href="../login/login.php">Login</a>
                </p>
            </div>
        </div>
    </main>
</body>
</html>
