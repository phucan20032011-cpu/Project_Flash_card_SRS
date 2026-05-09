<?php
// admin_users.php - Trang quản lý danh sách người dùng cho Admin
require_once __DIR__ . '/config/db.php';
requireAdmin();

$db = getDB();

// Lấy danh sách user (trừ các admin khác ra để tránh tự xóa nhau)
$stmt = $db->query("
    SELECT u.user_id, u.email, u.full_name, u.created_at,
           COUNT(DISTINCT vs.set_id) as total_sets,
           COUNT(DISTINCT f.card_id) as total_cards
    FROM Users u
    LEFT JOIN Vocabulary_Sets vs ON u.user_id = vs.user_id
    LEFT JOIN Flashcards f ON vs.set_id = f.set_id
    WHERE u.role = 'user'
    GROUP BY u.user_id
    ORDER BY u.created_at DESC
");
$users = $stmt->fetchAll();

$pageTitle = 'Quản lý User';
include __DIR__ . '/views/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>👥 Quản Lý Người Dùng</h1>
    </div>

    <div class="card">
        <div class="card-body" style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--border);">
                        <th style="padding: 1rem;">ID</th>
                        <th style="padding: 1rem;">Họ Tên</th>
                        <th style="padding: 1rem;">Email</th>
                        <th style="padding: 1rem;">Số bộ từ</th>
                        <th style="padding: 1rem;">Số thẻ</th>
                        <th style="padding: 1rem;">Ngày tham gia</th>
                        <th style="padding: 1rem; text-align: right;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="7" style="text-align:center; padding: 2rem;">Chưa có người dùng nào.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $u): ?>
                            <tr id="user-row-<?= $u['user_id'] ?>" style="border-bottom: 1px solid var(--border);">
                                <td style="padding: 1rem;">#<?= $u['user_id'] ?></td>
                                <td style="padding: 1rem; font-weight:600;"><?= htmlspecialchars($u['full_name']) ?></td>
                                <td style="padding: 1rem;"><?= htmlspecialchars($u['email']) ?></td>
                                <td style="padding: 1rem;"><span class="badge badge-primary"><?= $u['total_sets'] ?> bộ</span></td>
                                <td style="padding: 1rem;"><span class="badge badge-success"><?= $u['total_cards'] ?> thẻ</span></td>
                                <td style="padding: 1rem; color:var(--text-muted); font-size:0.9rem;"><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
                                <td style="padding: 1rem; text-align: right;">
                                    <button class="btn btn-danger btn-sm" onclick="deleteUser(<?= $u['user_id'] ?>)">🗑️ Xóa</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/views/footer.php'; ?>