<?php
// api/update_srs.php - Cập nhật thuật toán SRS sau khi user đánh giá thẻ
// Đây là file QUAN TRỌNG NHẤT của hệ thống
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Phương thức không hợp lệ');
}

requireLogin();

$input  = json_decode(file_get_contents('php://input'), true);
$cardId = (int)($input['card_id'] ?? 0);
$grade  = (int)($input['grade']   ?? 0); // 0=Quên, 1=Khó, 2=Khá, 3=Nhớ
$userId = $_SESSION['user_id'];

if ($cardId <= 0 || $grade < 0 || $grade > 3) {
    jsonResponse(false, 'Dữ liệu không hợp lệ!');
}

$db = getDB();

// Lấy thông tin SRS hiện tại của thẻ này
$stmt = $db->prepare("SELECT * FROM SRS_Schedule WHERE card_id = ? AND user_id = ?");
$stmt->execute([$cardId, $userId]);
$srs  = $stmt->fetch();

// Nếu chưa có bản ghi SRS, tạo mặc định
$currentBox = $srs ? (int)$srs['box_level'] : 1;

// =============================================
// THUẬT TOÁN LEITNER ĐƠN GIẢN
// - Quên (grade=0): về Hộp 1, ôn lại ngày mai
// - Khó  (grade=1): Hộp + 1, ôn sau 2^(box-1) ngày
// - Khá  (grade=2): Hộp + 1, ôn sau 2^(box-1) ngày
// - Nhớ  (grade=3): Hộp + 1, ôn sau 2^(box-1) ngày (khoảng cách dài hơn)
// =============================================

if ($grade === 0) {
    // Quên: reset về hộp 1, ôn lại ngày mai
    $newBox      = 1;
    $intervalDays = 1;
} else {
    // Nhớ ở mức nào đó: tăng hộp lên 1 (tối đa hộp 10)
    $newBox = min($currentBox + 1, 10);

    // Khoảng cách ôn = 2^(box hiện tại - 1) ngày
    // Hộp 1 -> 1 ngày, Hộp 2 -> 2 ngày, Hộp 3 -> 4 ngày,
    // Hộp 4 -> 8 ngày, Hộp 5 -> 16 ngày ...
    $intervalDays = (int)pow(2, $newBox - 1);

    // Grade 1 (Khó): giảm khoảng cách xuống 1 nửa
    if ($grade === 1) {
        $intervalDays = max(1, (int)($intervalDays / 2));
    }
}

// Tính ngày ôn tiếp theo
$nextReviewDate = date('Y-m-d', strtotime("+{$intervalDays} days"));

if ($srs) {
    // Cập nhật bản ghi SRS đã có
    $stmt = $db->prepare("
        UPDATE SRS_Schedule
        SET box_level = ?, next_review_date = ?
        WHERE card_id = ? AND user_id = ?
    ");
    $stmt->execute([$newBox, $nextReviewDate, $cardId, $userId]);
} else {
    // Tạo bản ghi SRS mới (lần đầu học thẻ này)
    $stmt = $db->prepare("
        INSERT INTO SRS_Schedule (card_id, user_id, box_level, easiness_factor, next_review_date)
        VALUES (?, ?, ?, 2.5, ?)
    ");
    $stmt->execute([$cardId, $userId, $newBox, $nextReviewDate]);
}

// Lưu lịch sử học vào Study_History
$stmtHist = $db->prepare("
    INSERT INTO Study_History (user_id, card_id, response_grade)
    VALUES (?, ?, ?)
");
$stmtHist->execute([$userId, $cardId, $grade]);

// Tạo thông báo thân thiện cho người dùng
$gradeLabels = ['Quên', 'Khó', 'Khá', 'Nhớ tốt'];
$msg = sprintf(
    '%s → Hộp %d | Ôn lại sau %d ngày (%s)',
    $gradeLabels[$grade],
    $newBox,
    $intervalDays,
    $nextReviewDate
);

jsonResponse(true, $msg, [
    'new_box'          => $newBox,
    'next_review_date' => $nextReviewDate,
    'interval_days'    => $intervalDays
]);
