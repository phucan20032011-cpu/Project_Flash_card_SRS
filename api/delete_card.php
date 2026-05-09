<?php
// delete_card.php - Xóa một flashcard
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Phương thức không hợp lệ');
}

requireLogin();

$input  = json_decode(file_get_contents('php://input'), true);
$cardId = (int)($input['card_id'] ?? 0);
$userId = $_SESSION['user_id'];

if ($cardId <= 0) {
    jsonResponse(false, 'ID thẻ không hợp lệ');
}

$db = getDB();

// Xóa thẻ
$stmt = $db->prepare("
    DELETE f FROM Flashcards f
    JOIN Vocabulary_Sets vs ON vs.set_id = f.set_id
    WHERE f.card_id = ? AND vs.user_id = ?
");
$stmt->execute([$cardId, $userId]);

if ($stmt->rowCount() === 0) {
    jsonResponse(false, 'Không tìm thấy thẻ hoặc bạn không có quyền!');
}

jsonResponse(true, 'Đã xóa flashcard!');
