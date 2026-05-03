<?php
// api/logout.php - Xử lý đăng xuất
require_once __DIR__ . '/../config/db.php';

// Xóa toàn bộ session
session_destroy();

// Chuyển về trang đăng nhập
header('Location: /flashcard/index.php');
exit;
