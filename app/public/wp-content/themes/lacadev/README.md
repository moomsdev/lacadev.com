# ⚡ La Cà Dev Theme - High Performance WordPress Theme

> *Theme WordPress được code giữa những chuyến đi – bởi La Cà Dev, một kẻ lang thang mê phím và bánh xe.*

**Version:** 3.1  
**Author:** La Cà Dev  
**License:** Private

---

## 🌟 Tổng Quan

Theme WordPress hiệu suất cao được xây dựng với triết lý "Performance First" - loại bỏ dependencies nặng nề, áp dụng kỹ thuật modern và tối ưu hóa từng chi tiết.

### ✨ Điểm Nổi Bật

**🚀 Siêu Tốc Độ**
- ✅ **Zero jQuery:** Toàn bộ frontend code viết bằng **Vanilla JavaScript**
- ✅ **Code Splitting:** Webpack tách vendors bundle (685KB) và theme code (12KB)
- ✅ **Minification:** JS/CSS được minify tối đa với Terser + CssMinimizerPlugin
- ✅ **Smart Loading:** Vendors load blocking, theme scripts defer
- ✅ **Image Optimization:** PNG giảm 64%, tự động optimize với ImageMinimizerPlugin

**📊 Web Vitals Monitoring**
- ✅ Giám sát LCP, CLS, FID realtime trong development
- ✅ Color-coded ratings (Tốt ✓ / Cần cải thiện ⚠ / Kém ✗)
- ✅ Detailed metrics với thresholds theo chuẩn Google

**🛡️ Bảo Mật**
- ✅ AJAX requests bảo vệ bằng **Nonce Verification**
- ✅ Input/Output sanitization và escaping

**🎨 Modern Architecture**
- ✅ Webpack 5 với hot reload (BrowserSync)
- ✅ SCSS với modern-compiler
- ✅ PostCSS với autoprefixer
- ✅ ES6+ với Babel transpilation

---

## 📦 Yêu Cầu Hệ Thống

- **Node.js:** v20+ (recommended: v20 LTS)
- **Yarn:** Latest version
- **PHP:** 7.4+
- **Composer:** 2.x
- **WordPress:** 5.8+

---

## 🚀 Cài Đặt

### 1. Clone và Setup Dependencies

```bash
# Di chuyển vào thư mục theme
cd app/public/wp-content/themes/lacadev

# Cài đặt PHP dependencies
composer install

# Cài đặt Node dependencies
yarn install
```

### 2. Development Workflow

```bash
# Development mode với watch + hot reload
yarn dev

# Chạy trên: http://localhost:3000
# Backend: http://lacadev.local
```

### 3. Production Build

```bash
# Build cho production (minify, optimize)
yarn build
```

---

## 📂 Cấu Trúc Thư Mục

```
lacadev/
├── app/                    # PHP Logic
│   ├── helpers/           # Helper functions
│   ├── routes/            # Route definitions
│   └── src/               # Core classes (PostTypes, Settings, etc)
│
├── resources/             # Source Assets (EDIT HERE)
│   ├── build/            # Webpack configs
│   ├── scripts/          # JavaScript source
│   │   ├── theme/       # Frontend JS
│   │   ├── admin/       # Admin JS
│   │   └── login/       # Login page JS
│   ├── styles/           # SCSS source
│   │   ├── theme/       # Frontend styles
│   │   ├── admin/       # Admin styles
│   │   └── login/       # Login styles
│   └── images/           # Source images
│
├── dist/                  # Compiled Assets (DON'T EDIT)
│   ├── vendors.js        # Node modules bundle (685KB minified)
│   ├── theme.js          # Theme code (12KB minified)
│   ├── admin.js          # Admin code (13KB minified)
│   └── styles/           # Compiled CSS
│
├── theme/                 # WordPress Template Files
│   ├── setup/            # Theme setup (hooks, filters)
│   ├── views/            # Template parts
│   └── functions.php     # Entry point
│
└── block-gutenberg/       # Gutenberg blocks
```

---

## �️ Các Lệnh Quan Trọng

| Command | Description | When to Use |
|---------|-------------|-------------|
| `composer install` | Cài đặt PHP dependencies | Lần đầu setup |
| `yarn install` | Cài đặt Node dependencies | Lần đầu setup |
| `yarn dev` | Development mode + watch | Khi đang code |
| `yarn build` | Production build (minified) | Trước khi deploy |
| `yarn build:theme` | Build theme assets only | Debug theme bundle |
| `yarn build:blocks` | Build Gutenberg blocks only | Debug blocks |

---

## ⚡ Production Build Optimization

### Minification Settings

**JavaScript (Terser)**
- ✅ Minification: ON
- ✅ Drop console.log: YES (production only)
- ✅ Mangle variables: YES
- ✅ Mangle properties: NO (preserve object keys)
- ✅ Remove comments: YES
- ✅ Reserved keywords: `['Swal', 'themeData', 'ajaxurl_params', 'adminI18n']`

**CSS (CssMinimizerPlugin)**
- ✅ Minification: ON
- ✅ Remove all comments: YES
- ✅ Merge duplicate rules: YES

**Images (ImageMinimizerPlugin)**
- ✅ JPEG: MozJPEG (quality 85, progressive)
- ✅ PNG: PNGQuant (quality 70-90)
- ✅ GIF: Gifsicle (optimization level 3)
- ✅ SVG: SVGO with safe optimizations

### Bundle Sizes (After Optimization)

| File | Before | After | Reduction |
|------|--------|-------|-----------|
| `vendors.js` | 1.74 MB | **685 KB** | 60.6% 🎉 |
| `theme.js` | 31 KB | **12.1 KB** | 61.0% 🎉 |
| `admin.js` | 27.9 KB | **13 KB** | 53.4% |
| `theme.css` | 45 KB | **43.8 KB** | 2.7% |

**Total Theme Entrypoint:** 1.82 MB → **741 KB** (59.3% reduction)

---

## 📊 Performance Monitoring

### Web Vitals Tracking (Development)

Console output với color-coded ratings:

```
✓ LCP: 1234.56ms - TỐT ✓ (0 - 2500ms)
⚠ CLS: 0.15 - CẦN CẢI THIỆN ⚠ (0.1 - 0.25)  
✓ FID: 45.23ms - TỐT ✓ (0 - 100ms)

📊 Page Load Metrics:
  DOM Content Loaded: 847.23ms
  Page Load Complete: 1523.45ms
  DNS Lookup: 12.34ms
  TCP Connection: 45.67ms
```

### Thresholds (Google Standards)

| Metric | Good ✓ | Needs Improvement ⚠ | Poor ✗ |
|--------|--------|---------------------|--------|
| **LCP** | ≤ 2.5s | 2.5s - 4.0s | > 4.0s |
| **FID** | ≤ 100ms | 100ms - 300ms | > 300ms |
| **CLS** | ≤ 0.1 | 0.1 - 0.25 | > 0.25 |

---

## 🔧 Script Loading Strategy

### Frontend
- **vendors.js:** Load in footer with defer
- **theme.js:** Load in footer with defer (depends on vendors.js)

### Admin Area
- **vendors.js:** Load in `<head>` **blocking** (no defer)
- **admin.js:** Load in footer with defer (depends on vendors.js)

> ⚠️ **Critical:** Admin vendors.js MUST load blocking để đảm bảo SweetAlert2 available trước khi admin.js execute.

---

## 💡 Coding Standards

### JavaScript
1. ✅ **NO jQuery** (trừ khi plugin bên thứ 3 require)
2. ✅ Use `const/let` instead of `var`
3. ✅ Use arrow functions where appropriate
4. ✅ Use template literals for string concatenation
5. ✅ Always use `fetch()` instead of jQuery.ajax

### AJAX Security
```javascript
// Frontend - Always send nonce
fetch('/wp-admin/admin-ajax.php', {
    method: 'POST',
    body: new URLSearchParams({
        action: 'my_action',
        nonce: themeData.nonce,
        data: value
    })
});

// Backend - Always verify nonce
check_ajax_referer('theme_nonce', 'nonce');
```

### PHP
1. ✅ Always sanitize input: `sanitize_text_field()`, `wp_kses_post()`
2. ✅ Always escape output: `esc_html()`, `esc_attr()`, `esc_url()`
3. ✅ Use nonces for all forms and AJAX requests
4. ✅ Follow WordPress coding standards

---

## 🐛 Common Issues & Solutions

### Admin JS không hoạt động sau `yarn build`

**Nguyên nhân:** vendors.js chưa load hoặc bị defer  
**Giải pháp:** Đảm bảo vendors.js load blocking trong admin:

```php
wp_enqueue_script('theme-vendors-js', $url, [], $version, false); // false = in head
```

### Console.log bị xóa trong production

**Nguyên nhân:** Terser config có `drop_console: true`  
**Giải pháp:** Chỉ dùng console.error() hoặc disable drop_console trong development

### CLS cao (> 0.25)

**Nguyên nhân:** Layout shift khi load images/fonts  
**Giải pháp:** 
- Thêm `width` và `height` cho tất cả images
- Dùng font-display: swap cho web fonts
- Reserve space cho dynamic content

---

## 📝 Changelog

### Version 3.1 (Current)
- ✅ Enabled production minification (JS/CSS)
- ✅ Implemented vendors.js code splitting
- ✅ Added comprehensive Web Vitals monitoring
- ✅ Fixed admin.js loading issues
- ✅ Optimized image compression (64% reduction)
- ✅ Added property name preservation in mangle config
- ✅ Total bundle size reduction: 59.3%

---

## 📞 Support & Contact

**Author:** La Cà Dev  
**Email:** mooms.dev@gmail.com  
**Website:** https://lacadev.com

---

*Happy Coding! 🚀*  
**La Cà Dev - Code giữa những chuyến đi**
