<?php
// api/flag_card.php - Đánh dấu / bỏ đánh dấu thẻ cần xem lại
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Phương thức không hợp lệ');
}

requireLogin();

$input  = json_decode(file_get_contents('php://input'), true);
$cardId = (int)($input['card_id'] ?? 0);
$userId = $_SESSION['user_id'];

$db = getDB();

// Toggle is_flagged: 0 -> 1, 1 -> 0
$stmt = $db->prepare("
    UPDATE Flashcards f
    JOIN Vocabulary_Sets vs ON vs.set_id = f.set_id
    SET f.is_flagged = IF(f.is_flagged = 1, 0, 1)
    WHERE f.card_id = ? AND vs.user_id = ?
");
$stmt->execute([$cardId, $userId]);

// Lấy trạng thái mới
$stmtGet = $db->prepare("SELECT is_flagged FROM Flashcards WHERE card_id = ?");
$stmtGet->execute([$cardId]);
$newFlag = $stmtGet->fetch()['is_flagged'];

jsonResponse(true, $newFlag ? 'Đã đánh dấu!' : 'Đã bỏ đánh dấu!', ['is_flagged' => (bool)$newFlag]);
