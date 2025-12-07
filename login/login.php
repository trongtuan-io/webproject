<?php
session_start();
require 'config.php';

$inputs = [];
$errors = [];

// Xử lý khi submit form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy và lọc input
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $inputs['email'] = $email;

    if ($email === false || empty($email)) {
        $errors['email'] = 'Email is empty or invalid';
    }


    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
    $inputs['password'] = $password;

    if ($password === false || empty($password)) {
        $errors['password'] = 'Password is empty or invalid';
    }

    if (empty($errors)) {
        // Lấy user theo email
        $stmt = $conn->prepare("SELECT email, password FROM user WHERE email = ? LIMIT 1");

        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();

            // So sánh mật khẩu nhập vào với mật khẩu đã hash trong DB
            if ($user && password_verify($password, $user['password'])) {
                // Đăng nhập thành công
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                if($_SESSION['role'] === 'admin')
                {
                  header("Location : ");
                }
                if($_SESSION['role'] === 'user')
                {
                  header();
                }
                echo "Đăng nhập thành công";
                exit;
            } else {
                $errors['login'] = 'Email hoặc mật khẩu không đúng';
            }
        } else {
            $errors['login'] = 'Lỗi SQL khi prepare statement';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
  <link rel="stylesheet" href="/login/login.css?v=2">
</head>
<body>
  <main class="auth">
    <div class="auth_content">
      <div class="auth_content_inner">
        <h1>Login</h1>

        <?php if (!empty($errors['login'])): ?>
          <div style="color:red; margin-bottom: 8px;">
            <?= htmlspecialchars($errors['login']) ?>
          </div>
        <?php endif; ?>

        <form class="auth_form" method="post">
          <input
            type="email"
            placeholder="Email"
            class="form_input"
            name="email"
            value="<?= htmlspecialchars($inputs['email'] ?? '') ?>"
          >
          <small style="color: red;">
            <?= htmlspecialchars($errors['email'] ?? '') ?>
          </small>

          <input
            type="password"
            placeholder="Password"
            class="form_input"
            name="password"
          >
          <small style="color: red;">
            <?= htmlspecialchars($errors['password'] ?? '') ?>
          </small>

          <button type="submit" class="btn_sign_up" name="login">Login</button>
        </form>
     
        <p class="auth_text">
          Chưa có tài khoản?
          <a href="../sign-up/sign-up.php">Sign up</a>
        </p>
      </div>
    </div>
  </main>
</body>
</html>
