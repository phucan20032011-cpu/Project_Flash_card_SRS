<?php
// api/delete_user.php - Admin xóa người dùng
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Phương thức không hợp lệ');
}

requireAdmin(); // Bắt buộc là Admin mới được gọi API này

$input  = json_decode(file_get_contents('php://input'), true);
$targetUserId = (int)($input['user_id'] ?? 0);

if ($targetUserId <= 0) {
    jsonResponse(false, 'ID người dùng không hợp lệ');
}

$db = getDB();

// Thực hiện xóa (Cascade sẽ tự động xóa Vocabulary_Sets và Flashcards của người này)
$stmt = $db->prepare("DELETE FROM Users WHERE user_id = ? AND role = 'user'");
$stmt->execute([$targetUserId]);

if ($stmt->rowCount() === 0) {
    jsonResponse(false, 'Không tìm thấy người dùng hoặc không thể xóa Admin!');
}

jsonResponse(true, 'Đã xóa tài khoản và toàn bộ dữ liệu của người dùng này!');
