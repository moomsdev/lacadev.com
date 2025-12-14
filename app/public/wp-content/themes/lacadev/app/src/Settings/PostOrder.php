<?php
/**
 * Custom Post Order
 * 
 * Drag & Drop ordering for posts, pages, custom post types, and taxonomies
 * 
 * @package LacaDev
 * @since 1.0.0
 */

namespace App\Settings;

class PostOrder {
    
    /**
     * Constructor
     */
    public function __construct() {
        // Database setup
        add_action('init', [$this, 'checkDatabase']);
        
        // Admin menu - Priority 100 to appear after Tools and Login Socials
        add_action('admin_menu', [$this, 'addAdminMenu'], 100);
        
        // Load scripts
        add_action('admin_enqueue_scripts', [$this, 'enqueueScripts']);
        
        // AJAX handlers
        add_action('wp_ajax_update_post_order', [$this, 'updatePostOrder']);
        add_action('wp_ajax_update_term_order', [$this, 'updateTermOrder']);
        
        // Frontend filters
        add_action('pre_get_posts', [$this, 'modifyQuery']);
        add_filter('get_terms_orderby', [$this, 'modifyTermsOrderBy'], 10, 3);
    }
    
    /**
     * Check and create term_order column if not exists
     */
    public function checkDatabase() {
        global $wpdb;
        
        if (!get_option('lacadev_post_order_installed')) {
            $result = $wpdb->query("DESCRIBE {$wpdb->terms} `term_order`");
            
            if (!$result) {
                $wpdb->query(
                    "ALTER TABLE {$wpdb->terms} 
                    ADD `term_order` INT(4) NULL DEFAULT '0'"
                );
            }
            
            update_option('lacadev_post_order_installed', 1);
        }
    }
    
    /**
     * Add submenu under Laca Admin
     */
    public function addAdminMenu() {
        add_submenu_page(
            'laca-admin',
            __('Post Order', 'laca'),
            __('Post Order', 'laca'),
            'manage_options',
            'laca-post-order',
            [$this, 'renderSettingsPage']
        );
    }
    
    /**
     * Enqueue scripts only on relevant pages
     */
    public function enqueueScripts($hook) {
        // Settings page
        if ($hook === 'laca-admin_page_laca-post-order') {
            wp_enqueue_style(
                'laca-admin-css',
                get_template_directory_uri() . '/dist/admin.css',
                [],
                wp_get_theme()->get('Version')
            );
        }
        
        // Post/Term list pages
        if ($this->shouldLoadSortable()) {
            // post-order.js is bundled into admin.js
            // Check if admin.js is already enqueued
            if (!wp_script_is('laca-admin-js', 'enqueued')) {
                wp_enqueue_script(
                    'laca-admin-js',
                    get_template_directory_uri() . '/dist/admin.js',
                    [],
                    wp_get_theme()->get('Version'),
                    true
                );
            }
            
            // Always localize script even if already enqueued
            wp_localize_script('laca-admin-js', 'lacaPostOrder', [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('laca_post_order_nonce'),
            ]);
        }
    }
    
    /**
     * Check if we should load sortable on current page
     */
    private function shouldLoadSortable() {
        $enabled_objects = $this->getEnabledObjects();
        $enabled_terms = $this->getEnabledTerms();
        
        if (empty($enabled_objects) && empty($enabled_terms)) {
            return false;
        }
        
        // Don't load if custom orderby is set
        if (isset($_GET['orderby'])) {
            return false;
        }
        
        // Check if we're on a post list page
        if (isset($_GET['post_type']) && in_array($_GET['post_type'], $enabled_objects)) {
            return true;
        }
        
        // Check if we're on default post list
        if (!isset($_GET['post_type']) && strstr($_SERVER['REQUEST_URI'], 'wp-admin/edit.php')) {
            return in_array('post', $enabled_objects);
        }
        
        // Check if we're on a taxonomy page
        if (isset($_GET['taxonomy']) && in_array($_GET['taxonomy'], $enabled_terms)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * AJAX: Update post order
     */
    public function updatePostOrder() {
        check_ajax_referer('laca_post_order_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_die('Unauthorized', 403);
        }
        
        global $wpdb;
        parse_str($_POST['order'], $data);
        
        if (!is_array($data) || empty($data['post'])) {
            wp_die('Invalid data', 400);
        }
        
        $ids = array_map('intval', $data['post']);
        
        // Get current menu_order values
        $menu_orders = [];
        foreach ($ids as $id) {
            $result = $wpdb->get_var($wpdb->prepare(
                "SELECT menu_order FROM {$wpdb->posts} WHERE ID = %d",
                $id
            ));
            $menu_orders[] = (int) $result;
        }
        
        sort($menu_orders);
        
        // Update with new order
        foreach ($ids as $position => $id) {
            $wpdb->update(
                $wpdb->posts,
                ['menu_order' => $menu_orders[$position]],
                ['ID' => $id],
                ['%d'],
                ['%d']
            );
        }
        
        wp_cache_flush();
        wp_die('success');
    }
    
    /**
     * AJAX: Update term order
     */
    public function updateTermOrder() {
        check_ajax_referer('laca_post_order_nonce', 'nonce');
        
        if (!current_user_can('manage_categories')) {
            wp_die('Unauthorized', 403);
        }
        
        global $wpdb;
        parse_str($_POST['order'], $data);
        
        if (!is_array($data) || empty($data['post'])) {
            wp_die('Invalid data', 400);
        }
        
        $ids = array_map('intval', $data['post']);
        
        // Get current term_order values
        $term_orders = [];
        foreach ($ids as $id) {
            $result = $wpdb->get_var($wpdb->prepare(
                "SELECT term_order FROM {$wpdb->terms} WHERE term_id = %d",
                $id
            ));
            $term_orders[] = (int) $result;
        }
        
        sort($term_orders);
        
        // Update with new order
        foreach ($ids as $position => $id) {
            $wpdb->update(
                $wpdb->terms,
                ['term_order' => $term_orders[$position]],
                ['term_id' => $id],
                ['%d'],
                ['%d']
            );
        }
        
        wp_cache_flush();
        wp_die('success');
    }
    
    /**
     * Modify WP_Query to order by menu_order
     */
    public function modifyQuery($wp_query) {
        $enabled_objects = $this->getEnabledObjects();
        
        if (empty($enabled_objects) || is_search()) {
            return;
        }
        
        // Admin
        if (is_admin() && !wp_doing_ajax()) {
            if (isset($wp_query->query['post_type']) && !isset($_GET['orderby'])) {
                if (in_array($wp_query->query['post_type'], $enabled_objects)) {
                    if (!$wp_query->get('orderby')) {
                        $wp_query->set('orderby', 'menu_order');
                    }
                    if (!$wp_query->get('order')) {
                        $wp_query->set('order', 'ASC');
                    }
                }
            }
        }
        // Frontend
        else {
            $active = false;
            
            if (isset($wp_query->query['post_type'])) {
                if (in_array($wp_query->query['post_type'], $enabled_objects)) {
                    $active = true;
                }
            } elseif (in_array('post', $enabled_objects)) {
                $active = true;
            }
            
            if ($active) {
                if (!$wp_query->get('orderby')) {
                    $wp_query->set('orderby', 'menu_order');
                }
                if (!$wp_query->get('order')) {
                    $wp_query->set('order', 'ASC');
                }
            }
        }
    }
    
    /**
     * Modify terms orderby
     */
    public function modifyTermsOrderBy($orderby, $args, $taxonomies) {
        if (is_admin() && !wp_doing_ajax()) {
            return $orderby;
        }
        
        $enabled_terms = $this->getEnabledTerms();
        
        if (!isset($args['taxonomy'])) {
            return $orderby;
        }
        
        $taxonomy = is_array($args['taxonomy']) ? $args['taxonomy'][0] : $args['taxonomy'];
        
        if (in_array($taxonomy, $enabled_terms)) {
            $orderby = 't.term_order';
        }
        
        return $orderby;
    }
    
    /**
     * Get enabled post types from options
     */
    private function getEnabledObjects() {
        $options = get_option('laca_post_order_options', []);
        return isset($options['objects']) && is_array($options['objects']) 
            ? $options['objects'] 
            : [];
    }
    
    /**
     * Get enabled taxonomies from options
     */
    private function getEnabledTerms() {
        $options = get_option('laca_post_order_options', []);
        return isset($options['terms']) && is_array($options['terms']) 
            ? $options['terms'] 
            : [];
    }
    
    /**
     * Render settings page
     */
    public function renderSettingsPage() {
        // Save settings
        if (isset($_POST['laca_post_order_submit'])) {
            check_admin_referer('laca_post_order_settings');
            
            $options = [
                'objects' => isset($_POST['objects']) ? array_map('sanitize_text_field', $_POST['objects']) : [],
                'terms' => isset($_POST['terms']) ? array_map('sanitize_text_field', $_POST['terms']) : [],
            ];
            
            update_option('laca_post_order_options', $options);
            
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Settings saved!', 'laca') . '</p></div>';
        }
        
        $current_options = get_option('laca_post_order_options', []);
        $enabled_objects = isset($current_options['objects']) ? $current_options['objects'] : [];
        $enabled_terms = isset($current_options['terms']) ? $current_options['terms'] : [];
        
        // Get all public post types
        $post_types = get_post_types(['public' => true], 'objects');
        
        // Get all public taxonomies
        $taxonomies = get_taxonomies(['public' => true], 'objects');
        ?>
        
        <div class="wrap">
            <h1><?php esc_html_e('Custom Post Order Settings', 'laca'); ?></h1>
            <p><?php esc_html_e('Enable drag & drop ordering for posts, pages, custom post types, and taxonomies.', 'laca'); ?></p>
            
            <form method="post" action="">
                <?php wp_nonce_field('laca_post_order_settings'); ?>
                
                <h2><?php esc_html_e('Post Types', 'laca'); ?></h2>
                <p class="description"><?php esc_html_e('Select which post types should have drag & drop ordering:', 'laca'); ?></p>
                
                <table class="form-table">
                    <tbody>
                        <?php foreach ($post_types as $post_type): ?>
                            <tr>
                                <th scope="row">
                                    <label for="object_<?php echo esc_attr($post_type->name); ?>">
                                        <?php echo esc_html($post_type->label); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="checkbox" 
                                           name="objects[]" 
                                           id="object_<?php echo esc_attr($post_type->name); ?>"
                                           value="<?php echo esc_attr($post_type->name); ?>"
                                           <?php checked(in_array($post_type->name, $enabled_objects)); ?>>
                                    <span class="description"><?php echo esc_html($post_type->description ?: $post_type->name); ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <h2><?php esc_html_e('Taxonomies', 'laca'); ?></h2>
                <p class="description"><?php esc_html_e('Select which taxonomies should have drag & drop ordering:', 'laca'); ?></p>
                
                <table class="form-table">
                    <tbody>
                        <?php foreach ($taxonomies as $taxonomy): ?>
                            <tr>
                                <th scope="row">
                                    <label for="term_<?php echo esc_attr($taxonomy->name); ?>">
                                        <?php echo esc_html($taxonomy->label); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="checkbox" 
                                           name="terms[]" 
                                           id="term_<?php echo esc_attr($taxonomy->name); ?>"
                                           value="<?php echo esc_attr($taxonomy->name); ?>"
                                           <?php checked(in_array($taxonomy->name, $enabled_terms)); ?>>
                                    <span class="description"><?php echo esc_html($taxonomy->description ?: $taxonomy->name); ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <?php submit_button(__('Save Settings', 'laca'), 'primary', 'laca_post_order_submit'); ?>
            </form>
        </div>
        
        <?php
    }
}
