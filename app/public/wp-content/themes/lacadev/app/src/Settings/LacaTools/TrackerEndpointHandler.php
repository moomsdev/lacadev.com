<?php

namespace App\Settings\LacaTools;

use App\Models\ProjectLog;
use App\Models\ProjectAlert;

class TrackerEndpointHandler
{
    public function init(): void
    {
        add_action('rest_api_init', [$this, 'registerRoutes']);
    }

    public function registerRoutes(): void
    {
        register_rest_route('laca/v1', '/tracker/log', [
            'methods'             => \WP_REST_Server::CREATABLE,
            'callback'            => [$this, 'handleIncomingLog'],
            'permission_callback' => '__return_true', // Authentication sẽ được xử lý riêng qua Secret Key
        ]);
    }

    public function handleIncomingLog(\WP_REST_Request $request)
    {
        $parameters = $request->get_json_params() ?: $request->get_body_params();

        $secretKey = $parameters['secret_key'] ?? '';
        $logs      = $parameters['logs'] ?? [];

        if (empty($secretKey) || empty($logs) || !is_array($logs)) {
            return new \WP_REST_Response(['success' => false, 'message' => 'Dữ liệu không hợp lệ.'], 400);
        }

        // Tìm Project có secret_key tương ứng
        global $wpdb;
        $projectId = $wpdb->get_var($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %s LIMIT 1",
            '_tracker_secret_key',
            $secretKey
        ));

        if (!$projectId) {
            return new \WP_REST_Response(['success' => false, 'message' => 'Secret key không đúng.'], 401);
        }

        $insertedCount = 0;
        foreach ($logs as $log) {
            $type    = sanitize_key($log['type'] ?? 'other');
            $content = sanitize_textarea_field($log['content'] ?? '');
            
            if (empty($content)) {
                continue;
            }

            // Map type từ client sang type của database Server
            $logType = 'bug_fix'; // Mặc định
            if ($type === 'plugin_update' || $type === 'theme_update' || $type === 'core_update') {
                $logType = 'deployment';
            } elseif ($type === 'file_changed' || $type === 'code_edit') {
                $logType = 'bug_fix'; 
                // Đồng thời tạo cảnh báo (Alert) vì file code bị sửa
                $this->createAlert($projectId, "Phát hiện file bị chỉnh sửa: \n" . $content);
            }

            $logId = ProjectLog::add([
                'project_id'  => $projectId,
                'log_content' => $content,
                'log_type'    => $logType,
                'is_auto'     => 1,
                'log_by'      => 'Hệ thống LacaDev Bot',
            ]);

            if ($logId) {
                $insertedCount++;
            }
        }

        return new \WP_REST_Response([
            'success' => true,
            'message' => "Đã ghi nhận {$insertedCount} sự kiện.",
        ], 200);
    }

    /**
     * Tạo cảnh báo lên hệ thống chính nếu phát hiện sửa code
     */
    private function createAlert(int $projectId, string $msg): void
    {
        if (!class_exists('\App\Models\ProjectAlert')) {
            return;
        }

        // Chỉ tạo alert nếu chưa có alert tương tự (hoặc có thể tạo liên tục tuỳ chiến lược)
        // Ở đây ta cứ tạo alert Warning
        ProjectAlert::add([
            'project_id'  => $projectId,
            'alert_type'  => 'security',
            'alert_level' => 'warning',
            'alert_msg'   => $msg,
        ]);
    }
}
