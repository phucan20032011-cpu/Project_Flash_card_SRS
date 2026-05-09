<?php
// study.php - Trang học: Lật thẻ + Đánh giá SRS
require_once __DIR__ . '/config/db.php';
requireLogin();

$db     = getDB();
$userId = $_SESSION['user_id'];
$setId  = (int)($_GET['set_id'] ?? 0);

// Kiểm tra bộ từ hợp lệ
$stmt = $db->prepare("SELECT * FROM Vocabulary_Sets WHERE set_id = ? AND user_id = ?");
$stmt->execute([$setId, $userId]);
$set = $stmt->fetch();

if (!$set) {
    header('Location: /flashcard/dashboard.php');
    exit;
}

// Lấy các thẻ cần ôn hôm nay theo thuật toán SRS
// Ưu tiên: thẻ chưa có SRS (mới thêm) + thẻ đến hạn ôn
$stmt = $db->prepare("
    SELECT f.card_id, f.front_text, f.back_text, f.tags,
           COALESCE(s.box_level, 1)               AS box_level,
           COALESCE(s.next_review_date, CURDATE()) AS next_review_date
    FROM Flashcards f
    LEFT JOIN SRS_Schedule s ON s.card_id = f.card_id AND s.user_id = ?
    WHERE f.set_id = ?
      AND (s.next_review_date IS NULL OR s.next_review_date <= CURDATE())
    ORDER BY COALESCE(s.box_level, 1) ASC, RAND()
    LIMIT 50
");
$stmt->execute([$userId, $setId]);
$dueCards = $stmt->fetchAll();

// Nếu không có thẻ nào cần ôn hôm nay
$noCardsToday = empty($dueCards);

$pageTitle = 'Học: ' . htmlspecialchars($set['title']);
include __DIR__ . '/views/header.php';
?>

<style>
    /* CSS riêng cho trang học */
    .study-wrapper {
        min-height: calc(100vh - 60px);
    }

    #study-done {
        display: none;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 60vh;
    }
</style>

<div class="study-wrapper container">
    <!-- Breadcrumb -->
    <p style="font-size:0.88rem; color:var(--text-muted); margin-bottom:1rem;">
        <a href="/flashcard/dashboard.php" style="color:var(--primary);">📚 Bộ từ</a>
        &rsaquo;
        <a href="/flashcard/cards.php?set_id=<?= $setId ?>" style="color:var(--primary);">
            <?= htmlspecialchars($set['title']) ?>
        </a>
        &rsaquo; Học
    </p>

    <?php if ($noCardsToday): ?>
        <!-- Không có thẻ cần ôn hôm nay -->
        <div class="study-done" style="display:flex;">
            <div class="done-icon"></div>
            <h2>Tuyệt vời! Bạn đã ôn xong hôm nay!</h2>
            <p>Không có thẻ nào đến hạn ôn hôm nay. Hãy quay lại vào ngày mai!</p>
            <div style="display:flex; gap:1rem; flex-wrap:wrap; justify-content:center;">
                <a href="/flashcard/cards.php?set_id=<?= $setId ?>" class="btn btn-outline">
                    Xem tất cả thẻ
                </a>
                <a href="/flashcard/dashboard.php" class="btn btn-primary">
                    Về trang chủ
                </a>
            </div>
        </div>

    <?php else: ?>

        <!-- === KHU VỰC HỌC CHÍNH === -->
        <div id="study-area">

            <!-- Thanh tiến độ -->
            <div class="study-progress">
                <div style="display:flex; justify-content:space-between; font-size:0.85rem; color:var(--text-muted); margin-bottom:0.4rem;">
                    <span>Tiến độ buổi học</span>
                    <span id="progress-text">0 / <?= count($dueCards) ?> thẻ</span>
                </div>
                <div class="progress-bar-wrap">
                    <div class="progress-bar-fill" id="progress-fill" style="width:0%"></div>
                </div>
            </div>

            <!-- Thẻ lật 3D - Click để lật -->
            <div class="flip-scene" onclick="flipCard()" title="Click để xem đáp án">
                <div class="flip-card" id="flip-card">

                    <!-- Mặt trước: câu hỏi / từ cần học -->
                    <div class="flip-front">

                        <div class="card-text" id="front-text">...</div>

                    </div>

                    <!-- Mặt sau: đáp án / nghĩa -->
                    <div class="flip-back">
                        <div class="card-label" style="color:rgba(255,255,255,0.7);">Đáp án</div>
                        <div class="card-text" id="back-text">...</div>
                    </div>

                </div>
            </div>

            <!-- Nút đánh giá SRS - vô hiệu hóa cho đến khi lật thẻ -->
            <div class="rating-buttons">

                <!-- Grade 0: Quên hoàn toàn -> box_level = 1, ôn lại ngày mai -->
                <button class="rating-btn btn-forget" disabled onclick="rateCard(0)">
                    <span class="btn-emoji"></span>
                    <span>Quên</span>
                    <span class="btn-day">Ôn lại ngày mai</span>
                </button>

                <!-- Grade 1: Khó -> tăng 1 box, ôn 2 ngày sau -->
                <button class="rating-btn btn-hard" disabled onclick="rateCard(1)">
                    <span class="btn-emoji"></span>
                    <span>Khó</span>
                    <span class="btn-day">~2 ngày</span>
                </button>

                <!-- Grade 2: Khá -> tăng 1 box, ôn 4 ngày sau -->
                <button class="rating-btn btn-good" disabled onclick="rateCard(2)">
                    <span class="btn-emoji"></span>
                    <span>Khá</span>
                    <span class="btn-day">~4 ngày</span>
                </button>

                <!-- Grade 3: Nhớ tốt -> tăng 1 box, ôn theo cấp số nhân -->
                <button class="rating-btn btn-easy" disabled onclick="rateCard(3)">
                    <span class="btn-emoji"></span>
                    <span>Nhớ tốt</span>
                    <span class="btn-day">~8+ ngày</span>
                </button>

            </div>

            <!-- Hướng dẫn nhỏ -->
            <p style="text-align:center; color:var(--text-muted); font-size:0.82rem;">
                Hãy lật thẻ trước khi đánh giá mức độ ghi nhớ
            </p>
        </div>

        <!-- === MÀN HÌNH HOÀN THÀNH === -->
        <div id="study-done">
            <div class="done-icon"></div>
            <h2>Hoàn thành buổi học!</h2>
            <p id="done-message">Bạn đã học xong <?= count($dueCards) ?> thẻ hôm nay. Tiếp tục phát huy nhé!</p>
            <div style="display:flex; gap:1rem; flex-wrap:wrap; justify-content:center; margin-top:1.5rem;">
                <a href="/flashcard/study.php?set_id=<?= $setId ?>" class="btn btn-success">
                    Học lại
                </a>
                <a href="/flashcard/stats.php" class="btn btn-outline">
                    Xem thống kê
                </a>
                <a href="/flashcard/dashboard.php" class="btn btn-primary">
                    Về trang chủ
                </a>
            </div>
        </div>

    <?php endif; ?>
</div>

<?php include __DIR__ . '/views/footer.php'; ?>

<?php if (!$noCardsToday): ?>
    <script>
        // Truyền dữ liệu thẻ từ PHP sang JavaScript
        // json_encode đã tự xử lý escape, htmlspecialchars để bảo vệ thêm
        const studyData = <?= json_encode(array_map(function ($c) {
                                return [
                                    'card_id'    => (int)$c['card_id'],
                                    'front_text' => $c['front_text'],
                                    'back_text'  => $c['back_text'],
                                    'box_level'  => (int)$c['box_level']
                                ];
                            }, $dueCards)) ?>;

        // Khởi tạo buổi học ngay khi trang load xong
        document.addEventListener('DOMContentLoaded', () => {
            initStudy(studyData);
        });
    </script>
<?php endif; ?>