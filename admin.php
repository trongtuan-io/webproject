<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

// Lấy danh sách tất cả người dùng từ bảng users
$sql_users = "SELECT * FROM users";
$stmt = $conn->prepare($sql_users);
$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Lấy tất cả dự án (đã duyệt) để có thể quản lý quyền người dùng trong từng dự án
$sql_projects = "SELECT * FROM projects WHERE is_approved = 1";  // Lấy các dự án đã duyệt
$stmt = $conn->prepare($sql_projects);
$stmt->execute();
$projects = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Nếu có hành động thay đổi quyền, xử lý cập nhật
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['user_id']) && isset($_POST['project_id']) && isset($_POST['role'])) {
    $user_id = $_POST['user_id'];
    $project_id = $_POST['project_id'];
    $role = $_POST['role'];

    // Cập nhật vai trò của người dùng trong dự án
    $stmt = $conn->prepare("UPDATE project_members SET role = ? WHERE user_id = ? AND project_id = ?");
    $stmt->bind_param("sii", $role, $user_id, $project_id);
    $stmt->execute();
    echo "Cập nhật quyền thành công!";
}



// Lấy danh sách bình luận chưa duyệt
$sql_comments = "SELECT c.id, c.content, c.project_id, c.user_id, u.email FROM comments c
                 JOIN users u ON c.user_id = u.id
                 WHERE c.is_approved = 0";
$stmt = $conn->prepare($sql_comments);
$stmt->execute();
$comments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Duyệt hoặc từ chối bình luận
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment_id']) && isset($_POST['action'])) {
    $comment_id = $_POST['comment_id'];
    $action = $_POST['action']; // 'approve' hoặc 'reject'
    $is_approved = ($action == 'approve') ? 1 : 0;

    // Cập nhật trạng thái duyệt bình luận
    $stmt = $conn->prepare("UPDATE comments SET is_approved = ? WHERE id = ?");
    $stmt->bind_param("ii", $is_approved, $comment_id);
    $stmt->execute();
    echo "Cập nhật trạng thái bình luận thành công!";
}



?>

<h1>Quản lý người dùng và quyền</h1>

<?php foreach ($projects as $project): ?>
    <h2>Dự án: <?= htmlspecialchars($project['title']) ?></h2>

    <!-- Hiển thị danh sách người dùng và quyền của họ trong dự án này -->
    <table>
        <thead>
            <tr>
                <th>Người dùng</th>
                <th>Vai trò hiện tại</th>
                <th>Cập nhật quyền</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Lấy danh sách người dùng tham gia dự án này
            $sql_members = "SELECT u.id, u.email, pm.role FROM project_members pm
                            JOIN users u ON pm.user_id = u.id
                            WHERE pm.project_id = ?";
            $stmt = $conn->prepare($sql_members);
            $stmt->bind_param("i", $project['id']);
            $stmt->execute();
            $members = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            foreach ($members as $member):
            ?>
                <tr>
                    <td><?= htmlspecialchars($member['email']) ?></td>
                    <td><?= htmlspecialchars($member['role']) ?></td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="user_id" value="<?= $member['id'] ?>">
                            <input type="hidden" name="project_id" value="<?= $project['id'] ?>">
                            <select name="role">
                                <option value="viewer" <?= $member['role'] == 'viewer' ? 'selected' : '' ?>>Người xem</option>
                                <option value="contributor" <?= $member['role'] == 'contributor' ? 'selected' : '' ?>>Người đóng góp</option>
                                <option value="moderator" <?= $member['role'] == 'moderator' ? 'selected' : '' ?>>Người điều hành</option>
                                <option value="owner" <?= $member['role'] == 'owner' ? 'selected' : '' ?>>Chủ sở hữu</option>
                            </select>
                            <button type="submit">Cập nhật quyền</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endforeach; ?>



<h1>Duyệt Bình luận</h1>

<table>
    <thead>
        <tr>
            <th>Bình luận</th>
            <th>Người dùng</th>
            <th>Hành động</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($comments as $comment): ?>
            <tr>
                <td><?= htmlspecialchars($comment['content']) ?></td>
                <td><?= htmlspecialchars($comment['email']) ?></td>
                <td>
                    <!-- Form duyệt hoặc từ chối bình luận -->
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                        <button type="submit" name="action" value="approve">Duyệt</button>
                    </form>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                        <button type="submit" name="action" value="reject">Từ chối</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<a href="logout.php">Đăng xuất</a>