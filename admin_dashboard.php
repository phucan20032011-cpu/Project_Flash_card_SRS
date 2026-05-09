<?php
// admin_dashboard.php - Trang báo cáo tổng quan toàn hệ thống cho Admin
require_once __DIR__ . '/config/db.php';
requireAdmin(); // Chặn User thường

$db = getDB();

// Truy vấn tổng hợp dữ liệu toàn hệ thống
$totalUsers = $db->query("SELECT COUNT(*) FROM Users WHERE role = 'user'")->fetchColumn();
$totalSets  = $db->query("SELECT COUNT(*) FROM Vocabulary_Sets")->fetchColumn();
$totalCards = $db->query("SELECT COUNT(*) FROM Flashcards")->fetchColumn();
$totalStudy = $db->query("SELECT COUNT(*) FROM Study_History")->fetchColumn();

$pageTitle = 'Báo cáo hệ thống';
include __DIR__ . '/views/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>📈 Báo Cáo Tổng Quan Hệ Thống</h1>
        <p style="color:var(--text-muted);">Dữ liệu được tổng hợp từ tất cả người dùng trên nền tảng.</p>
    </div>

    <div class="stats-row">
        <div class="stat-box">
            <div class="stat-icon blue">👥</div>
            <div class="stat-info">
                <div class="stat-number"><?= $totalUsers ?></div>
                <div class="stat-label">Học viên (Users)</div>
            </div>
        </div>
        <div class="stat-box">
            <div class="stat-icon purple">📚</div>
            <div class="stat-info">
                <div class="stat-number"><?= $totalSets ?></div>
                <div class="stat-label">Tổng bộ từ vựng</div>
            </div>
        </div>
        <div class="stat-box">
            <div class="stat-icon orange">🃏</div>
            <div class="stat-info">
                <div class="stat-number"><?= $totalCards ?></div>
                <div class="stat-label">Tổng Flashcards</div>
            </div>
        </div>
        <div class="stat-box">
            <div class="stat-icon green">🎯</div>
            <div class="stat-info">
                <div class="stat-number"><?= $totalStudy ?></div>
                <div class="stat-label">Lượt lật thẻ toàn trang</div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/views/footer.php'; ?>