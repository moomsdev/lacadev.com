# HƯỚNG DẪN KỸ NĂNG AGENT - GIAO DIỆN LACA DEV

Tài liệu này cung cấp các hướng dẫn dành cho trợ lý AI và lập trình viên khi làm việc với giao diện `lacadev`, đảm bảo tuân thủ các tiêu chuẩn WordPress hiện đại, tối ưu hóa hiệu suất và các phương pháp bảo mật tốt nhất.

## 1. Kiến trúc Cốt lõi (WPEmerge & Cấu trúc Ứng dụng)
Giao diện sử dụng framework **WPEmerge** cho cách tiếp cận MVC có cấu trúc.
- **Routes (Đường dẫn):** `app/routes/` - Định nghĩa các URL và ánh xạ chúng tới Controllers.
- **Controllers (Bộ điều khiển):** `app/src/Controllers/` - Xử lý logic nghiệp vụ, tương tác với Models và chuẩn bị dữ liệu cho Views.
- **Views (Giao diện):** `theme/templates/` hoặc thông qua các lớp `App\View` - chỉ dành cho logic hiển thị.
- **Cấu hình:** `app/config.php` & `config.json`.

## 2. Tiêu chuẩn Lập trình & Phương pháp Tốt nhất

### A. Tối ưu hóa Hiệu suất (Quan trọng)
1. **Tải Tài nguyên:**
   - Sử dụng các wrapper `Assets::enqueueScript()` và `Assets::enqueueStyle()` để quản lý phiên bản và handle riêng biệt.
   - **Critical CSS:** Kiểm tra `dist/critical.css` và nhúng inline vào `<head>` (được xử lý qua `yarn critical`).
   - **Trì hoãn Script:** Script không quan trọng nên được trì hoãn bằng thuộc tính `defer` (được xử lý trong `assets.php`).
   - **Preloading:** Sử dụng `<link rel="preload">` trong `wp_head` cho các tài nguyên quan trọng (fonts, ảnh above-the-fold, CSS chính). **Không** echo thẻ preload bên trong filter `script_loader_tag`.

2. **Cơ sở dữ liệu & Truy xuất Dữ liệu:**
   - **Không truy vấn N+1:** Tránh dùng `get_post_meta()` hoặc `wp_get_object_terms()` bên trong vòng lặp.
   - **Caching:** Sử dụng `wp_cache_set` / `wp_cache_get` cho các tính toán nặng hoặc truy vấn phức tạp.
   - **Vòng lặp truy vấn:** Sử dụng `WP_Query` với `no_found_rows => true` khi không cần phân trang.

3. **Hình ảnh:**
   - Sử dụng các hàm helper responsive của theme (ví dụ: `theResponsivePostThumbnail`) để tận dụng `srcsets`.
   - Đảm bảo có `loading="lazy"` và `decoding="async"` trên các ảnh ngoài màn hình hiển thị.

### B. Bảo mật (Bắt buộc)
1. **Escape Đầu ra (Output Escaping):**
   - Thuộc tính HTML: `echo esc_attr($var)`
   - Nội dung HTML: `echo esc_html($var)`
   - HTML an toàn (có thẻ): `echo wp_kses_post($var)`
   - Đường dẫn URL: `echo esc_url($var)`
   - Dữ liệu JavaScript: `echo wp_json_encode($data)`
2. **Làm sạch Đầu vào (Input Sanitization):**
   - Làm sạch tất cả dữ liệu `$_GET`, `$_POST`, `$_REQUEST` trước khi sử dụng (ví dụ: `sanitize_text_field`, `absint`).
3. **Nonces (Token xác thực):**
   - Luôn xác minh nonce cho Form và các request AJAX (`check_ajax_referer` hoặc `wp_verify_nonce`).

### C. Gutenberg Blocks (Hybrid)
- **Static Blocks:** Được phát triển trong `block-gutenberg/` sử dụng React/JSX.
- **Dynamic Blocks:** Sử dụng `render.php` để render phía server, đảm bảo logic PHP chạy trên mỗi request.
- **block.json:** Phải có cho mỗi block để định nghĩa metadata, attributes, và styles.

## 3. Quy trình Phát triển
- **Start Dev Server:** `yarn dev` (Theo dõi thay đổi, biên dịch SCSS/JS).
- **Production Build:** `yarn build` (Nén assets, tạo `dist/`).
- **Generate Critical CSS:** `yarn critical` (Tạo lại CSS đường dẫn quan trọng).

## 4. Checklist Trước khi Commit cho Agents
1. [ ] **Escaping:** Tất cả biến trong file template đã được escape đúng cách chưa?
2. [ ] **Preload Logic:** Việc preload có được thực hiện trong `wp_head`, không phải trong footer scripts không?
3. [ ] **Console Logs:** Các dòng debug `console.log` hoặc `var_dump` đã được xóa chưa?
4. [ ] **Kích thước Asset:** Dung lượng bundle có tăng hiệu quả không? (Kiểm tra `dist/Only`).
5. [ ] **Kiểm tra Mobile:** Giao diện có responsive không?

---
*Được tạo bởi Phân tích Kỹ năng Agent*
