<?php
// index.php - Trang chủ: Đăng nhập / Đăng ký
require_once __DIR__ . '/config/db.php';

// Nếu đã đăng nhập rồi thì chuyển thẳng sang dashboard
if (isLoggedIn()) {
    header('Location: /flashcard/dashboard.php');
    exit;
}

$error = '';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FlashCard SRS - Đăng nhập</title>
    <link rel="stylesheet" href="/flashcard/assets/style.css">
</head>

<body>
    <div id="toast"></div>

    <!-- Khung đăng nhập / đăng ký chính giữa màn hình -->
    <div class="auth-wrapper">
        <div class="auth-box">

            <div class="auth-logo">
                <h1>FlashCard SRS</h1>
                <p>Học từ vựng thông minh với phương pháp lặp lại ngắt quãng</p>
            </div>

            <!-- Tab chuyển đổi Đăng nhập / Đăng ký -->
            <div class="auth-tabs">
                <button class="auth-tab active" data-tab="login" onclick="switchTab('login')">Đăng nhập</button>
                <button class="auth-tab" data-tab="register" onclick="switchTab('register')">Đăng ký</button>
            </div>

            <!-- === FORM ĐĂNG NHẬP === -->
            <div id="tab-login" class="tab-content">
                <div id="login-error" class="alert alert-error" style="display:none;"></div>

                <div class="form-group">
                    <label for="login-email">Email</label>
                    <input type="email" id="login-email" placeholder="example@gmail.com">
                </div>
                <div class="form-group">
                    <label for="login-pass">Mật khẩu</label>
                    <input type="password" id="login-pass" placeholder="••••••••">
                </div>
                <button class="btn btn-primary" style="width:100%;" onclick="doLogin()">
                    Đăng nhập
                </button>
                <!-- <p style="text-align:center; margin-top:1rem; font-size:0.82rem; color:var(--text-muted);">
                    Demo: <strong>student@demo.com</strong> / <strong>password</strong>
                </p> -->
            </div>

            <!-- === FORM ĐĂNG KÝ === -->
            <div id="tab-register" class="tab-content" style="display:none;">
                <div id="register-error" class="alert alert-error" style="display:none;"></div>

                <div class="form-group">
                    <label for="reg-name">Họ tên</label>
                    <input type="text" id="reg-name" placeholder="Nguyễn Văn A">
                </div>
                <div class="form-group">
                    <label for="reg-email">Email</label>
                    <input type="email" id="reg-email" placeholder="example@gmail.com">
                </div>
                <div class="form-group">
                    <label for="reg-pass">Mật khẩu (tối thiểu 6 ký tự)</label>
                    <input type="password" id="reg-pass" placeholder="••••••••">
                </div>
                <button class="btn btn-primary" style="width:100%;" onclick="doRegister()">
                    Tạo tài khoản
                </button>
            </div>

        </div>
    </div>

    <script src="/flashcard/assets/script.js"></script>
    <script>
        // Xử lý đăng nhập bằng Fetch API (không tải lại trang)
        async function doLogin() {
            const email = document.getElementById('login-email').value.trim();
            const pass = document.getElementById('login-pass').value;

            // Kiểm tra đơn giản phía client
            if (!email || !pass) {
                showLoginError('Vui lòng nhập đầy đủ email và mật khẩu!');
                return;
            }

            // Gọi API đăng nhập
            const result = await fetchAPI('/flashcard/api/login.php', {
                email,
                password: pass
            });

            if (result.success) {
                // Đăng nhập thành công -> chuyển trang
                window.location.href = '/flashcard/dashboard.php';
            } else {
                showLoginError(result.message);
            }
        }

        // Xử lý đăng ký
        async function doRegister() {
            const full_name = document.getElementById('reg-name').value.trim();
            const email = document.getElementById('reg-email').value.trim();
            const password = document.getElementById('reg-pass').value;

            if (!full_name || !email || !password) {
                showRegisterError('Vui lòng điền đầy đủ thông tin!');
                return;
            }
            if (password.length < 6) {
                showRegisterError('Mật khẩu phải có ít nhất 6 ký tự!');
                return;
            }

            const result = await fetchAPI('/flashcard/api/register.php', {
                full_name,
                email,
                password
            });

            if (result.success) {
                showToast('Đăng ký thành công! Đang chuyển trang...');
                setTimeout(() => window.location.href = '/flashcard/dashboard.php', 1000);
            } else {
                showRegisterError(result.message);
            }
        }

        function showLoginError(msg) {
            const el = document.getElementById('login-error');
            el.textContent = msg;
            el.style.display = 'block';
        }

        function showRegisterError(msg) {
            const el = document.getElementById('register-error');
            el.textContent = msg;
            el.style.display = 'block';
        }

        // Ẩn lỗi khi người dùng bắt đầu gõ
        document.addEventListener('input', () => {
            document.getElementById('login-error').style.display = 'none';
            document.getElementById('register-error').style.display = 'none';
        });

        // Cho phép nhấn Enter để đăng nhập
        document.getElementById('login-pass').addEventListener('keypress', e => {
            if (e.key === 'Enter') doLogin();
        });
    </script>
</body>

</html>