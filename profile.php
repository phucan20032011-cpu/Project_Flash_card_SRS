<?php
// profile.php - Xem thông tin cá nhân (chỉ xem, không sửa)
// Đặt ở thư mục gốc: flashcard/
require_once __DIR__ . '/config/db.php';
requireLogin();

$db     = getDB();
$userId = $_SESSION['user_id'];

// Lấy thông tin user
$stmt = $db->prepare("SELECT email, full_name, role, created_at FROM Users WHERE user_id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

// Thống kê học tập (chỉ cho user thường)
$totalSets    = 0;
$totalCards   = 0;
$totalReviews = 0;

if ($user['role'] === 'user') {
    $stmt = $db->prepare("SELECT COUNT(*) FROM Vocabulary_Sets WHERE user_id = ?");
    $stmt->execute([$userId]);
    $totalSets = $stmt->fetchColumn();

    $stmt = $db->prepare("
        SELECT COUNT(*) FROM Flashcards f
        JOIN Vocabulary_Sets vs ON vs.set_id = f.set_id
        WHERE vs.user_id = ?
    ");
    $stmt->execute([$userId]);
    $totalCards = $stmt->fetchColumn();

    $stmt = $db->prepare("SELECT COUNT(*) FROM Study_History WHERE user_id = ?");
    $stmt->execute([$userId]);
    $totalReviews = $stmt->fetchColumn();
}

$pageTitle = 'Hồ sơ cá nhân';
include __DIR__ . '/views/header.php';
?>

<div class="container" style="max-width: 600px;">
    <div class="page-header">
        <h1>Hồ Sơ Cá Nhân</h1>
        <a href="/flashcard/dashboard.php" class="btn btn-outline">← Quay lại</a>
    </div>

    <div class="card">
        <div class="card-body">

            <!-- Avatar + tên -->
            <div style="text-align:center; padding: 1rem 0 1.5rem;">
                <div style="
                    width: 72px; height: 72px; border-radius: 50%;
                    background: linear-gradient(135deg, var(--primary), #7c3aed);
                    color: white; font-size: 1.8rem; font-weight: 700;
                    display: inline-flex; align-items: center; justify-content: center;
                    margin-bottom: 0.75rem;
                ">
                    <?= strtoupper(mb_substr($user['full_name'], 0, 1)) ?>
                </div>
                <div style="font-size: 1.2rem; font-weight: 700;"><?= htmlspecialchars($user['full_name']) ?></div>
                <div style="font-size: 0.85rem; color: var(--text-muted);">
                    <?= $user['role'] === 'admin' ? 'Quản trị viên' : 'Học viên' ?>
                </div>
            </div>

            <!-- Thông tin chi tiết -->
            <div style="display: flex; flex-direction: column; gap: 0; border-top: 1px solid var(--border);">

                <div style="display:flex; justify-content:space-between; padding: 0.9rem 0; border-bottom: 1px solid var(--border);">
                    <span style="color: var(--text-muted); font-size: 0.88rem;">Email</span>
                    <span style="font-weight: 600; font-size: 0.92rem;"><?= htmlspecialchars($user['email']) ?></span>
                </div>

                <div style="display:flex; justify-content:space-between; padding: 0.9rem 0; border-bottom: 1px solid var(--border);">
                    <span style="color: var(--text-muted); font-size: 0.88rem;">Họ tên</span>
                    <span style="font-weight: 600; font-size: 0.92rem;"><?= htmlspecialchars($user['full_name']) ?></span>
                </div>

                <div style="display:flex; justify-content:space-between; padding: 0.9rem 0; border-bottom: 1px solid var(--border);">
                    <span style="color: var(--text-muted); font-size: 0.88rem;">Vai trò</span>
                    <span style="font-weight: 600; font-size: 0.92rem;">
                        <?= $user['role'] === 'admin' ? 'Quản trị viên' : 'Học viên' ?>
                    </span>
                </div>

                <div style="display:flex; justify-content:space-between; padding: 0.9rem 0;
                    <?= $user['role'] === 'user' ? 'border-bottom: 1px solid var(--border);' : '' ?>">
                    <span style="color: var(--text-muted); font-size: 0.88rem;">Ngày tham gia</span>
                    <span style="font-weight: 600; font-size: 0.92rem;">
                        <?= date('d/m/Y', strtotime($user['created_at'])) ?>
                    </span>
                </div>

                <?php if ($user['role'] === 'user'): ?>
                    <div style="display:flex; justify-content:space-between; padding: 0.9rem 0; border-bottom: 1px solid var(--border);">
                        <span style="color: var(--text-muted); font-size: 0.88rem;">Bộ từ vựng</span>
                        <span style="font-weight: 600; font-size: 0.92rem;"><?= $totalSets ?> bộ</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; padding: 0.9rem 0; border-bottom: 1px solid var(--border);">
                        <span style="color: var(--text-muted); font-size: 0.88rem;">Tổng số thẻ</span>
                        <span style="font-weight: 600; font-size: 0.92rem;"><?= $totalCards ?> thẻ</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; padding: 0.9rem 0;">
                        <span style="color: var(--text-muted); font-size: 0.88rem;">Tổng lượt ôn</span>
                        <span style="font-weight: 600; font-size: 0.92rem;"><?= $totalReviews ?> lượt</span>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/views/footer.php'; ?>