<?php
// stats.php - Trang thống kê tiến độ học tập
require_once __DIR__ . '/config/db.php';
requireLogin();

$db     = getDB();
$userId = $_SESSION['user_id'];

// Tổng số thẻ đã học trong 7 ngày qua (theo ngày)
$stmtWeek = $db->prepare("
    SELECT DATE(reviewed_at) AS day, COUNT(*) AS count
    FROM Study_History
    WHERE user_id = ? AND reviewed_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
    GROUP BY DATE(reviewed_at)
    ORDER BY day ASC
");
$stmtWeek->execute([$userId]);
$weekData = $stmtWeek->fetchAll();

// Tỷ lệ các mức đánh giá (Quên/Khó/Khá/Nhớ)
$stmtGrade = $db->prepare("
    SELECT response_grade, COUNT(*) AS count
    FROM Study_History
    WHERE user_id = ?
    GROUP BY response_grade
    ORDER BY response_grade ASC
");
$stmtGrade->execute([$userId]);
$gradeData = $stmtGrade->fetchAll();

// Phân bố thẻ theo hộp Leitner
$stmtBoxes = $db->prepare("
    SELECT box_level, COUNT(*) AS count
    FROM SRS_Schedule
    WHERE user_id = ?
    GROUP BY box_level
    ORDER BY box_level ASC
");
$stmtBoxes->execute([$userId]);
$boxData = $stmtBoxes->fetchAll();

// Tổng số thẻ đã học (all time)
$stmtTotal = $db->prepare("SELECT COUNT(*) AS total FROM Study_History WHERE user_id = ?");
$stmtTotal->execute([$userId]);
$totalReviews = $stmtTotal->fetch()['total'];

// Chuỗi streak học (số ngày liên tiếp)
$stmtStreak = $db->prepare("
    SELECT DISTINCT DATE(reviewed_at) AS study_day
    FROM Study_History
    WHERE user_id = ?
    ORDER BY study_day DESC
");
$stmtStreak->execute([$userId]);
$studyDays = $stmtStreak->fetchAll(PDO::FETCH_COLUMN);

// Tính streak ngày liên tiếp
$streak = 0;
$today  = new DateTime();
foreach ($studyDays as $day) {
    $diff = $today->diff(new DateTime($day))->days;
    if ($diff == $streak) {
        $streak++;
    } else {
        break;
    }
}

$pageTitle = 'Thống kê';
include __DIR__ . '/views/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>Thống Kê Học Tập</h1>
        <a href="/flashcard/dashboard.php" class="btn btn-outline">← Quay lại</a>
    </div>

    <!-- Tổng quan nhanh -->
    <div class="stats-row" style="margin-bottom:2rem;">
        <div class="stat-box">
            <div class="stat-icon purple">🔄</div>
            <div class="stat-info">
                <div class="stat-number"><?= $totalReviews ?></div>
                <div class="stat-label">Tổng lượt ôn</div>
            </div>
        </div>
        <div class="stat-box">
            <div class="stat-icon orange">🔥</div>
            <div class="stat-info">
                <div class="stat-number"><?= $streak ?></div>
                <div class="stat-label">Ngày học liên tiếp</div>
            </div>
        </div>
        <div class="stat-box">
            <div class="stat-icon green">🧠</div>
            <div class="stat-info">
                <?php
                // Tính tỷ lệ nhớ (grade >= 2)
                $totalG = array_sum(array_column($gradeData, 'count'));
                $goodG  = 0;
                foreach ($gradeData as $g) {
                    if ($g['response_grade'] >= 2) $goodG += $g['count'];
                }
                $pct = $totalG > 0 ? round($goodG / $totalG * 100) : 0;
                ?>
                <div class="stat-number"><?= $pct ?>%</div>
                <div class="stat-label">Tỷ lệ ghi nhớ</div>
            </div>
        </div>
        <div class="stat-box">
            <div class="stat-icon blue">🏆</div>
            <div class="stat-info">
                <?php
                $maxBox = 0;
                foreach ($boxData as $b) $maxBox = max($maxBox, $b['box_level']);
                ?>
                <div class="stat-number"><?= $maxBox ?></div>
                <div class="stat-label">Hộp cao nhất đạt</div>
            </div>
        </div>
    </div>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; flex-wrap:wrap;">

        <!-- Biểu đồ 7 ngày -->
        <div class="card">
            <div class="card-header">
                <h2>Lượt học 7 ngày qua</h2>
            </div>
            <div class="card-body">
                <?php
                // Chuẩn bị dữ liệu 7 ngày (kể cả ngày không học)
                $days7 = [];
                for ($i = 6; $i >= 0; $i--) {
                    $days7[date('Y-m-d', strtotime("-{$i} days"))] = 0;
                }
                foreach ($weekData as $wd) {
                    if (isset($days7[$wd['day']])) $days7[$wd['day']] = (int)$wd['count'];
                }
                $maxVal = max(array_values($days7)) ?: 1;
                ?>
                <div style="display:flex; align-items:flex-end; gap:0.5rem; height:150px; margin-bottom:0.5rem;">
                    <?php foreach ($days7 as $day => $count): ?>
                        <?php $h = max(4, round($count / $maxVal * 100)); ?>
                        <div style="flex:1; display:flex; flex-direction:column; align-items:center; gap:0.3rem; height:100%; justify-content:flex-end;">
                            <span style="font-size:0.72rem; font-weight:700; color:var(--primary);">
                                <?= $count ?: '' ?>
                            </span>
                            <div style="width:100%; height:<?= $h ?>%; background:var(--primary); border-radius:4px 4px 0 0; opacity:0.85; transition:height 0.5s ease;"></div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div style="display:flex; gap:0.5rem;">
                    <?php foreach ($days7 as $day => $count): ?>
                        <div style="flex:1; text-align:center; font-size:0.68rem; color:var(--text-muted);">
                            <?= date('d/m', strtotime($day)) ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Phân bố đánh giá -->
        <div class="card">
            <div class="card-header">
                <h2>Kết quả đánh giá</h2>
            </div>
            <div class="card-body">
                <?php
                $labels = ['Quên', 'Khó', 'Khá', 'Nhớ'];
                $colors = ['#EF4444', '#F59E0B', '#3B82F6', '#10B981'];
                $gradeMap = [];
                foreach ($gradeData as $g) $gradeMap[$g['response_grade']] = $g['count'];
                $total = array_sum($gradeMap) ?: 1;
                ?>
                <div style="display:flex; flex-direction:column; gap:0.75rem;">
                    <?php for ($i = 0; $i <= 3; $i++): ?>
                        <?php
                        $cnt = $gradeMap[$i] ?? 0;
                        $pct2 = round($cnt / $total * 100);
                        ?>
                        <div>
                            <div style="display:flex; justify-content:space-between; font-size:0.85rem; margin-bottom:0.3rem;">
                                <span><?= $labels[$i] ?></span>
                                <span style="font-weight:700;"><?= $cnt ?> (<?= $pct2 ?>%)</span>
                            </div>
                            <div style="background:var(--border); border-radius:100px; height:10px;">
                                <div style="width:<?= $pct2 ?>%; background:<?= $colors[$i] ?>; height:100%; border-radius:100px; transition:width 0.5s;"></div>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>

    </div>

    <!-- Phân bố hộp Leitner -->
    <div class="card" style="margin-top:1.5rem;">
        <div class="card-header">
            <h2>Phân bố theo Hộp Leitner</h2>
            <span style="font-size:0.82rem; color:var(--text-muted);">Hộp cao = ghi nhớ lâu hơn</span>
        </div>
        <div class="card-body">
            <?php if (empty($boxData)): ?>
                <p style="color:var(--text-muted); text-align:center;">Chưa có dữ liệu. Hãy bắt đầu học!</p>
            <?php else: ?>
                <div style="display:flex; flex-wrap:wrap; gap:0.75rem;">
                    <?php
                    $boxColors = ['#EDE9FE', '#DBEAFE', '#D1FAE5', '#FEF3C7', '#FEE2E2'];
                    $totalCards = array_sum(array_column($boxData, 'count'));
                    foreach ($boxData as $box):
                        $pctBox = round($box['count'] / $totalCards * 100);
                        $color = $boxColors[($box['box_level'] - 1) % count($boxColors)];
                    ?>
                        <div style="background:<?= $color ?>; border-radius:10px; padding:1rem 1.5rem; text-align:center; min-width:100px; flex:1;">
                            <div style="font-size:1.5rem; font-weight:800; color:var(--primary);">
                                <?= $box['count'] ?>
                            </div>
                            <div style="font-size:0.8rem; font-weight:600; color:var(--text);">Hộp <?= $box['box_level'] ?></div>
                            <div style="font-size:0.72rem; color:var(--text-muted);"><?= $pctBox ?>%</div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <p style="margin-top:1rem; font-size:0.82rem; color:var(--text-muted);">
                    Thẻ ở hộp cao hơn sẽ được ôn ít thường xuyên hơn vì bạn đã nhớ tốt hơn.
                </p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/views/footer.php'; ?>