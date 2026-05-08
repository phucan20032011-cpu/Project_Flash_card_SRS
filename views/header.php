<?php
// views/header.php - Header tái sử dụng cho mọi trang
// Biến $pageTitle phải được đặt trước khi include file này
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'FlashCard SRS') ?> - FlashCard SRS</title>
    <link rel="stylesheet" href="/flashcard/assets/style.css">
</head>

<body>

    <!-- Navbar điều hướng -->
    <header class="navbar">
        <a href="/flashcard/dashboard.php" class="logo">
            🧠 FlashCard SRS
        </a>
        <nav>
            <a href="/flashcard/dashboard.php" <?= ($pageTitle === 'Dashboard') ? 'class="active"' : '' ?>>
                📚 Bộ từ vựng
            </a>
            <a href="/flashcard/stats.php" <?= ($pageTitle === 'Thống kê') ? 'class="active"' : '' ?>>
                📊 Thống kê
            </a>
            <!-- Hiện tên người dùng và nút đăng xuất -->
            <span style="color:rgba(255,255,255,0.7); font-size:0.85rem;">
                👤 <?= htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['email'] ?? '') ?>
            </span>
            <a href="/flashcard/api/logout.php">🚪 Đăng xuất</a>
        </nav>
    </header>

    <!-- Toast thông báo nhanh (dùng JS để hiện) -->
    <div id="toast"></div>