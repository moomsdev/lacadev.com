<?php

use Overtrue\Socialite\SocialiteManager;

add_action('wp_ajax_nopriv_user_login', 'mm_user_login');
add_action('wp_ajax_user_login', 'mm_user_login');

define('SOCIAL_DRIVER', [
    'google'   => [
        'client_id'     => get_option('google_client_id'),
        'client_secret' => get_option('google_client_secret'),
        'redirect'      => get_option('google_redirect_uri'),
    ],
]);
function mm_user_login()
{
    if (empty($_POST)) {
        return '';
    }

    if (!wp_verify_nonce($_POST['_token'], 'user_dang_nhap')) {
        return __('Token mismatch!', 'laca');
    }

    if (empty($_POST['user_login']) || empty($_POST['password'])) {
        return __('Tài khoản hoặc mật khẩu không đúng, vui lòng kiểm tra lại', 'laca');
    }

    $user = wp_signon([
        'user_login'    => $_POST['user_login'],
        'user_password' => $_POST['password'],
        'remember'      => true,
    ], false);

    if (is_wp_error($user)) {
        wp_send_json_error($user->get_error_message());
    }

    // Return success with alert data for AJAX handler
    wp_send_json_success([
        'redirect' => $_POST['redirect_to'],
        'alert' => [
            'title' => __('Xin chào, ', 'laca') . $user->user_email,
            'message' => __('Chúc mừng bạn đã đăng nhập thành công', 'laca')
        ]
    ]);
}

add_action('wp_ajax_nopriv_user_register', 'mm_user_register');
add_action('wp_ajax_user_register', 'mm_user_register');
function mm_user_register()
{
    if (empty($_POST)) {
        return '';
    }

    /* Kiem tra captcha */
    //    $captcha = $_POST['g-recaptcha-response'];
    //    if (empty($captcha)) return [
    //      'status'   => false,
    //      'loi_nhan' => __("Bạn chưa nhập mã xác nhận (chọn vào I'm not robot)", 'mtdev'),
    //    ];
    //    $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=6LfIuzYUAAAAADoy5KWNcnYkDumOexP1apz9Vv3v&response=" . $captcha . "&remoteip=" . $_SERVER['REMOTE_ADDR']);
    //    $response = json_decode($response, true);
    //    if (!$response['success']) return [
    //      'status'   => 'alert-success',
    //      'title'    => 'Cảnh báo',
    //      'loi_nhan' => __("Mã xác nhận chưa chính xác", 'mtdev'),
    //    ];

    /* Kiem tra token truoc khi xu ly */
    if (!wp_verify_nonce($_REQUEST['_token'], 'user_dang_ky_thanh_vien')) {
        return __('Token mismatch!', 'laca');
    }

    if (empty($_POST['first_name'])) {
        return __('Vui lòng nhập họ', 'laca');
    }

    if (empty($_POST['last_name'])) {
        return __('Vui lòng nhập tên', 'laca');
    }

    if (empty($_POST['email'])) {
        return __('Vui lòng nhập email', 'laca');
    }

    if (empty($_POST['password'])) {
        return __('Vui lòng nhập mật khẩu', 'laca');
    }

    if ($_POST['password'] !== $_POST['password_confirmation']) {
        return __('Vui lòng kiểm tra lại mật khẩu', 'laca');
    }

    $userParams = [
        'user_login'   => $_POST['user_login'],
        'user_email'   => $_POST['email'],
        'user_pass'    => $_POST['password_confirmation'],  // When creating an user, `user_pass` is expected.
        'display_name' => $_POST['last_name'],
    ];

    $idUser = wp_insert_user($userParams);

    update_user_meta($idUser, '_user_birthday', sanitize_text_field($_POST['birthday']));
    update_user_meta($idUser, '_user_gender', sanitize_text_field($_POST['sex']));

    if (is_wp_error($idUser)) {
        return $idUser->get_error_message();
    }

    return true;
}

add_action('wp_ajax_nopriv_user_reset_password', 'mm_user_reset_password');
add_action('wp_ajax_user_reset_password', 'mm_user_reset_password');
function mm_user_reset_password()
{
    wp_send_json_success(true);
}

add_action('wp_ajax_nopriv_google_login', 'googleLogin');
add_action('wp_ajax_google_login', 'googleLogin');
function googleLogin() {
    if (is_user_logged_in()) {
        socialCallbackRedirectUrl();
        die();
    }

    $redirect = !empty($_GET['redirect_to']) ? $_GET['redirect_to'] : null;
    $socialite = new SocialiteManager(SOCIAL_DRIVER);

    // Nếu có redirect_to thì override redirect URI
    if ($redirect) {
        /** @noinspection PhpUndefinedMethodInspection */
        $socialite->driver('google')->redirectUrl($redirect);
    }

    $response  = $socialite->driver('google')->redirect();
    echo $response;
}

function socialCallbackRedirectUrl()
{
    $user = wp_get_current_user();

    echo '<script>opener.socialLoginReturn({
                success: true,
                notification: {
                    title: "' . __('Xin chào, ', 'laca') . $user->user_email . '", 
                    message: "' . __('Chúc mừng bạn đã đăng nhập thành công', 'laca') . '"
                },
                redirect: "/"
            });window.close();</script>';
}

add_action('wp_ajax_nopriv_google_admin_callback', 'googleAdminCallback');
add_action('wp_ajax_google_admin_callback', 'googleAdminCallback');
/**
 * Xử lý callback đăng nhập/đăng ký admin bằng Google
 */
function googleAdminCallback() {
    $socialite = new SocialiteManager(SOCIAL_DRIVER);
    $user = $socialite->driver('google')->user();

    if (!$user || empty($user->getEmail())) {
        echo '<script>alert("Không lấy được thông tin từ Google!");window.close();</script>';
        exit;
    }

    // Kiểm tra email có phải admin không
    $admin_user = get_user_by('email', $user->getEmail());
    if ($admin_user && in_array('administrator', $admin_user->roles)) {
        // Đăng nhập user admin
        wp_set_current_user($admin_user->ID);
        wp_set_auth_cookie($admin_user->ID);

        echo '<script>opener.socialLoginReturn({
            success: true,
            notification: {
                title: "' . __('Xin chào, ', 'laca') . $admin_user->user_email . '", 
                message: "' . __('Chúc mừng bạn đã đăng nhập thành công với quyền admin', 'laca') . '"
            },
            redirect: "/wp-admin/"
        });window.close();</script>';
        exit;
    } else {
        echo '<script>alert("Tài khoản Google này không có quyền admin!");window.close();</script>';
        exit;
    }
}

/**
 * Thêm nút đăng nhập Google vào trang login
 */
add_action('login_form', function () {
    // Lấy URL để bắt đầu quá trình đăng nhập Google
    $google_login_url = admin_url('admin-ajax.php?action=google_login&redirect_to=' . urlencode(admin_url('admin-ajax.php?action=google_admin_callback')));
    ?>
    <div class="google-login-container">
        <a href="<?php echo esc_url($google_login_url); ?>" class="button google-login-button">
            <span class="google-icon">
                <svg viewBox="0 0 24 24" width="18" height="18" xmlns="http://www.w3.org/2000/svg">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                    <path d="M5.84 14.1c-.22-.66-.35-1.36-.35-2.1s.13-1.44.35-2.1V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l3.66-2.84z" fill="#FBBC05"/>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z" fill="#EA4335"/>
                </svg>
            </span>
            <span class="button-text">Tiếp tục với Google</span>
        </a>
    </div>
    <?php
});

/**
 * Set welcome alert flag on login
 */
add_action('wp_login', 'mm_set_login_alert_flag', 10, 2);
function mm_set_login_alert_flag($user_login, $user) {
    set_transient('show_welcome_alert_' . $user->ID, true, 60);
}

/**
 * Inject welcome alert script in admin
 */
add_action('admin_enqueue_scripts', 'mm_inject_login_alert_script', 20);
function mm_inject_login_alert_script() {
    $user_id = get_current_user_id();
    if (get_transient('show_welcome_alert_' . $user_id)) {
        delete_transient('show_welcome_alert_' . $user_id);
        $user = get_userdata($user_id);
        
        // Get current hour in 24-hour format
        $current_hour = (int) current_time('H');
        $current_day = strtolower(current_time('l')); // lower case full day name
        
        $title = '';
        $message = '';
        
        // Check for weekend first
        if ($current_day === 'saturday' || $current_day === 'sunday') {
            $title = __('Cuối tuần vui vẻ, ', 'laca') . $user->display_name;
            $message = sprintf(__('Hãy làm việc nhẹ nhàng và thư giãn nhé.', 'laca'), $user->display_name);
        } else {
            // Weekday logic
            if ($current_hour >= 5 && $current_hour < 12) {
                // Morning (5:00 - 11:59)
                $title = sprintf(__('Chào buổi sáng %s', 'laca'), $user->display_name);
                $message = __('Nhớ uống một tách cà phê trước khi bắt đầu nhé! ☕', 'laca');
            } elseif ($current_hour >= 12 && $current_hour < 18) {
                // Afternoon (12:00 - 17:59)
                $title = sprintf(__('Chào buổi chiều %s', 'laca'), $user->display_name);
                $message = __('Giữ vững năng lượng để hoàn thành nốt công việc nào! ☀️', 'laca');
            } else {
                // Evening/Night (18:00 - 4:59)
                $title = sprintf(__('Chào buổi tối %s', 'laca'), $user->display_name);
                $message = __('Đừng làm việc quá khuya nhé! 🌙', 'laca');
            }
        }
        
        $script = "
            localStorage.setItem('show_alert', JSON.stringify({
                title: '" . esc_js($title) . "',
                message: '" . esc_js($message) . "'
            }));
        ";
        wp_add_inline_script('theme-admin-js-bundle', $script, 'before');
    }
}
