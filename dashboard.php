<?php
session_start();
require 'config.php';

// Kiểm tra nếu người dùng đã đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];  // Lấy vai trò người dùng từ session

// Lấy tất cả các dự án mà người dùng tham gia và vai trò của họ trong từng dự án
$sql_projects = "SELECT p.id AS project_id, p.title, pm.role 
                 FROM projects p
                 JOIN project_members pm ON p.id = pm.project_id
                 WHERE pm.user_id = ?";  // Lấy dự án của người dùng từ bảng project_members
$stmt = $conn->prepare($sql_projects);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$projects = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['project_id']) && isset($_POST['content'])) {
    $project_id = $_POST['project_id'];
    $content = $_POST['content'];

    // Làm sạch dữ liệu đầu vào để bảo vệ khỏi XSS
    $content = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');

    // Thêm bình luận vào cơ sở dữ liệu, mặc định là chưa duyệt
    $stmt = $conn->prepare("INSERT INTO comments (project_id, user_id, content, is_approved) VALUES (?, ?, ?, 0)");
    $stmt->bind_param("iis", $project_id, $user_id, $content);
    $stmt->execute();
    
    // Sau khi thêm bình luận, thông báo thành công và reload lại trang
    echo "<script>alert('Bình luận đã được gửi, chờ duyệt!');</script>";
}

// Lấy bình luận đã duyệt cho mỗi dự án
$sql_comments = "SELECT * FROM comments WHERE is_approved = 1"; // Chỉ lấy bình luận đã duyệt
$stmt = $conn->prepare($sql_comments);
$stmt->execute();
$comments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

foreach ($projects as $project) {
    // Kiểm tra nếu người dùng có quyền bình luận (contributor, moderator, owner)
    $can_comment = in_array($project['role'], ['contributor', 'moderator', 'owner']);
    var_dump($can_comment);  // Kiểm tra và hiển thị giá trị của $can_comment
}

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
    <main>
        <h1>Dashboard</h1>

        <!-- Hiển thị tất cả các dự án mà người dùng tham gia -->
        <?php if (!empty($projects)): ?>
            <h2>Danh sách Dự Án</h2>
            <?php foreach ($projects as $project): ?>
                <div class="project-card">
                    <h3><?= htmlspecialchars($project['title']) ?></h3>
                    <p><?= htmlspecialchars($project['description']) ?></p>
                    
                    <!-- Hiển thị vai trò của người dùng trong dự án này -->
                    <p>Vai trò của bạn trong dự án này: <?= htmlspecialchars($project['role']) ?></p>

                    <!-- Bình luận của người dùng -->
                    <h5>Bình luận:</h5>
                    <?php foreach ($comments as $comment): ?>
                        <?php if ($comment['project_id'] == $project['project_id']): ?>
                            <p><strong><?= htmlspecialchars($comment['user_id']) ?>:</strong> <?= htmlspecialchars($comment['content']) ?></p>
                        <?php endif; ?>
                    <?php endforeach; ?>

                    <!-- Kiểm tra quyền bình luận của người dùng -->
                    <?php if ($can_comment): ?>
                        <form method="POST">
                            <input type="hidden" name="project_id" value="<?= $project['project_id'] ?>">
                            <textarea name="content" required placeholder="Thêm bình luận..."></textarea><br>
                            <button type="submit">Gửi bình luận</button>
                        </form>
                    <?php else: ?>
                        <!-- Thông báo nếu người dùng không có quyền bình luận -->
                        <p>Vì bạn có vai trò là <?= htmlspecialchars($project['role']) ?>, bạn không thể bình luận trong dự án này.</p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Hiện tại không có dự án nào để hiển thị.</p>
            <a href="create_project.php">Tạo dự án</a>
        <?php endif; ?>
        <a href="logout.php">Đăng xuất</a>
    </main>
</body>
</html>
