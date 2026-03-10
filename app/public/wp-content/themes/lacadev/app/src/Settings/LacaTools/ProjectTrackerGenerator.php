<?php

namespace App\Settings\LacaTools;

class ProjectTrackerGenerator
{
    public function init(): void
    {
        add_action('wp_ajax_laca_get_tracker_code', [$this, 'generateCodeAjax']);
    }

    /**
     * Trả về đoạn code PHP để client cài dưới dạng mu-plugin
     */
    public function generateCodeAjax(): void
    {
        check_ajax_referer('laca_project_manager', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Không có quyền']);
        }

        $projectId = absint($_POST['project_id'] ?? 0);
        if (!$projectId) {
            wp_send_json_error(['message' => 'Lỗi ID dự án']);
        }

        $secretKey = get_post_meta($projectId, '_tracker_secret_key', true);
        if (empty($secretKey)) {
            wp_send_json_error(['message' => 'Dự án này chưa có Secret Key. Vui lòng F5 trang lấy lại mã.']);
        }

        $endpoint = rest_url('laca/v1/tracker/log');
        
        $code = $this->getMuPluginTemplate($endpoint, $secretKey);

        wp_send_json_success(['code' => $code]);
    }

    private function getMuPluginTemplate(string $endpoint, string $secretKey): string
    {
        return <<<PHP
<?php
/**
 * Plugin Name: LacaDev Project Tracker
 * Description: Tự động ghi nhận log cập nhật plugin/theme/core và file integrity về LacaDev CMS.
 * Version: 1.0.0
 * Author: MOOMS.DEV
 */

if (!defined('ABSPATH')) {
    exit;
}

class LacaDevTrackerClient {
    private \$endpoint = '{$endpoint}';
    private \$secret   = '{$secretKey}';

    public function __construct() {
        add_action('upgrader_process_complete', [\$this, 'handleUpdates'], 10, 2);
    }

    public function handleUpdates(\$upgrader_object, \$options) {
        if (!isset(\$options['action']) || !isset(\$options['type'])) {
            return;
        }

        \$action = \$options['action']; // update, install
        \$type   = \$options['type'];   // plugin, theme, core
        
        if (\$action !== 'update') {
            return;
        }

        \$logs = [];
        
        if (\$type === 'plugin' && isset(\$options['plugins'])) {
            foreach (\$options['plugins'] as \$plugin) {
                \$logs[] = [
                    'type'    => 'plugin_update',
                    'content' => "Cập nhật plugin: {\$plugin}"
                ];
            }
        } elseif (\$type === 'theme' && isset(\$options['themes'])) {
            foreach (\$options['themes'] as \$theme) {
                \$logs[] = [
                    'type'    => 'theme_update',
                    'content' => "Cập nhật theme: {\$theme}"
                ];
            }
        } elseif (\$type === 'core') {
            \$logs[] = [
                'type'    => 'core_update',
                'content' => "Cập nhật WordPress Core."
            ];
        }

        if (!empty(\$logs)) {
            \$this->sendLogs(\$logs);
        }
    }

    private function sendLogs(array \$logs) {
        wp_remote_post(\$this->endpoint, [
            'body' => [
                'secret_key' => \$this->secret,
                'logs'       => \$logs,
            ],
            'timeout' => 5,
            'blocking' => false // Bắn log async tránh làm chậm web client
        ]);
    }
}

new LacaDevTrackerClient();
PHP;
    }
}
