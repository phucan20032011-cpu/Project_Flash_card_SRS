<?php
// api/delete_set.php - Xóa bộ từ vựng (cascade xóa cả thẻ)
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Phương thức không hợp lệ');
}

requireLogin();

$input  = json_decode(file_get_contents('php://input'), true);
$setId  = (int)($input['set_id'] ?? 0);
$userId = $_SESSION['user_id'];

if ($setId <= 0) {
    jsonResponse(false, 'ID không hợp lệ');
}

$db = getDB();

// Xóa bộ từ vựng - ON DELETE CASCADE trong DB sẽ tự xóa các thẻ liên quan
$stmt = $db->prepare("DELETE FROM Vocabulary_Sets WHERE set_id = ? AND user_id = ?");
$stmt->execute([$setId, $userId]);

if ($stmt->rowCount() === 0) {
    jsonResponse(false, 'Không tìm thấy bộ từ hoặc bạn không có quyền!');
}

jsonResponse(true, 'Đã xóa bộ từ vựng!');
