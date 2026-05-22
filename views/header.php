<?php
// views/header.php - ĐÃ CẬP NHẬT: dropdown tên user + modal đổi mật khẩu
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
        <a href="/flashcard/dashboard.php" class="logo">FlashCard SRS</a>
        <nav>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="/flashcard/admin_dashboard.php" <?= ($pageTitle === 'Báo cáo hệ thống') ? 'class="active"' : '' ?>>Báo cáo</a>
                <a href="/flashcard/admin_users.php" <?= ($pageTitle === 'Quản lý User')     ? 'class="active"' : '' ?>>Quản lý User</a>
            <?php else: ?>
                <a href="/flashcard/dashboard.php" <?= ($pageTitle === 'Dashboard')  ? 'class="active"' : '' ?>>Bộ từ vựng</a>
                <a href="/flashcard/stats.php" <?= ($pageTitle === 'Thống kê')   ? 'class="active"' : '' ?>>Thống kê</a>
            <?php endif; ?>

            <!-- Dropdown tên user -->
            <div class="user-menu" id="userMenu">
                <button class="user-menu-btn" onclick="toggleUserMenu(event)">
                    <span class="user-avatar">
                        <?= strtoupper(mb_substr($_SESSION['full_name'] ?? $_SESSION['email'] ?? 'K', 0, 1)) ?>
                    </span>
                    <span class="user-name">
                        <?= htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['email'] ?? 'Khách') ?>
                        <?= isset($_SESSION['role']) && $_SESSION['role'] === 'admin' ? ' (Admin)' : '' ?>
                    </span>
                    <span style="font-size:0.7rem; opacity:0.7;">▼</span>
                </button>

                <div class="user-dropdown" id="userDropdown">
                    <a href="/flashcard/profile.php" class="dropdown-item">Xem hồ sơ</a>
                    <div class="dropdown-item" onclick="openModal('modal-change-pw')" style="cursor:pointer;">Đổi mật khẩu</div>
                    <div class="dropdown-divider"></div>
                    <a href="/flashcard/api/logout.php" class="dropdown-item danger">Đăng xuất</a>
                </div>
            </div>

        </nav>
    </header>

    <div id="toast"></div>

    <!-- Modal đổi mật khẩu (nhúng sẵn trong header, dùng được ở mọi trang) -->
    <div id="modal-change-pw" class="modal-overlay">
        <div class="modal-box">
            <h3>Đổi Mật Khẩu</h3>
            <div id="changepw-error" class="alert alert-error" style="display:none;"></div>
            <div id="changepw-success" class="alert alert-success" style="display:none;"></div>

            <div class="form-group">
                <label>Mật khẩu hiện tại</label>
                <input type="password" id="cp-old" placeholder="Nhập mật khẩu đang dùng">
            </div>
            <div class="form-group">
                <label>Mật khẩu mới</label>
                <input type="password" id="cp-new" placeholder="Tối thiểu 6 ký tự">
            </div>
            <div class="form-group">
                <label>Xác nhận mật khẩu mới</label>
                <input type="password" id="cp-confirm" placeholder="Nhập lại mật khẩu mới">
            </div>

            <div class="modal-actions">
                <button class="btn btn-outline" onclick="closeChangePwModal()">Hủy</button>
                <button class="btn btn-primary" onclick="doChangePassword()">Xác nhận</button>
            </div>
        </div>
    </div>

    <style>
        /* --- Dropdown user menu trong navbar --- */
        .user-menu {
            position: relative;
        }

        .user-menu-btn {
            display: flex;
            align-items: center;
            gap: 0.45rem;
            background: rgba(255, 255, 255, 0.15);
            border: none;
            border-radius: 8px;
            padding: 0.35rem 0.75rem;
            cursor: pointer;
            transition: background 0.2s;
            color: white;
        }

        .user-menu-btn:hover {
            background: rgba(255, 255, 255, 0.25);
        }

        .user-avatar {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 700;
            flex-shrink: 0;
        }

        .user-name {
            font-size: 0.85rem;
            max-width: 120px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .user-dropdown {
            display: none;
            position: absolute;
            top: calc(100% + 0.5rem);
            right: 0;
            background: white;
            border: 1px solid var(--border);
            border-radius: 10px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            min-width: 180px;
            z-index: 300;
            overflow: hidden;
        }

        .user-dropdown.open {
            display: block;
        }

        .dropdown-item {
            display: block;
            padding: 0.75rem 1rem;
            font-size: 0.88rem;
            /* color: var(--text); */
            color: #000 !important;
            /* color: black; */
            text-decoration: none;
            transition: background 0.15s;
        }

        .dropdown-item:hover {
            background: var(--bg);
        }

        .dropdown-item.danger {
            color: var(--danger);
        }

        .dropdown-divider {
            height: 1px;
            background: var(--border);
            margin: 0.2rem 0;
        }
    </style>

    <script>
        function toggleUserMenu(e) {
            e.stopPropagation();
            document.getElementById('userDropdown').classList.toggle('open');
        }

        // Đóng dropdown khi click ra ngoài
        document.addEventListener('click', function() {
            document.getElementById('userDropdown')?.classList.remove('open');
        });

        async function doChangePassword() {
            const oldPw = document.getElementById('cp-old').value;
            const newPw = document.getElementById('cp-new').value;
            const confPw = document.getElementById('cp-confirm').value;
            const errEl = document.getElementById('changepw-error');
            const okEl = document.getElementById('changepw-success');

            errEl.style.display = 'none';
            okEl.style.display = 'none';

            if (!oldPw || !newPw || !confPw) {
                errEl.textContent = 'Vui lòng điền đầy đủ!';
                errEl.style.display = 'block';
                return;
            }
            if (newPw.length < 6) {
                errEl.textContent = 'Mật khẩu mới phải có ít nhất 6 ký tự!';
                errEl.style.display = 'block';
                return;
            }
            if (newPw !== confPw) {
                errEl.textContent = 'Mật khẩu xác nhận không khớp!';
                errEl.style.display = 'block';
                return;
            }

            const result = await fetchAPI('/flashcard/api/change_password.php', {
                old_password: oldPw,
                new_password: newPw
            });

            if (result.success) {
                okEl.textContent = 'Đổi mật khẩu thành công!';
                okEl.style.display = 'block';
                document.getElementById('cp-old').value = '';
                document.getElementById('cp-new').value = '';
                document.getElementById('cp-confirm').value = '';
                setTimeout(() => closeChangePwModal(), 1500);
            } else {
                errEl.textContent = result.message;
                errEl.style.display = 'block';
            }
        }

        function closeChangePwModal() {
            closeModal('modal-change-pw');
            document.getElementById('changepw-error').style.display = 'none';
            document.getElementById('changepw-success').style.display = 'none';
            document.getElementById('cp-old').value = '';
            document.getElementById('cp-new').value = '';
            document.getElementById('cp-confirm').value = '';
        }
    </script>