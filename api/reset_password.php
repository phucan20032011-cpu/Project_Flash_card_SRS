<?php
// api/reset_password.php - Cập nhật mật khẩu mới sau khi đã xác minh danh tính
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Phương thức không hợp lệ');
}

$input        = json_decode(file_get_contents('php://input'), true);
$userId       = (int)($input['user_id']      ?? 0);
$newPassword  =       $input['new_password'] ?? '';

if ($userId <= 0 || empty($newPassword)) {
    jsonResponse(false, 'Dữ liệu không hợp lệ!');
}

if (strlen($newPassword) < 6) {
    jsonResponse(false, 'Mật khẩu phải có ít nhất 6 ký tự!');
}

$db = getDB();

// Kiểm tra user tồn tại
$stmt = $db->prepare("SELECT user_id FROM Users WHERE user_id = ?");
$stmt->execute([$userId]);
if (!$stmt->fetch()) {
    jsonResponse(false, 'Tài khoản không hợp lệ!');
}

// Cập nhật mật khẩu
$hash = password_hash($newPassword, PASSWORD_DEFAULT);
$stmt = $db->prepare("UPDATE Users SET password_hash = ? WHERE user_id = ?");
$stmt->execute([$hash, $userId]);

jsonResponse(true, 'Đặt lại mật khẩu thành công!');
