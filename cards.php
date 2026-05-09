<?php
// cards.php - Trang quản lý flashcard 
require_once __DIR__ . '/config/db.php';
requireLogin();

$db     = getDB();
$userId = $_SESSION['user_id'];
$setId  = (int)($_GET['set_id'] ?? 0);

// thông tin bộ từ vựng và kiểm tra quyền 
$stmt = $db->prepare("SELECT * FROM Vocabulary_Sets WHERE set_id = ? AND user_id = ?");
$stmt->execute([$setId, $userId]);
$set  = $stmt->fetch();

// Nếu không tìm thấy hoặc không có quyền -> về dashboard
if (!$set) {
    header('Location: /flashcard/dashboard.php');
    exit;
}

// Lấy danh sách thẻ trong bộ (sắp xếp: thẻ cần ôn trước)
$stmt = $db->prepare("
    SELECT f.*,
           COALESCE(s.box_level, 1)           AS box_level,
           COALESCE(s.next_review_date, CURDATE()) AS next_review_date
    FROM Flashcards f
    LEFT JOIN SRS_Schedule s ON s.card_id = f.card_id AND s.user_id = ?
    WHERE f.set_id = ?
    ORDER BY f.is_flagged DESC, f.created_at DESC
");
$stmt->execute([$userId, $setId]);
$cards = $stmt->fetchAll();

$pageTitle = htmlspecialchars($set['title']);
include __DIR__ . '/views/header.php';
?>

<div class="container">
    <!-- Breadcrumb -->
    <p style="margin-bottom:1rem; font-size:0.88rem; color:var(--text-muted);">
        <a href="/flashcard/dashboard.php" style="color:var(--primary);">Bộ từ vựng</a>
        &rsaquo; <?= htmlspecialchars($set['title']) ?>
    </p>

    <div class="page-header">
        <div>
            <h1><?= htmlspecialchars($set['title']) ?></h1>
            <?php if ($set['description']): ?>
                <p style="color:var(--text-muted); margin-top:0.3rem;">
                    <?= htmlspecialchars($set['description']) ?>
                </p>
            <?php endif; ?>
        </div>
        <div style="display:flex; gap:0.75rem; flex-wrap:wrap;">
            <?php if (!empty($cards)): ?>
                <a href="/flashcard/study.php?set_id=<?= $setId ?>"
                    class="btn btn-success">▶ Học ngay</a>
            <?php endif; ?>
            <button class="btn btn-primary" onclick="openAddCard()">Thêm thẻ</button>
        </div>
    </div>

    <!-- Danh sách thẻ -->
    <?php if (empty($cards)): ?>
        <div class="empty-state">
            <div class="empty-icon">📭</div>
            <h3>Chưa có thẻ nào trong bộ này</h3>
            <p>Thêm flashcard đầu tiên để bắt đầu học!</p>
            <button class="btn btn-primary" style="margin-top:1rem" onclick="openAddCard()">
                Thêm thẻ
            </button>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="card-header">
                <h2><?= count($cards) ?> flashcard</h2>
                <span style="font-size:0.85rem; color:var(--text-muted);">
                    Click để đánh dấu thẻ cần xem lại
                </span>
            </div>
            <div class="card-body" style="padding:1rem;">
                <div class="flashcard-list">
                    <?php foreach ($cards as $card): ?>
                        <div class="flashcard-item <?= $card['is_flagged'] ? 'flagged' : '' ?>"
                            id="card-row-<?= $card['card_id'] ?>">

                            <!-- Nội dung thẻ -->
                            <div class="flashcard-texts">
                                <div class="front">
                                    <?= htmlspecialchars($card['front_text']) ?>
                                </div>
                                <div class="back">
                                    <?= htmlspecialchars($card['back_text']) ?>
                                </div>
                                <?php if ($card['tags']): ?>
                                    <div class="flashcard-tags">
                                        <?php foreach (explode(',', $card['tags']) as $tag): ?>
                                            <span class="tag"><?= htmlspecialchars(trim($tag)) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Thông tin SRS -->
                            <div style="text-align:center; min-width:80px; font-size:0.78rem; color:var(--text-muted);">
                                <div style="font-weight:700; color:var(--primary);">Hộp <?= $card['box_level'] ?></div>
                                <div>Ôn: <?= $card['next_review_date'] ?></div>
                            </div>

                            <!-- Các nút hành động -->
                            <div style="display:flex; gap:0.4rem; flex-shrink:0;">
                                <!-- Nút đánh dấu -->
                                <button class="btn btn-outline btn-sm"
                                    title="<?= $card['is_flagged'] ? 'Bỏ đánh dấu' : 'Đánh dấu cần xem lại' ?>"
                                    onclick="toggleFlag(<?= $card['card_id'] ?>, this)">
                                    <?= $card['is_flagged'] ? '' : '⚑' ?>
                                </button>
                                <!-- Nút sửa -->
                                <button class="btn btn-outline btn-sm"
                                    onclick="openEditCard(<?= htmlspecialchars(json_encode([
                                                                'card_id'    => $card['card_id'],
                                                                'front_text' => $card['front_text'],
                                                                'back_text'  => $card['back_text'],
                                                                'tags'       => $card['tags']
                                                            ])) ?>)">✏️</button>
                                <!-- Nút xóa -->
                                <button class="btn btn-danger btn-sm"
                                    onclick="deleteCard(<?= $card['card_id'] ?>)">Xóa</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Modal thêm / sửa flashcard -->
<div id="modal-card" class="modal-overlay">
    <div class="modal-box">
        <h3 id="modal-title-card">Thêm Flashcard mới</h3>
        <form id="form-card" onsubmit="return false;">
            <input type="hidden" id="edit-card-id" name="card_id" value="">
            <input type="hidden" name="set_id" value="<?= $setId ?>">
            <div class="form-error alert alert-error" style="display:none;"></div>

            <div class="form-group">
                <label>Mặt trước (Câu hỏi / Từ cần học) *</label>
                <textarea id="edit-front-text" name="front_text"
                    placeholder="VD: Apple" required></textarea>
            </div>
            <div class="form-group">
                <label>Mặt sau (Đáp án / Nghĩa) *</label>
                <textarea id="edit-back-text" name="back_text"
                    placeholder="VD: Táo - loại quả màu đỏ hoặc xanh" required></textarea>
            </div>
            <div class="form-group">
                <label>Tags (phân cách bằng dấu phẩy)</label>
                <input type="text" id="edit-tags" name="tags"
                    placeholder="VD: danh từ, đồ ăn, A1">
            </div>

            <div class="modal-actions">
                <button type="button" class="btn btn-outline" onclick="closeModal('modal-card')">Hủy</button>
                <button type="button" class="btn btn-primary"
                    onclick="submitCardForm('form-card', '/flashcard/api/save_card.php')">
                    Lưu thẻ
                </button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/views/footer.php'; ?>

<script>
    // Mở modal thêm thẻ mới (xóa sạch dữ liệu cũ)
    function openAddCard() {
        document.getElementById('edit-card-id').value = '';
        document.getElementById('edit-front-text').value = '';
        document.getElementById('edit-back-text').value = '';
        document.getElementById('edit-tags').value = '';
        document.getElementById('modal-title-card').textContent = 'Thêm Flashcard mới';
        document.querySelector('#form-card .form-error').style.display = 'none';
        openModal('modal-card');
    }
</script>