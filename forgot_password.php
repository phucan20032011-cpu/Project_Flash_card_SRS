<?php
// forgot_password.php - Đặt ở thư mục gốc: flashcard/
require_once __DIR__ . '/config/db.php';

if (isLoggedIn()) {
    header('Location: /flashcard/dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu - FlashCard SRS</title>
    <link rel="stylesheet" href="/flashcard/assets/style.css">
</head>

<body>
    <div id="toast"></div>

    <div class="auth-wrapper">
        <div class="auth-box">
            <div class="auth-logo">
                <h1>FlashCard SRS</h1>
                <p>Đặt lại mật khẩu</p>
            </div>

            <!-- Bước 1: Xác minh danh tính -->
            <div id="step-1">
                <div id="step1-error" class="alert alert-error" style="display:none;"></div>
                <div class="form-group">
                    <label>Họ tên đầy đủ</label>
                    <input type="text" id="input-name" placeholder="Nguyễn Văn A">
                </div>
                <div class="form-group">
                    <label>Email đã đăng ký</label>
                    <input type="email" id="input-email" placeholder="example@gmail.com">
                </div>
                <button class="btn btn-primary" style="width:100%;" onclick="doVerify()">Tiếp tục</button>
                <p style="text-align:center; margin-top:1rem; font-size:0.88rem;">
                    <a href="/flashcard/index.php" style="color:var(--primary);">← Quay lại đăng nhập</a>
                </p>
            </div>

            <!-- Bước 2: Nhập mật khẩu mới -->
            <div id="step-2" style="display:none;">
                <div id="step2-error" class="alert alert-error" style="display:none;"></div>
                <p style="color:var(--text-muted); font-size:0.88rem; margin-bottom:1.2rem;">
                    Xác minh thành công! Nhập mật khẩu mới của bạn.
                </p>
                <div class="form-group">
                    <label>Mật khẩu mới</label>
                    <input type="password" id="input-new-pw" placeholder="Tối thiểu 6 ký tự">
                </div>
                <div class="form-group">
                    <label>Xác nhận mật khẩu mới</label>
                    <input type="password" id="input-confirm-pw" placeholder="Nhập lại mật khẩu mới">
                </div>
                <button class="btn btn-primary" style="width:100%;" onclick="doReset()">Đặt lại mật khẩu</button>
            </div>

        </div>
    </div>

    <script src="/flashcard/assets/script.js"></script>
    <script>
        // Lưu user_id sau khi xác minh thành công ở bước 1
        let verifiedUserId = null;

        async function doVerify() {
            const full_name = document.getElementById('input-name').value.trim();
            const email = document.getElementById('input-email').value.trim();
            const errEl = document.getElementById('step1-error');
            errEl.style.display = 'none';

            if (!full_name || !email) {
                errEl.textContent = 'Vui lòng nhập đầy đủ thông tin!';
                errEl.style.display = 'block';
                return;
            }

            const result = await fetchAPI('/flashcard/api/verify_identity.php', {
                full_name,
                email
            });

            if (result.success) {
                verifiedUserId = result.user_id;
                document.getElementById('step-1').style.display = 'none';
                document.getElementById('step-2').style.display = 'block';
            } else {
                errEl.textContent = result.message;
                errEl.style.display = 'block';
            }
        }

        async function doReset() {
            const newPw = document.getElementById('input-new-pw').value;
            const confPw = document.getElementById('input-confirm-pw').value;
            const errEl = document.getElementById('step2-error');
            errEl.style.display = 'none';

            if (!newPw || !confPw) {
                errEl.textContent = 'Vui lòng điền đầy đủ!';
                errEl.style.display = 'block';
                return;
            }
            if (newPw.length < 6) {
                errEl.textContent = 'Mật khẩu phải có ít nhất 6 ký tự!';
                errEl.style.display = 'block';
                return;
            }
            if (newPw !== confPw) {
                errEl.textContent = 'Mật khẩu xác nhận không khớp!';
                errEl.style.display = 'block';
                return;
            }

            const result = await fetchAPI('/flashcard/api/reset_password.php', {
                user_id: verifiedUserId,
                new_password: newPw
            });

            if (result.success) {
                showToast('Đặt lại mật khẩu thành công!');
                setTimeout(() => window.location.href = '/flashcard/index.php', 1200);
            } else {
                errEl.textContent = result.message;
                errEl.style.display = 'block';
            }
        }

        document.getElementById('input-email').addEventListener('keypress', e => {
            if (e.key === 'Enter') doVerify();
        });
        document.getElementById('input-confirm-pw').addEventListener('keypress', e => {
            if (e.key === 'Enter') doReset();
        });
    </script>
</body>

</html>