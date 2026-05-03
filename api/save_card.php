<?php
// api/save_card.php - Thêm mới hoặc cập nhật flashcard
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Phương thức không hợp lệ');
}

requireLogin();

$input  = json_decode(file_get_contents('php://input'), true);
$userId = $_SESSION['user_id'];

$cardId    = (int)($input['card_id']    ?? 0);
$setId     = (int)($input['set_id']     ?? 0);
$frontText = trim($input['front_text']  ?? '');
$backText  = trim($input['back_text']   ?? '');
$tags      = trim($input['tags']        ?? '');

// Kiểm tra dữ liệu bắt buộc
if (empty($frontText) || empty($backText)) {
    jsonResponse(false, 'Mặt trước và mặt sau không được để trống!');
}

$db = getDB();

if ($cardId > 0) {
    // === CẬP NHẬT thẻ hiện có ===
    // Kiểm tra thẻ thuộc về user này (qua Vocabulary_Sets)
    $stmt = $db->prepare("
        UPDATE Flashcards f
        JOIN Vocabulary_Sets vs ON vs.set_id = f.set_id
        SET f.front_text = ?, f.back_text = ?, f.tags = ?
        WHERE f.card_id = ? AND vs.user_id = ?
    ");
    $stmt->execute([$frontText, $backText, $tags, $cardId, $userId]);

    if ($stmt->rowCount() === 0) {
        jsonResponse(false, 'Không tìm thấy thẻ hoặc bạn không có quyền!');
    }

    jsonResponse(true, 'Đã cập nhật flashcard!');

} else {
    // === THÊM FLASHCARD MỚI ===

    // Kiểm tra bộ từ này thuộc về user
    $stmtCheck = $db->prepare("SELECT set_id FROM Vocabulary_Sets WHERE set_id = ? AND user_id = ?");
    $stmtCheck->execute([$setId, $userId]);
    if (!$stmtCheck->fetch()) {
        jsonResponse(false, 'Bộ từ vựng không hợp lệ!');
    }

    // Thêm thẻ mới vào Flashcards
    $stmt = $db->prepare("INSERT INTO Flashcards (set_id, front_text, back_text, tags) VALUES (?, ?, ?, ?)");
    $stmt->execute([$setId, $frontText, $backText, $tags]);
    $newCardId = $db->lastInsertId();

    // Tạo bản ghi SRS ban đầu cho user (box = 1, ôn ngay hôm nay)
    $stmtSRS = $db->prepare("
        INSERT INTO SRS_Schedule (card_id, user_id, box_level, easiness_factor, next_review_date)
        VALUES (?, ?, 1, 2.5, CURDATE())
        ON DUPLICATE KEY UPDATE box_level = box_level -- không làm gì nếu đã tồn tại
    ");
    $stmtSRS->execute([$newCardId, $userId]);

    jsonResponse(true, 'Thêm flashcard thành công!', ['card_id' => $newCardId]);
}
