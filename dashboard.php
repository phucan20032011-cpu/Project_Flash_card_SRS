<?php
// dashboard.php - Trang chính: Danh sách bộ từ vựng
require_once __DIR__ . '/config/db.php';
requireLogin(); // Bắt buộc đăng nhập

$pageTitle = 'Dashboard';
$db        = getDB();
$userId    = $_SESSION['user_id'];

// Lấy danh sách bộ từ vựng của user (kèm số thẻ)
$stmt = $db->prepare("
    SELECT vs.*, COUNT(f.card_id) AS card_count
    FROM Vocabulary_Sets vs
    LEFT JOIN Flashcards f ON f.set_id = vs.set_id
    WHERE vs.user_id = ?
    GROUP BY vs.set_id
    ORDER BY vs.created_at DESC
");
$stmt->execute([$userId]);
$sets = $stmt->fetchAll();

// Đếm số thẻ cần ôn hôm nay (next_review_date <= hôm nay)
$stmtDue = $db->prepare("
    SELECT COUNT(*) AS due_count
    FROM SRS_Schedule s
    JOIN Flashcards f ON f.card_id = s.card_id
    JOIN Vocabulary_Sets vs ON vs.set_id = f.set_id
    WHERE s.user_id = ? AND s.next_review_date <= CURDATE()
");
$stmtDue->execute([$userId]);
$dueCount = $stmtDue->fetch()['due_count'];

// Đếm tổng số thẻ của user
$stmtCards = $db->prepare("
    SELECT COUNT(*) AS total
    FROM Flashcards f
    JOIN Vocabulary_Sets vs ON vs.set_id = f.set_id
    WHERE vs.user_id = ?
");
$stmtCards->execute([$userId]);
$totalCards = $stmtCards->fetch()['total'];

// Đếm số lần học hôm nay
$stmtToday = $db->prepare("
    SELECT COUNT(*) AS today
    FROM Study_History
    WHERE user_id = ? AND DATE(reviewed_at) = CURDATE()
");
$stmtToday->execute([$userId]);
$todayCount = $stmtToday->fetch()['today'];

include __DIR__ . '/views/header.php';
?>

<div class="container">
    <!-- Thống kê nhanh -->
    <div class="stats-row">
        <div class="stat-box">
            <div class="stat-icon purple">📚</div>
            <div class="stat-info">
                <div class="stat-number"><?= count($sets) ?></div>
                <div class="stat-label">Bộ từ vựng</div>
            </div>
        </div>
        <div class="stat-box">
            <div class="stat-icon blue">🃏</div>
            <div class="stat-info">
                <div class="stat-number"><?= $totalCards ?></div>
                <div class="stat-label">Tổng số thẻ</div>
            </div>
        </div>
        <div class="stat-box">
            <div class="stat-icon orange">⏰</div>
            <div class="stat-info">
                <div class="stat-number"><?= $dueCount ?></div>
                <div class="stat-label">Thẻ cần ôn hôm nay</div>
            </div>
        </div>
        <div class="stat-box">
            <div class="stat-icon green">✅</div>
            <div class="stat-info">
                <div class="stat-number"><?= $todayCount ?></div>
                <div class="stat-label">Đã học hôm nay</div>
            </div>
        </div>
    </div>

    <!-- Header trang + nút tạo mới -->
    <div class="page-header">
        <h1>📚 Bộ Từ Vựng Của Tôi</h1>
        <button class="btn btn-primary" onclick="openAddSet()">
            ➕ Tạo bộ từ mới
        </button>
    </div>

    <!-- Danh sách bộ từ vựng -->
    <?php if (empty($sets)): ?>
        <div class="empty-state">
            <div class="empty-icon">📭</div>
            <h3>Chưa có bộ từ vựng nào</h3>
            <p>Bắt đầu bằng cách tạo bộ từ vựng đầu tiên của bạn!</p>
            <button class="btn btn-primary" style="margin-top:1rem" onclick="openAddSet()">
                ➕ Tạo ngay
            </button>
        </div>
    <?php else: ?>
        <div class="sets-grid">
            <?php foreach ($sets as $set): ?>
                <div class="set-card" id="set-card-<?= $set['set_id'] ?>">
                    <!-- Click vào tên/mô tả để vào quản lý thẻ -->
                    <a href="/flashcard/cards.php?set_id=<?= $set['set_id'] ?>"
                       style="text-decoration:none; color:inherit; flex-grow:1;">
                        <h3><?= htmlspecialchars($set['title']) ?></h3>
                        <p><?= htmlspecialchars($set['description'] ?: 'Chưa có mô tả') ?></p>
                    </a>

                    <div class="set-meta">
                        <span class="badge badge-primary">🃏 <?= $set['card_count'] ?> thẻ</span>
                        <div class="set-actions" onclick="event.stopPropagation()">
                            <!-- Nút học ngay -->
                            <?php if ($set['card_count'] > 0): ?>
                                <a href="/flashcard/study.php?set_id=<?= $set['set_id'] ?>"
                                   class="btn btn-success btn-sm">▶ Học</a>
                            <?php endif; ?>
                            <!-- Nút sửa -->
                            <button class="btn btn-outline btn-sm"
                                onclick="openEditSet(<?= htmlspecialchars(json_encode([
                                    'set_id'      => $set['set_id'],
                                    'title'       => $set['title'],
                                    'description' => $set['description']
                                ])) ?>)">✏️</button>
                            <!-- Nút xóa -->
                            <button class="btn btn-danger btn-sm"
                                onclick="deleteSet(<?= $set['set_id'] ?>)">🗑️</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Modal thêm/sửa bộ từ vựng -->
<div id="modal-set" class="modal-overlay">
    <div class="modal-box">
        <h3 id="modal-title-set">➕ Tạo bộ từ vựng mới</h3>
        <form id="form-set" onsubmit="return false;">
            <input type="hidden" id="edit-set-id" name="set_id" value="">
            <div class="form-error alert alert-error" style="display:none;"></div>

            <div class="form-group">
                <label>Tên bộ từ vựng *</label>
                <input type="text" id="edit-set-title" name="title"
                       placeholder="VD: Tiếng Anh giao tiếp" required>
            </div>
            <div class="form-group">
                <label>Mô tả</label>
                <textarea id="edit-set-desc" name="description"
                          placeholder="Mô tả ngắn về bộ từ này..."></textarea>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-outline" onclick="closeModal('modal-set')">Hủy</button>
                <button type="button" class="btn btn-primary"
                    onclick="submitSetForm('form-set', '/flashcard/api/save_set.php')">
                    💾 Lưu
                </button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/views/footer.php'; ?>

<script>
// Mở modal thêm mới (xóa dữ liệu cũ)
function openAddSet() {
    document.getElementById('edit-set-id').value    = '';
    document.getElementById('edit-set-title').value = '';
    document.getElementById('edit-set-desc').value  = '';
    document.getElementById('modal-title-set').textContent = '➕ Tạo bộ từ vựng mới';
    // Ẩn thông báo lỗi cũ
    document.querySelector('#form-set .form-error').style.display = 'none';
    openModal('modal-set');
}
</script>
