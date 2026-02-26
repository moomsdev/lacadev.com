<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Rate limiting helper for AJAX requests
 */
function lacadev_check_rate_limit($action_name, $limit = 20, $period = 60) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $transient_key = 'rate_limit_' . $action_name . '_' . md5($ip);
    $request_count = get_transient($transient_key);
    
    if ($request_count === false) {
        set_transient($transient_key, 1, $period);
        return true;
    }
    
    if ($request_count >= $limit) {
        wp_send_json_error([
            'message' => __('Quá nhiều requests. Vui lòng thử lại sau.', 'laca')
        ], 429);
        exit;
    }
    
    set_transient($transient_key, $request_count + 1, $period);
    return true;
}

/**
 * Improve search: ONLY search in title, accent-insensitive (Vietnamese support)
 */
function lacadev_improve_search_relevance($search, $wp_query) {
    global $wpdb;
    
    if (empty($wp_query->query_vars['s'])) {
        return $search;
    }
    
    $search_term = $wpdb->esc_like($wp_query->query_vars['s']);
    
    // Search ONLY in post_title with accent-insensitive collation
    // utf8mb4_unicode_ci ignores accents: "se" matches "sẽ", "sê", "sế", etc.
    $search = " AND ({$wpdb->posts}.post_title COLLATE utf8mb4_unicode_ci LIKE '%{$search_term}%')";
    
    return $search;
}

/**
 * AJAX Search Handler
 */
add_action('wp_ajax_nopriv_ajax_search', 'mms_ajax_search');
add_action('wp_ajax_ajax_search', 'mms_ajax_search');

function mms_ajax_search() {
    // Rate limiting: 20 requests per minute
    lacadev_check_rate_limit('ajax_search', 20, 60);
    
    // Security check
    check_ajax_referer('theme_search_nonce', 'nonce');

    // Get search query
    $search_query = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
    
    // Add search relevance filter (title-only, accent-insensitive)
    add_filter('posts_search', 'lacadev_improve_search_relevance', 10, 2);
    
    $html = '';
    $has_results = false;
    
    // Get all public post types
    $post_types = get_post_types(['public' => true], 'objects');
    
    // Organize post types by category
    $organized_types = [
        'product' => [],
        'post' => [],
        'page' => [],
        'other' => []
    ];
    
    foreach ($post_types as $post_type) {
        $type_name = $post_type->name;
        
        // Skip attachments
        if ($type_name === 'attachment') {
            continue;
        }
        
        // Categorize
        if ($type_name === 'product') {
            $organized_types['product'][] = $type_name;
        } elseif ($type_name === 'post') {
            $organized_types['post'][] = $type_name;
        } elseif ($type_name === 'page') {
            $organized_types['page'][] = $type_name;
        } else {
            $organized_types['other'][] = $type_name;
        }
    }
    
    // Search Products (WooCommerce)
    if (!empty($organized_types['product']) && class_exists('WooCommerce')) {
        $products = new WP_Query([
            'post_type' => 'product',
            'posts_per_page' => 2, // Only show 2 items
            's' => $search_query,
            'post_status' => 'publish',
            'no_found_rows' => false,
            'orderby' => 'date',
            'order' => 'DESC',
        ]);
        
        if ($products->have_posts()) {
            $has_results = true;
            $displayed_count = $products->post_count;
            $total_products = $products->found_posts;
            
            $html .= '<div class="search-results__section">';
            $html .= '<h3 class="search-results__title"><strong>Sản phẩm liên quan</strong> <span class="search-results__count">(hiển thị ' . $displayed_count . '/' . $total_products . ' sản phẩm)</span>:</h3>';
            $html .= '<div class="search-results__list">';
            
            while ($products->have_posts()) {
                $products->the_post();
                
                $html .= '<a href="' . esc_url(get_permalink()) . '" class="search-results__item">';
                $html .= '<div class="search-results__image">';
                $html .= getResponsivePostThumbnail(get_the_ID(), 'mobile', ['alt' => get_the_title()]);
                $html .= '</div>';
                $html .= '<div class="search-results__content">';
                $html .= '<h4 class="search-results__item-title">' . esc_html(get_the_title()) . '</h4>';
                $html .= '</div>';
                $html .= '</a>';
            }
            
            $html .= '</div></div>';
            wp_reset_postdata();
        }
    }
    
    // Search Posts
    if (!empty($organized_types['post'])) {
        $posts = new WP_Query([
            'post_type' => 'post',
            'posts_per_page' => 2, // Only show 2 items
            's' => $search_query,
            'post_status' => 'publish',
            'no_found_rows' => false,
            'orderby' => 'date',
            'order' => 'DESC',
        ]);
        
        if ($posts->have_posts()) {
            $has_results = true;
            $displayed_count = $posts->post_count;
            $total_posts = $posts->found_posts;
            
            $html .= '<div class="search-results__section">';
            $html .= '<h3 class="search-results__title"><strong>Bài viết liên quan</strong> <span class="search-results__count">(hiển thị ' . $displayed_count . '/' . $total_posts . ' bài viết)</span>:</h3>';
            $html .= '<div class="search-results__list">';
            
            while ($posts->have_posts()) {
                $posts->the_post();
                
                $html .= '<a href="' . esc_url(get_permalink()) . '" class="search-results__item">';
                $html .= '<div class="search-results__image">';
                $html .= getResponsivePostThumbnail(get_the_ID(), 'mobile', ['alt' => get_the_title()]);
                $html .= '</div>';
                $html .= '<div class="search-results__content">';
                $html .= '<h4 class="search-results__item-title">' . esc_html(get_the_title()) . '</h4>';
                $html .= '</div>';
                $html .= '</a>';
            }
            
            $html .= '</div></div>';
            wp_reset_postdata();
        }
    }
    
    // Search Pages
    if (!empty($organized_types['page'])) {
        $pages = new WP_Query([
            'post_type' => 'page',
            'posts_per_page' => 2, // Only show 2 items
            's' => $search_query,
            'post_status' => 'publish',
            'no_found_rows' => false,
            'orderby' => 'date',
            'order' => 'DESC',
        ]);
        
        if ($pages->have_posts()) {
            $has_results = true;
            $displayed_count = $pages->post_count;
            $total_pages = $pages->found_posts;
            
            $html .= '<div class="search-results__section">';
            $html .= '<h3 class="search-results__title"><strong>Trang liên quan</strong> <span class="search-results__count">(hiển thị ' . $displayed_count . '/' . $total_pages . ' trang)</span>:</h3>';
            $html .= '<div class="search-results__list">';
            
            while ($pages->have_posts()) {
                $pages->the_post();
                
                $html .= '<a href="' . esc_url(get_permalink()) . '" class="search-results__item">';
                $html .= '<div class="search-results__image">';
                $html .= getResponsivePostThumbnail(get_the_ID(), 'mobile', ['alt' => get_the_title()]);
                $html .= '</div>';
                $html .= '<div class="search-results__content">';
                $html .= '<h4 class="search-results__item-title">' . esc_html(get_the_title()) . '</h4>';
                $html .= '</div>';
                $html .= '</a>';
            }
            
            $html .= '</div></div>';
            wp_reset_postdata();
        }
    }
    
    // Search Other Custom Post Types
    if (!empty($organized_types['other'])) {
        foreach ($organized_types['other'] as $custom_type) {
            $custom_posts = new WP_Query([
                'post_type' => $custom_type,
                'posts_per_page' => 2, // Only show 2 items
                's' => $search_query,
                'post_status' => 'publish',
                'no_found_rows' => false,
                'orderby' => 'date',
                'order' => 'DESC',
            ]);
            
            if ($custom_posts->have_posts()) {
                $has_results = true;
                $post_type_obj = get_post_type_object($custom_type);
                $type_label = $post_type_obj->labels->name ?? $custom_type;
                $displayed_count = $custom_posts->post_count;
                $total_custom = $custom_posts->found_posts;
                
                $html .= '<div class="search-results__section">';
                $html .= '<h3 class="search-results__title"><strong>' . esc_html($type_label) . ' liên quan</strong> <span class="search-results__count">(hiển thị ' . $displayed_count . '/' . $total_custom . ')</span>:</h3>';
                $html .= '<div class="search-results__list">';
                
                while ($custom_posts->have_posts()) {
                    $custom_posts->the_post();
                    
                    $html .= '<a href="' . esc_url(get_permalink()) . '" class="search-results__item">';
                    $html .= '<div class="search-results__image">';
                    $html .= getResponsivePostThumbnail(get_the_ID(), 'mobile', ['alt' => get_the_title()]);
                    $html .= '</div>';
                    $html .= '<div class="search-results__content">';
                    $html .= '<h4 class="search-results__item-title">' . esc_html(get_the_title()) . '</h4>';
                    $html .= '</div>';
                    $html .= '</a>';
                }
                
                $html .= '</div></div>';
                wp_reset_postdata();
            }
        }
    }
    
    // No results found
    if (!$has_results) {
        $html = '<div class="search-results__empty">';
        $html .= '<p>Không tìm thấy kết quả nào cho "<strong>' . esc_html($search_query) . '</strong>"</p>';
        $html .= '</div>';
    }
    
    // Remove search filter after use
    remove_filter('posts_search', 'lacadev_improve_search_relevance', 10);
    
    // Return HTML
    echo $html;
    wp_die();
}

// =============================================================================
// AJAX HANDLERS - CUSTOM SORT, THUMBNAIL, CONTACT FORM, LOAD PAGE
// =============================================================================

if (!defined('ABSPATH')) {
    exit;
}

// -----------------------------------------------------------------------------
// AJAX: Update Custom Sort Order
// -----------------------------------------------------------------------------
/**
 * Cập nhật thứ tự sắp xếp (menu_order) cho các post qua Ajax.
 *
 * @action wp_ajax_update_custom_sort_order
 */
add_action('wp_ajax_update_custom_sort_order', 'updateCustomSortOrder');
function updateCustomSortOrder() {
    // Basic permissions check
    if (!current_user_can('edit_posts')) {
        wp_send_json_error(['message' => 'Unauthorized access.']);
    }

    // Kiểm tra nonce để bảo vệ CSRF
    check_ajax_referer('update_custom_sort_order', 'nonce');
    
    // Kiểm tra tham số đầu vào
    if (empty($_POST['post_ids']) || empty($_POST['current_page'])) {
        wp_send_json_error(['message' => 'Missing parameters.']);
    }

    $postIds = array_map('absint', wp_unslash($_POST['post_ids']));
    $currentPage = absint(wp_unslash($_POST['current_page']));
    $order = (($currentPage - 1) * count($postIds)) + 1;

    // Cập nhật menu_order cho từng post
    foreach ($postIds as $postId) {
        wp_update_post([
            'ID'         => $postId,
            'menu_order' => $order,
        ]);
        $order++;
    }

    wp_send_json_success();
}

// -----------------------------------------------------------------------------
// AJAX: Update Post Thumbnail ID
// -----------------------------------------------------------------------------
/**
 * Cập nhật thumbnail (ảnh đại diện) cho post qua Ajax.
 *
 * @action wp_ajax_update_post_thumbnail_id
 */
add_action('wp_ajax_update_post_thumbnail_id', 'updatePostThumbnailId');

function updatePostThumbnailId() {
    if (!current_user_can('edit_posts')) {
        wp_send_json_error(['message' => 'Unauthorized access.']);
    }

    // Kiểm tra nonce để bảo vệ CSRF
    check_ajax_referer('update_post_thumbnail', 'nonce');
    
    // Kiểm tra các tham số post_id và attachment_id
    if (empty($_POST['post_id']) || empty($_POST['attachment_id'])) {
        wp_send_json_error(['message' => 'Missing parameters.']);
    }

    $postId = absint(wp_unslash($_POST['post_id']));
    $attachmentId = absint(wp_unslash($_POST['attachment_id']));

    // Cập nhật _thumbnail_id bằng hàm update_post_meta
    if (update_post_meta($postId, '_thumbnail_id', $attachmentId)) {
        wp_send_json_success(['message' => 'Thumbnail updated.']);
    } else {
        wp_send_json_error(['message' => 'Failed to update thumbnail.']);
    }
}

// -----------------------------------------------------------------------------
// AJAX: Remove Post Thumbnail
// -----------------------------------------------------------------------------
/**
 * Xóa thumbnail (ảnh đại diện) cho post qua Ajax.
 *
 * @action wp_ajax_remove_post_thumbnail
 */
add_action('wp_ajax_remove_post_thumbnail', 'removePostThumbnail');

function removePostThumbnail() {
    if (!current_user_can('edit_posts')) {
        wp_send_json_error(['message' => 'Unauthorized access.']);
    }

    // Kiểm tra nonce để bảo vệ CSRF
    check_ajax_referer('update_post_thumbnail', 'nonce');
    
    // Kiểm tra tham số post_id
    if (empty($_POST['post_id'])) {
        wp_send_json_error(['message' => 'Missing post ID.']);
    }

    $postId = absint(wp_unslash($_POST['post_id']));

    // Xóa thumbnail bằng hàm delete_post_thumbnail
    if (delete_post_thumbnail($postId)) {
        wp_send_json_success(['message' => 'Thumbnail removed.']);
    } else {
        wp_send_json_error(['message' => 'Failed to remove thumbnail.']);
    }
}

// -----------------------------------------------------------------------------
// AJAX: Check submission status for returning users
// -----------------------------------------------------------------------------
add_action('wp_ajax_laca_check_submission_status', 'lacadev_check_submission_status');
add_action('wp_ajax_nopriv_laca_check_submission_status', 'lacadev_check_submission_status');

function lacadev_check_submission_status() {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $last_submission = get_transient('laca_contact_' . md5($ip));
    
    if ($last_submission) {
        wp_send_json_success([
            'submitted' => true,
            'time' => date_i18n('H:i - d/m/Y', $last_submission),
            'message' => sprintf(__('Bạn đã gửi lời nhắn vào lúc %s. Bạn có muốn gửi thêm nội dung khác?', 'laca'), date_i18n('H:i - d/m/Y', $last_submission))
        ]);
    }
    
    wp_send_json_success(['submitted' => false]);
}

// -----------------------------------------------------------------------------
// AJAX: Gửi form liên hệ (Contact Form)
// -----------------------------------------------------------------------------
add_action('wp_ajax_nopriv_laca_contact_submit', 'lacadev_handle_contact_submit');
add_action('wp_ajax_laca_contact_submit', 'lacadev_handle_contact_submit');

function lacadev_handle_contact_submit() {
    // 1. Security check
    check_ajax_referer('laca_contact_nonce', 'nonce');

    // 2. Rate Limiting Check
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $transient_key = 'laca_contact_' . md5($ip);
    $last_submission = get_transient($transient_key);
    
    // If they haven't explicitly confirmed they want to resubmit
    if ($last_submission && !isset($_POST['resubmit_confirmed'])) {
         wp_send_json_error([
             'code' => 'recently_submitted',
             'time' => date_i18n('H:i - d/m/Y', $last_submission),
             'message' => sprintf(__('Bạn vừa gửi tin nhắn vào lúc %s. Đợi một chút rồi gửi tiếp nhé!', 'laca'), date_i18n('H:i - d/m/Y', $last_submission))
         ]);
    }

    // 3. Verify reCAPTCHA v3
    $recaptcha_response = isset($_POST['recaptcha_response']) ? sanitize_text_field($_POST['recaptcha_response']) : '';
    $secret_key = carbon_get_theme_option('recaptcha_secret_key');
    $score_threshold = (float) carbon_get_theme_option('recaptcha_score') ?: 0.5;

    if (!empty($secret_key)) {
        $response = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', [
            'body' => [
                'secret'   => $secret_key,
                'response' => $recaptcha_response,
                'remoteip' => $ip
            ]
        ]);

        if (is_wp_error($response)) {
             wp_send_json_error(['message' => __('Không thể kết nối với Google reCAPTCHA.', 'laca')]);
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);
        if (!$data['success']) {
            wp_send_json_error(['message' => __('Xác thực reCAPTCHA thất bại.', 'laca')]);
        }
        if ($data['score'] < $score_threshold) {
            wp_send_json_error(['message' => __('Hệ thống nghi ngờ bạn là bot (Điểm thấp).', 'laca')]);
        }
    }

    // 4. Sanitize Input
    $name    = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
    $email   = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
    $subject = isset($_POST['subject']) ? sanitize_text_field($_POST['subject']) : '';
    $message = isset($_POST['message']) ? sanitize_textarea_field($_POST['message']) : '';

    if (empty($name) || empty($email) || empty($message)) {
        wp_send_json_error(['message' => __('Vui lòng điền đầy đủ các thông tin bắt buộc.', 'laca')]);
    }

    if (!is_email($email)) {
        wp_send_json_error(['message' => __('Địa chỉ email không hợp lệ.', 'laca')]);
    }

    // 5. Send Email
    $to = carbon_get_theme_option('email') ?: get_option('admin_email');
    $site_name = get_bloginfo('name');
    
    $email_subject = sprintf('[%s] Lời nhắn mới từ %s', $site_name, $name);
    if ($subject) {
        $email_subject .= ': ' . $subject;
    }

    $headers = [
        'Content-Type: text/html; charset=UTF-8',
        'Reply-To: ' . $name . ' <' . $email . '>'
    ];

    $email_body = "
        <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <h2 style='color: #007bff;'>Lời nhắn mới từ trang Liên hệ</h2>
            <p><strong>Người gửi:</strong> {$name}</p>
            <p><strong>Email:</strong> {$email}</p>
            <p><strong>Tiêu đề:</strong> " . ($subject ?: 'Không có') . "</p>
            <hr style='border: 0; border-top: 1px solid #eee;'>
            <p><strong>Nội dung:</strong></p>
            <p style='background: #f9f9f9; padding: 15px; border-left: 4px solid #007bff;'>
                " . nl2br($message) . "
            </p>
            <hr style='border: 0; border-top: 1px solid #eee;'>
            <p style='font-size: 12px; color: #888;'>Tin nhắn này được gửi tự động từ hệ thống {$site_name}.</p>
        </div>
    ";

    $sent = wp_mail($to, $email_subject, $email_body, $headers);

    if ($sent) {
        // Set transient for 30 minutes to prevent spam
        set_transient($transient_key, time(), 30 * MINUTE_IN_SECONDS);
        wp_send_json_success(['message' => __('Lời nhắn của bạn đã được gửi đi thành công. Tôi sẽ phản hồi sớm nhé!', 'laca')]);
    } else {
        wp_send_json_error(['message' => __('Đã có lỗi xảy ra khi gửi mail. Vui lòng thử lại sau.', 'laca')]);
    }
}

// -----------------------------------------------------------------------------
// AJAX: Load Page Content
// -----------------------------------------------------------------------------
/**
 * Tải nội dung trang qua Ajax (dùng cho các yêu cầu động).
 *
 * @action wp_ajax_nopriv_get_page
 * @action wp_ajax_get_page
 */
add_action('wp_ajax_nopriv_get_page', 'ajaxGetPage');
add_action('wp_ajax_get_page', 'ajaxGetPage');

function ajaxGetPage() {
    // Security check
    check_ajax_referer('theme_nonce', 'nonce');

    $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
    
    if (!$post_id) {
        wp_send_json_error(['message' => 'Missing Post ID']);
    }

    // Setup global post data
    global $post;
    $post = get_post($post_id);
    
    if (!$post || ($post->post_status !== 'publish' && !current_user_can('read_post', $post_id))) {
        wp_send_json_error(['message' => 'Post not found or unauthorized']);
    }
    
    setup_postdata($post);

    ob_start();
    // Load the page template. Note: This will load 'page.php' from the theme root.
    // If you need a specific part, use get_template_part('template-parts/content', 'page');
    get_template_part('page'); 
    $content = ob_get_clean();
    
    wp_reset_postdata();
    wp_send_json_success($content);
}

// -----------------------------------------------------------------------------
// AJAX: Load More Search Results
// -----------------------------------------------------------------------------
/**
 * Load more search results for specific post type
 *
 * @action wp_ajax_nopriv_load_more_search
 * @action wp_ajax_load_more_search
 */
add_action('wp_ajax_nopriv_load_more_search', 'lacadev_load_more_search');
add_action('wp_ajax_load_more_search', 'lacadev_load_more_search');

function lacadev_load_more_search() {
    // Security check
    check_ajax_referer('theme_search_nonce', 'nonce');
    
    // Get parameters
    $post_type = isset($_POST['post_type']) ? sanitize_text_field($_POST['post_type']) : '';
    $search_query = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
    $paged = isset($_POST['paged']) ? absint($_POST['paged']) : 1;
    
    if (empty($post_type) || empty($search_query)) {
        wp_send_json_error(['message' => 'Missing parameters']);
        return;
    }
    
    // Add search filter (title-only, accent-insensitive)
    add_filter('posts_search', 'lacadev_improve_search_relevance', 10, 2);
    
    // Query posts
    $query = new WP_Query([
        'post_type' => $post_type,
        'posts_per_page' => 8,
        's' => $search_query,
        'post_status' => 'publish',
        'paged' => $paged,
    ]);
    
    // Remove search filter
    remove_filter('posts_search', 'lacadev_improve_search_relevance', 10);
    
    if (!$query->have_posts()) {
        wp_send_json_error(['message' => 'No more posts']);
        return;
    }
    
    // Map post_type to template part
    // Pages use 'post' template since loop-page.php doesn't exist
    $template_slug = $post_type;
    if ($post_type === 'page') {
        $template_slug = 'post';
    }
    
    // Generate HTML
    ob_start();
    while ($query->have_posts()) {
        $query->the_post();
        get_template_part('template-parts/loop', $template_slug);
    }
    wp_reset_postdata();
    $html = ob_get_clean();
    
    // Return response
    wp_send_json_success([
        'html' => $html,
        'has_more' => $paged < $query->max_num_pages,
        'next_page' => $paged + 1,
        'max_pages' => $query->max_num_pages,
    ]);
}
