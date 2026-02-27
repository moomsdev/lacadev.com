---
description: Hướng dẫn tạo mới Gutenberg Block chuẩn kiến trúc theme LacaDev
---
# Tiêu chuẩn và Quy trình phát triển Block Gutenberg - Theme LacaDev

Khi nhận được yêu cầu tạo thêm 1 Block Gutenberg mới cho theme, Agent phải tuân thủ nghiêm ngặt các quy tắc kiến trúc, an toàn, hiệu năng của theme dựa trên các chuẩn đã được chứng minh của dự án. 

Vị trí gốc của theme là: `app/public/wp-content/themes/lacadev`

## 1. Cấu trúc thư mục Block Gutenberg
Tuân thủ cấu trúc tạo file Dynamic Rendering.
- Tạo thư mục cho Block mới nằm trong: `block-gutenberg/[tên-block-của-bạn]/` 
- Trong thư mục đó phải có đầy đủ 5 files cơ bản:
  1. `block.json`: Metadata của block (`name`, `title`, `attributes`,...). Tên block phải bắt đầu với prefix `lacadev/[tên-block]`. Text domain: `laca`.
  2. `index.js`: Nơi register block.
  3. `edit.js`: Chứa React code để render UI trong Backend (Editor).
  4. `save.js`: Vì là dynamic block nên thường chỉ return `null`, hãy return hàm export tĩnh tương tự cấu trúc các block lân cận.
  5. `render.php`: Chứa PHP logic để xuất HTML ra ngoài màn hình người dùng.

## 2. Tổ chức Style (CSS/SCSS)
- SCSS **không** nên ném lung tung trong thư mục của block.
- Khai báo class chuẩn BEM naming convention, ví dụ: `.block-[tên-block] { ... }`
- Chèn code SCSS của block vào trong tệp tin: `resources/styles/theme/layout/_blocks.scss`
- Webpack của theme sẽ tự động minify quy hoạch và bundle tệp styles này.

## 3. Tổ chức Script (JS Frontend)
- Script JS phục vụ các effect, tính toán trên Frontend được khai báo trong: `resources/scripts/theme/` (Có thể tạo file js mới tại đây sau đó import vào `index.js` chính của theme).
- Hạn chế tối đa dùng jQuery. Khuyến khích Vanilla JS hoặc tích hợp theo thư viện animation có sẵn (GSAP,...).
- Không tự ý nhúng thẻ `<script>` vào `render.php`.

## 4. Tối ưu Hiệu năng (Performance) và Clean Code trong `render.php`
- **Hình ảnh:** BẮT BUỘC sử dụng hàm helper theme để render để tự động xuất WebP, kích thước đính kèm: `theResponsivePostThumbnail($size, $args)` hoặc `getResponsivePostThumbnail($post_id, ...)`.
- **Custom Post Queries:** 
  - Nếu query list bài viết bằng `WP_Query`, hãy thêm dòng `'no_found_rows' => true` để loại bỏ thao tác đếm tổng số trang cực chậm trong SQL nếu block đó không có tính năng Phân Trang (Pagination).
  - Không viết đoạn code `update_post_caches()` thừa vào vì core WP_Query mặc định đã làm sẵn tác vụ chống N+1 Caching.
  - **Tối kỵ `orderby => 'rand'`:** Rất tốn CPU cho MySQL DB, thay thế bằng cách get array data bình thường rồi dùng `shuffle($query->posts)` tại vòng lặp PHP nếu list trả về nhỏ. 

## 5. Bảo mật (Security) trong PHP
- **XSS & Data Wrapping:** Tất cả biến động in ra HTML phải qua hàm escape: 
  - `esc_html()` cho Text
  - `esc_attr()` cho class, id, attribute html
  - `esc_url()` cho link (href)
  - `wp_kses_post()` cho Rich text có format của wysiwyg editor content.
- **SQLi:** Mọi custom MySQL query được code thẳng với class `$wpdb` đều bắt buộc đưa vào `$wpdb->prepare()`.

## 6. Accessiblity Component (A11y)
- Thẻ block bao bọc bên ngoài thường là `<section>`, hãy cẩn thật định dạng Heading (h2, h3, h4) không bị sai cấp độ tài liệu. Tương phản màu sắc rõ ràng. Thêm các thẻ `aria-label` cho những btn, thẻ `<a>` mà mất Text/chỉ hiện viền icon.

## 7. Cập nhật & Biên dịch 
Sau khi bạn đã hoàn thiện code cho block mới. Hãy nhắc nhở tôi chạy (hoặc tự chạy nếu có turbo) câu lệnh Build của theme:
`yarn install && yarn build` (hoặc `npm run build`) tại thư mục root của theme để Webpack thực hiện phiên dịch file JS Editor, JS frontend và SCSS Layout.
