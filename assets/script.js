// assets/script.js - JavaScript toàn cục

// --- Hiển thị thông báo Toast ngắn ---
function showToast(message, duration = 2500) {
  const toast = document.getElementById("toast");
  if (!toast) return;
  toast.textContent = message;
  toast.classList.add("show");
  // Tự ẩn sau 'duration' mili giây
  setTimeout(() => toast.classList.remove("show"), duration);
}

// --- Xác nhận xóa trước khi thực hiện ---
function confirmDelete(message, callback) {
  if (window.confirm(message || "Bạn có chắc muốn xóa?")) {
    callback();
  }
}

// --- Chuyển đổi tab đăng nhập / đăng ký ---
function switchTab(tabName) {
  // Ẩn tất cả form
  document
    .querySelectorAll(".tab-content")
    .forEach((el) => (el.style.display = "none"));
  // Bỏ active tất cả tab button
  document
    .querySelectorAll(".auth-tab")
    .forEach((el) => el.classList.remove("active"));
  // Hiện form và active tab được chọn
  document.getElementById("tab-" + tabName).style.display = "block";
  document.querySelector(`[data-tab="${tabName}"]`).classList.add("active");
}

// --- Mở / đóng Modal ---
function openModal(id) {
  document.getElementById(id).classList.add("open");
}

function closeModal(id) {
  document.getElementById(id).classList.remove("open");
}

// Đóng modal khi click bên ngoài
document.addEventListener("click", function (e) {
  if (e.target.classList.contains("modal-overlay")) {
    e.target.classList.remove("open");
  }
});

// --- Gửi form AJAX không tải lại trang ---
// Hàm tiện ích: gọi API PHP và trả về JSON
async function fetchAPI(url, data) {
  try {
    const response = await fetch(url, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(data),
    });

    // Kiểm tra server có trả về OK không
    if (!response.ok) throw new Error("Lỗi kết nối máy chủ");

    return await response.json();
  } catch (err) {
    console.error("Lỗi fetchAPI:", err);
    return { success: false, message: "Không thể kết nối máy chủ!" };
  }
}

// LOGIC TRANG HỌC (study.php)
let studyCards = []; // Mảng thẻ cần học hôm nay
let currentIndex = 0; // Vị trí thẻ hiện tại
let isFlipped = false; // Trạng thái lật thẻ

// Khởi tạo buổi học - gọi từ study.php sau khi load
function initStudy(cards) {
  studyCards = cards;
  currentIndex = 0;
  showCard(0);
  updateProgress();
}

// Hiện thẻ tại vị trí index
function showCard(index) {
  if (index >= studyCards.length) {
    // Hết thẻ -> hiện màn hình kết thúc
    showDoneScreen();
    return;
  }

  const card = studyCards[index];
  const flipCard = document.getElementById("flip-card");

  // Reset về mặt trước khi chuyển thẻ mới
  isFlipped = false;
  flipCard.classList.remove("flipped");

  // Đổ nội dung thẻ
  document.getElementById("front-text").textContent = card.front_text;
  document.getElementById("back-text").textContent = card.back_text;

  // Vô hiệu hóa nút đánh giá cho đến khi lật thẻ
  setRatingDisabled(true);

  updateProgress();
}

// Lật thẻ khi click
function flipCard() {
  const flipCardEl = document.getElementById("flip-card");

  if (!isFlipped) {
    // Lật ra mặt sau
    flipCardEl.classList.add("flipped");
    isFlipped = true;
    // nút đánh giá sau khi đã xem đáp án
    setRatingDisabled(false);
  } else {
    // Lật lại mặt trước
    flipCardEl.classList.remove("flipped");
    isFlipped = false;
    setRatingDisabled(true);
  }
}

// Bật/tắt nút đánh giá
function setRatingDisabled(disabled) {
  document.querySelectorAll(".rating-btn").forEach((btn) => {
    btn.disabled = disabled;
  });
}

// Cập nhật thanh tiến độ học
function updateProgress() {
  const total = studyCards.length;
  const done = currentIndex;
  const pct = total > 0 ? Math.round((done / total) * 100) : 0;

  const fill = document.getElementById("progress-fill");
  const text = document.getElementById("progress-text");
  if (fill) fill.style.width = pct + "%";
  if (text) text.textContent = `${done} / ${total} thẻ`;
}

// Gửi đánh giá khi người dùng bấm Quên/Khó/Khá/Nhớ
async function rateCard(grade) {
  if (currentIndex >= studyCards.length) return;

  const card = studyCards[currentIndex];

  // Gọi API PHP để cập nhật SRS và lưu lịch sử
  const result = await fetchAPI("/flashcard/api/update_srs.php", {
    card_id: card.card_id,
    grade: grade,
  });

  if (result.success) {
    // Cho biết lịch ôn tiếp theo
    showToast(`${result.message}`);
  } else {
    showToast(result.message || "Có lỗi xảy ra");
  }

  // Chuyển sang thẻ tiếp theo
  currentIndex++;
  showCard(currentIndex);
}

// Hiện màn hình hoàn thành buổi học
function showDoneScreen() {
  document.getElementById("study-area").style.display = "none";
  document.getElementById("study-done").style.display = "flex";
}

// FORM THÊM / SỬA BỘ TỪ VỰNG (dashboard.php)
async function submitSetForm(formId, url, onSuccess) {
  const form = document.getElementById(formId);
  if (!form) return;

  const formData = new FormData(form);
  const data = Object.fromEntries(formData.entries());

  const result = await fetchAPI(url, data);

  if (result.success) {
    showToast(result.message);
    closeModal("modal-set");
    // Reload để cập nhật danh sách
    setTimeout(() => location.reload(), 800);
  } else {
    // Hiện thông báo lỗi trong form
    const errEl = form.querySelector(".form-error");
    if (errEl) errEl.textContent = result.message;
  }
}

// FORM THÊM / SỬA FLASHCARD (cards.php)
async function submitCardForm(formId, url) {
  const form = document.getElementById(formId);
  if (!form) return;

  const formData = new FormData(form);
  const data = Object.fromEntries(formData.entries());

  const result = await fetchAPI(url, data);

  if (result.success) {
    showToast(result.message);
    closeModal("modal-card");
    setTimeout(() => location.reload(), 800);
  } else {
    const errEl = form.querySelector(".form-error");
    if (errEl) errEl.textContent = result.message;
  }
}

// Xóa flashcard
async function deleteCard(cardId) {
  confirmDelete("Xóa thẻ này?", async () => {
    const result = await fetchAPI("/flashcard/api/delete_card.php", {
      card_id: cardId,
    });
    if (result.success) {
      showToast("Đã xóa thẻ");
      document.getElementById("card-row-" + cardId)?.remove();
    } else {
      showToast(result.message);
    }
  });
}

// Xóa bộ từ vựng
async function deleteSet(setId) {
  confirmDelete("Xóa bộ từ vựng này và toàn bộ thẻ bên trong?", async () => {
    const result = await fetchAPI("/flashcard/api/delete_set.php", {
      set_id: setId,
    });
    if (result.success) {
      showToast("Đã xóa bộ từ vựng");
      document.getElementById("set-card-" + setId)?.remove();
    } else {
      showToast(result.message);
    }
  });
}

// Đánh dấu / bỏ đánh dấu thẻ (flag)
async function toggleFlag(cardId, btn) {
  const result = await fetchAPI("/flashcard/api/flag_card.php", {
    card_id: cardId,
  });
  if (result.success) {
    // Cập nhật giao diện ngay lập tức
    const row = document.getElementById("card-row-" + cardId);
    if (result.is_flagged) {
      btn.textContent = "";
      btn.title = "Bỏ đánh dấu";
      row?.classList.add("flagged");
    } else {
      btn.textContent = "⚑";
      btn.title = "Đánh dấu cần xem lại";
      row?.classList.remove("flagged");
    }
  }
}

// Điền dữ liệu vào form khi sửa thẻ
function openEditCard(card) {
  document.getElementById("edit-card-id").value = card.card_id;
  document.getElementById("edit-front-text").value = card.front_text;
  document.getElementById("edit-back-text").value = card.back_text;
  document.getElementById("edit-tags").value = card.tags || "";
  document.getElementById("modal-title-card").textContent = "Sửa Flashcard";
  openModal("modal-card");
}

// Điền dữ liệu vào form khi sửa bộ từ vựng
function openEditSet(set) {
  document.getElementById("edit-set-id").value = set.set_id;
  document.getElementById("edit-set-title").value = set.title;
  document.getElementById("edit-set-desc").value = set.description || "";
  document.getElementById("modal-title-set").textContent = "Sửa Bộ Từ Vựng";
  openModal("modal-set");
}

// LOGIC DÀNH CHO ADMIN
async function deleteUser(userId) {
  confirmDelete(
    "CẢNH BÁO: Bạn có chắc chắn muốn xóa người dùng này? Toàn bộ bộ từ vựng và thẻ học của họ sẽ bị xóa vĩnh viễn!",
    async () => {
      const result = await fetchAPI("/flashcard/api/delete_user.php", {
        user_id: userId,
      });
      if (result.success) {
        showToast(result.message);
        // Ẩn dòng chứa user vừa bị xóa khỏi bảng
        document.getElementById("user-row-" + userId)?.remove();
      } else {
        showToast(result.message);
      }
    },
  );
}
