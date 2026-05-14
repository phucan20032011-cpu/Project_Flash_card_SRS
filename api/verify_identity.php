<?php
// api/verify_identity.php - Kiểm tra họ tên + email, trả về user_id nếu khớp
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Phương thức không hợp lệ');
}

$input     = json_decode(file_get_contents('php://input'), true);
$full_name = trim($input['full_name'] ?? '');
$email     = trim($input['email']     ?? '');

if (empty($full_name) || empty($email)) {
    jsonResponse(false, 'Vui lòng nhập đầy đủ thông tin!');
}

$db = getDB();

// Tìm user khớp cả email lẫn họ tên (không phân biệt hoa thường)
$stmt = $db->prepare("SELECT user_id FROM Users WHERE email = ? AND LOWER(full_name) = LOWER(?)");
$stmt->execute([$email, $full_name]);
$user = $stmt->fetch();

if (!$user) {
    jsonResponse(false, 'Không tìm thấy tài khoản với thông tin đã nhập!');
}

jsonResponse(true, 'Xác minh thành công!', ['user_id' => $user['user_id']]);
