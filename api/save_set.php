<?php
// api/save_set.php - Thêm mới hoặc cập nhật bộ từ vựng
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Phương thức không hợp lệ');
}

requireLogin(); // Bắt buộc đăng nhập

$input  = json_decode(file_get_contents('php://input'), true);
$userId = $_SESSION['user_id'];

$setId  = (int)($input['set_id']      ?? 0);
$title  = trim($input['title']        ?? '');
$desc   = trim($input['description']  ?? '');

if (empty($title)) {
    jsonResponse(false, 'Tên bộ từ vựng không được để trống!');
}

$db = getDB();

if ($setId > 0) {
    // === CẬP NHẬT bộ từ vựng hiện có ===
    // Kiểm tra bộ từ này có thuộc về user hiện tại không (bảo mật)
    $stmt = $db->prepare("UPDATE Vocabulary_Sets SET title = ?, description = ? WHERE set_id = ? AND user_id = ?");
    $stmt->execute([$title, $desc, $setId, $userId]);

    if ($stmt->rowCount() === 0) {
        jsonResponse(false, 'Không tìm thấy bộ từ vựng hoặc bạn không có quyền!');
    }

    jsonResponse(true, 'Đã cập nhật bộ từ vựng!', ['set_id' => $setId]);

} else {
    // === THÊM MỚI bộ từ vựng ===
    $stmt = $db->prepare("INSERT INTO Vocabulary_Sets (user_id, title, description) VALUES (?, ?, ?)");
    $stmt->execute([$userId, $title, $desc]);

    $newId = $db->lastInsertId();
    jsonResponse(true, 'Tạo bộ từ vựng thành công!', ['set_id' => $newId]);
}
