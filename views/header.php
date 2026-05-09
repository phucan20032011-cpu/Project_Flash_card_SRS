<?php
// header.php - Header 
if (!isset($pageTitle)) {
    $pageTitle = 'FlashCard SRS';
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - FlashCard SRS</title>
    <link rel="stylesheet" href="/flashcard/assets/style.css">
</head>

<body>

    <header class="navbar">
        <a href="/flashcard/dashboard.php" class="logo">
            FlashCard SRS
        </a>
        <nav>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="/flashcard/admin_dashboard.php" <?= ($pageTitle === 'Báo cáo hệ thống') ? 'class="active"' : '' ?>>
                    Báo cáo
                </a>
                <a href="/flashcard/admin_users.php" <?= ($pageTitle === 'Quản lý User') ? 'class="active"' : '' ?>>
                    Quản lý User
                </a>
            <?php else: ?>
                <a href="/flashcard/dashboard.php" <?= ($pageTitle === 'Dashboard') ? 'class="active"' : '' ?>>
                    Bộ từ vựng
                </a>
                <a href="/flashcard/stats.php" <?= ($pageTitle === 'Thống kê') ? 'class="active"' : '' ?>>
                    Thống kê
                </a>
            <?php endif; ?>

            <span style="color:rgba(255,255,255,0.7); font-size:0.85rem;">
                <?= htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['email'] ?? 'Khách') ?>
                <?= isset($_SESSION['role']) && $_SESSION['role'] === 'admin' ? '(Admin)' : '' ?>
            </span>
            <a href="/flashcard/api/logout.php">Đăng xuất</a>
        </nav>
    </header>

    <div id="toast"></div>