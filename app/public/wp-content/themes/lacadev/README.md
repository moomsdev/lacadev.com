# ⚡ Laca Dev Theme - High Performance WordPress Theme

Chào mừng bạn đến với project theme WordPress được tối ưu hóa đặc biệt của **Laca Dev Team**. Theme này được xây dựng với tư duy "Performance First" (Hiệu suất là ưu tiên hàng đầu), loại bỏ các dependencies nặng nề và áp dụng các kỹ thuật hiện đại nhất.

---

## 🌟 1. Theme Này Đạt Được Gì? (Ưu Điểm)

Đây không phải là một theme WordPress thông thường. Nó là một cỗ máy tốc độ:

*   **🚀 Siêu Tốc Độ (Ultra Fast):**
    *   **Zero jQuery Frontend:** Toàn bộ code frontend (Admin, Login, Frontend) được viết lại bằng **Vanilla JS**.
    *   **Critical CSS:** Tự động tách và inline CSS quan trọng vào thẻ `<head>` để render trang ngay lập tức (FCP cực thấp).
    *   **Asset Bundling:** Sử dụng **Webpack 5** để nén, gộp và tối ưu hóa toàn bộ JS/CSS.
    *   **Lazy Loading thông minh:** Tự động defer các script không quan trọng và lazy load hình ảnh.

*   **🛡️ Bảo Mật Cao (Secure):**
    *   Tất cả các requests AJAX đều được bảo vệ bởi **Nonce Verification**.
    *   Dữ liệu đầu vào/đầu ra được Sanitize và Escape kỹ càng.

*   **🛠️ Clean Code & Modern:**
    *   Cấu trúc code hiện đại, tách biệt logic (PHP) và assets (JS/SCSS).
    *   Loại bỏ hoàn toàn code rác của WordPress (Emoji, Embeds, WP Blocks CSS thừa).

*   **💰 SEO Friendly:**
    *   Cấu trúc HTML ngữ nghĩa (Semantic HTML).
    *   Tối ưu hóa Core Web Vitals của Google.

---

## ⚠️ 2. Lưu Ý (Nhược Điểm & Yêu Cầu)

Vì được tối ưu hóa sâu, theme này có một số rào cản kỹ thuật:

*   **Phải Có Kiến Thức Dev:** Bạn không thể chỉnh sửa CSS/JS trực tiếp qua giao diện WordPress hoặc FTP theo cách cổ điển.
*   **Cần Build Tools:** Bắt buộc phải cài đặt **Node.js** và **Yarn** để phát triển.
*   **Cấu trúc Khác Biệt:** File source nằm trong `resources/`, file chạy nằm trong `dist/`.

---

## 👨‍💻 3. Hướng Dẫn Dành Cho Developer

Nếu bạn là Developer tiếp nhận dự án này, hãy đọc kỹ hướng dẫn sau:

### 📥 A. Cài Đặt Môi Trường

1.  Đảm bảo máy đã cài **Node.js** (v20+) và **Yarn**.
2.  Mở terminal tại thư mục root của theme:
    ```bash
    composer install
    ```

### 🔨 B. Các Câu Lệnh Quan Trọng

| Lệnh | Mô Tả | Khi Nào Dùng? |
| :--- | :--- | :--- |
| `composer install` | Cài đặt các dependencies PHP. | **Khi cài đặt theme.** |
| `yarn install` | Cài đặt các dependencies JS. | **Khi cài đặt theme.** |
| `yarn dev` | Chạy server development, có watch file và source maps. | **Khi đang code.** |
| `yarn build` | Build code cho Production. Nén file, xóa comments, xóa source maps. | **Trước khi deploy/live.** |
| `yarn critical` | Quét trang chủ và tạo file `critical.css` (inline styles). | **Khi sửa đổi giao diện xong.** |

### 📂 C. Cấu Trúc Thư Mục

Code của theme được tổ chức khoa học:

*   `app/` ➡️ **Logic PHP:** Chứa Controllers, Helpers, Setup.
    *   `app/helpers/`: Các hàm tiện ích (AJAX, Functions).
    *   `app/src/`: Classes xử lý logic chính.
*   `resources/` ➡️ **Source Assets:** Nơi bạn viết code.
    *   `resources/scripts/`: Javascript (Module based).
    *   `resources/styles/`: SCSS/CSS.
*   `dist/` ➡️ **Compiled Assets:** Nơi Webpack xuất file ra (Không sửa trực tiếp ở đây).
*   `theme/` ➡️ **Template Files:** Các file cấu trúc theme (`functions.php`, `header.php`, partials...).

### 💡 D. Quy Tắc Code (Coding Standards)

1.  **NO jQuery:** Tuyệt đối không thêm jQuery vào frontend trừ khi bắt buộc từ plugin bên thứ 3.
2.  **AJAX:** Luôn dùng `check_ajax_referer` ở backend và gửi `nonce` từ frontend.
3.  **Styles:** Viết SCSS trong `resources/styles`, không viết inline style trong PHP.

---
*Happy Coding! 🚀*
**Laca Dev Team**
