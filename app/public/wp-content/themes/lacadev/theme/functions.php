<?php
use WPEmerge\Facades\WPEmerge;
use WPEmergeTheme\Facades\Theme;

if (!defined('ABSPATH')) {
    exit;
}

// =============================================================================
// SECURITY & PERMISSIONS
// =============================================================================

define('ALLOW_UNFILTERED_UPLOADS', true);
define('SUPER_USER', ['lacadev']);

// =============================================================================
// THEME INFORMATION
// =============================================================================

define('AUTHOR', [
    'name' => 'La Cà Dev',
    'email' => 'mooms.dev@gmail.com',
    'phone_number' => '0989 64 67 66',
    'website' => 'https://lacadev.com/',
    'date_started' => get_option('_theme_info_date_started'),
    'date_published' => get_option('_theme_info_date_publish'),
]);

// =============================================================================
// DIRECTORY CONSTANTS
// =============================================================================

// Directory Names
define('APP_APP_DIR_NAME', 'app');
define('APP_APP_HELPERS_DIR_NAME', 'helpers');
define('APP_APP_ROUTES_DIR_NAME', 'routes');
define('APP_APP_SETUP_DIR_NAME', 'setup');
define('APP_DIST_DIR_NAME', 'dist');
define('APP_RESOURCES_DIR_NAME', 'resources');
define('APP_THEME_DIR_NAME', 'theme');
define('APP_VENDOR_DIR_NAME', 'vendor');

// Theme Component Names
define('APP_THEME_USER_NAME', 'users');
define('APP_THEME_ECOMMERCE_NAME', 'users');
define('APP_THEME_POST_TYPE_NAME', 'post-types');
define('APP_THEME_TAXONOMY_NAME', 'taxonomies');
define('APP_THEME_WIDGET_NAME', 'widgets');
define('APP_THEME_BLOCK_NAME', 'blocks');
define('APP_THEME_WALKER_NAME', 'walkers');

// Directory Paths
define('APP_DIR', dirname(__DIR__) . DIRECTORY_SEPARATOR);
define('APP_APP_DIR', APP_DIR . APP_APP_DIR_NAME . DIRECTORY_SEPARATOR);
define('APP_APP_HELPERS_DIR', APP_APP_DIR . APP_APP_HELPERS_DIR_NAME . DIRECTORY_SEPARATOR);
define('APP_APP_ROUTES_DIR', APP_APP_DIR . APP_APP_ROUTES_DIR_NAME . DIRECTORY_SEPARATOR);
define('APP_RESOURCES_DIR', APP_DIR . APP_RESOURCES_DIR_NAME . DIRECTORY_SEPARATOR);
define('APP_THEME_DIR', APP_DIR . APP_THEME_DIR_NAME . DIRECTORY_SEPARATOR);
define('APP_VENDOR_DIR', APP_DIR . APP_VENDOR_DIR_NAME . DIRECTORY_SEPARATOR);
define('APP_DIST_DIR', APP_DIR . APP_DIST_DIR_NAME . DIRECTORY_SEPARATOR);

// Setup Directories
define('APP_APP_SETUP_DIR', APP_THEME_DIR . APP_APP_SETUP_DIR_NAME . DIRECTORY_SEPARATOR);
define('APP_APP_SETUP_ECOMMERCE_DIR', APP_THEME_DIR . APP_APP_SETUP_DIR_NAME . DIRECTORY_SEPARATOR . APP_THEME_ECOMMERCE_NAME . DIRECTORY_SEPARATOR);
define('APP_APP_SETUP_USER_DIR', APP_THEME_DIR . APP_APP_SETUP_DIR_NAME . DIRECTORY_SEPARATOR . APP_THEME_USER_NAME . DIRECTORY_SEPARATOR);
define('APP_APP_SETUP_POST_TYPE_DIR', APP_THEME_DIR . APP_APP_SETUP_DIR_NAME . DIRECTORY_SEPARATOR . APP_THEME_POST_TYPE_NAME . DIRECTORY_SEPARATOR);
define('APP_APP_SETUP_TAXONOMY_DIR', APP_THEME_DIR . APP_APP_SETUP_DIR_NAME . DIRECTORY_SEPARATOR . APP_THEME_TAXONOMY_NAME . DIRECTORY_SEPARATOR);
define('APP_APP_SETUP_WIDGET_DIR', APP_THEME_DIR . APP_APP_SETUP_DIR_NAME . DIRECTORY_SEPARATOR . APP_THEME_WIDGET_NAME . DIRECTORY_SEPARATOR);
define('APP_APP_SETUP_BLOCK_DIR', APP_THEME_DIR . APP_APP_SETUP_DIR_NAME . DIRECTORY_SEPARATOR . APP_THEME_BLOCK_NAME . DIRECTORY_SEPARATOR);
define('APP_APP_SETUP_WALKER_DIR', APP_THEME_DIR . APP_APP_SETUP_DIR_NAME . DIRECTORY_SEPARATOR . APP_THEME_WALKER_NAME . DIRECTORY_SEPARATOR);

// =============================================================================
// DEPENDENCIES & AUTOLOADING
// =============================================================================

// Load composer dependencies
if (file_exists(APP_VENDOR_DIR . 'autoload.php')) {
    require_once APP_VENDOR_DIR . 'autoload.php';
    \Carbon_Fields\Carbon_Fields::boot();
}

// Enable Theme shortcut
WPEmerge::alias('Theme', \WPEmergeTheme\Facades\Theme::class);

// Load helpers
require_once APP_APP_DIR . 'helpers.php';

// Bootstrap Theme
Theme::bootstrap(require APP_APP_DIR . 'config.php');

// Register hooks
require_once APP_APP_DIR . 'hooks.php';

// =============================================================================
// THEME SETUP
// =============================================================================

add_action('after_setup_theme', function () {
    // Load textdomain
    load_theme_textdomain('laca', APP_DIR . 'languages');

    // Load theme components
    require_once APP_APP_SETUP_DIR . 'theme-support.php';
    require_once APP_APP_SETUP_DIR . 'menus.php';
    require_once APP_APP_SETUP_DIR . 'ajax.php';

    // Load advanced optimization modules
    require_once APP_APP_SETUP_DIR . 'assets.php';
    require_once APP_APP_SETUP_DIR . 'performance.php';

    // Load Gutenberg blocks
    $blocks_dir = APP_APP_SETUP_DIR . '/blocks';
    $block_files = glob($blocks_dir . '/*.php');
    foreach ($block_files as $block_file) {
        require_once $block_file;
    }
});





// =============================================================================
// AUTOLOAD COMPONENTS
// =============================================================================

$folders = [
    APP_APP_SETUP_ECOMMERCE_DIR,
    APP_APP_SETUP_TAXONOMY_DIR,
    APP_APP_SETUP_WALKER_DIR,
];

foreach ($folders as $folder) {
    $filesPath = scandir($folder);
    if ($filesPath !== false) {
        foreach ($filesPath as $item) {
            $file = $folder . $item;
            if (is_file($file)) {
                require_once $folder . $item;
            }
        }
    }
}

// =============================================================================
// AJAX SEARCH (Optimized with Security & Caching)
// =============================================================================

/**
 * Improved AJAX search with security and caching
 */
function ajax_search()
{
    // Security check: verify nonce
    check_ajax_referer('theme_search_nonce', 'nonce');
    
    // Sanitize search query
    $search_query = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
    
    if (empty($search_query) || strlen($search_query) < 2) {
        wp_send_json_error(['message' => __('Vui lòng nhập ít nhất 2 ký tự', 'laca')]);
    }
    
    // Create cache key - include user ID for personalized results if needed
    $cache_key = 'ajax_search_' . md5($search_query . get_current_user_id());
    
    // Try to get cached results
    $cached_results = get_transient($cache_key);
    if ($cached_results !== false) {
        echo $cached_results;
        wp_die();
    }
    
    // Start output buffering to capture results for caching
    ob_start();
    
    // Query arguments
    $args = array(
        'post_type' => ['post', 'service', 'blog'],
        'posts_per_page' => 10,
        's' => $search_query,
        'post_status' => 'publish', // Only published posts
        'no_found_rows' => true, // Performance optimization
        'update_post_meta_cache' => false, // Performance optimization
        'update_post_term_cache' => false, // Performance optimization
    );
    
    $query = new WP_Query($args);
    
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            // Output HTML with proper escaping
            echo '<div class="search-result-item">';
            echo '<a href="' . esc_url(get_permalink()) . '">';
            echo '<h4>' . esc_html(get_the_title()) . '</h4>';
            echo '</a>';
            echo '</div>';
        }
    } else {
        echo '<div class="no-results">' . esc_html__('Không có kết quả', 'laca') . '</div>';
    }
    
    wp_reset_postdata();
    
    // Get buffered output
    $output = ob_get_clean();
    
    // Cache results for 60 seconds
    set_transient($cache_key, $output, 60);
    
    echo $output;
    wp_die();
}

add_action('wp_ajax_nopriv_ajax_search', 'ajax_search');
add_action('wp_ajax_ajax_search', 'ajax_search');
/**
 * Localize AJAX search data (script bundled in theme.js)
 */
function custom_ajax_search_script()
{
    // Script is bundled in theme.js, just localize the data
    wp_localize_script('theme-js-bundle', 'themeSearch', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('theme_search_nonce'),
    ]);
}
add_action('wp_enqueue_scripts', 'custom_ajax_search_script');

// =============================================================================
// CUSTOM POST TYPES
// =============================================================================

new \App\PostTypes\service();
