<?php
// logout.php - Xử lý đăng xuất
require_once __DIR__ . '/../config/db.php';

// Xóa session
session_destroy();

// về trang đăng nhập
header('Location: /flashcard/index.php');
exit;
