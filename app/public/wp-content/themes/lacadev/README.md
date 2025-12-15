# ⚡ La Cà Dev Theme (v3.1)

Theme WordPress hiệu suất cao, "Zero jQuery", tối ưu hóa cho tốc độ và trải nghiệm người dùng.

## 🌟 Điểm Nổi Bật

- **🚀 Siêu Tốc Độ:** Frontend Vanilla JS, Webpack bundling, tách code thông minh.
- **⚡ Critical CSS:** Tự động inline CSS quan trọng, FCP cực nhanh.
- **🛡️ Bảo Mật:** Nonce verification toàn diện.
- **📊 Web Vitals:** Giám sát hiệu suất realtime (LCP, CLS, FID).

## 🚀 Quick Start

**Yêu cầu:** Node.js v20+, Yarn, PHP 7.4+, Composer.

```bash
# 1. Setup
composer install && yarn install

# 2. Development (Watch + Hot Reload tại localhost:3000)
yarn dev

# 3. Production Build (Minify + Optimize)
yarn build
```

## � Commands

| Command | Chức năng | Khi nào chạy? |
|---------|-----------|---------------|
| `yarn dev` | Chạy dev server | Khi đang code |
| `yarn build` | Build production | Trước khi deploy |
| `yarn critical` | Tạo Critical CSS | Khi sửa Header/Home |
| `yarn build:theme` | Chỉ build theme | Debug theme assets |

## 📂 Cấu Trúc Dự Án

- **`app/`** (PHP Logic): Nơi chứa logic, post types, helpers.
- **`resources/`** (Source Code): **Sửa giao diện ở đây** (SCSS, JS, Images).
- **`dist/`** (Compiled): File đã build (Minified). **Không sửa ở đây**.
- **`theme/`** (Wrapper): File cấu trúc WordPress (`functions.php`, `header.php`...).

## � Workflow Lưu Ý

### 1. Critical CSS (`yarn critical`)
Tự động quét trang chủ và tạo CSS inline cho phần hiển thị đầu tiên (Header, Hero).
- Giúp web hiển thị nội dung **ngay lập tức**.
- **Lưu ý:** Cần chạy lại lệnh này nếu bạn sửa layout Header hoặc Hero section.

### 2. Assets Loading
- **Frontend:** `theme.js` load defer (footer).
- **Admin:** `vendors.js` load **blocking** (head) để đảm bảo thư viện (như SweetAlert2) sẵn sàng cho `admin.js`.

### 3. Minification
- `yarn build` sẽ tự động xóa `console.log` và nén code tối đa.
- Nếu code Admin lỗi, kiểm tra xem tên biến có bị đổi (mangle) sai không trong `webpack.production.js`.

---
*Author: La Cà Dev - Code giữa những chuyến đi*
Email: mooms.dev@gmail.com
Phone: 0989646766
website: https://lacadev.com