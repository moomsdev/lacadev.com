<?php

namespace App\Features\ProjectManagement\Ajax;

use App\Models\ProjectLog;
use App\Models\ProjectAlert;

/**
 * AJAX Handler: Logs & Alerts
 *
 * Đăng ký tại project.php __construct():
 *   new LogAjaxHandler();
 */
class LogAjaxHandler
{
    public function __construct()
    {
        add_action('wp_ajax_laca_resolve_alert', [$this, 'resolveAlert']);
        add_action('wp_ajax_laca_delete_log',    [$this, 'deleteLog']);
        add_action('wp_ajax_laca_add_log',       [$this, 'addLog']);
        add_action('wp_ajax_laca_add_alert',     [$this, 'addAlert']);
    }

    // -------------------------------------------------------------------------

    public function resolveAlert(): void
    {
        check_ajax_referer('laca_project_manager', 'nonce');

        $alertId   = absint($_POST['alert_id'] ?? 0);
        $projectId = absint($_POST['project_id'] ?? 0);

        if (!$projectId || get_post_type($projectId) !== 'project') {
            wp_send_json_error(['message' => 'Project không hợp lệ'], 400);
        }

        if (!current_user_can('edit_post', $projectId)) {
            wp_send_json_error(['message' => 'Không có quyền'], 403);
        }

        if (!$alertId) {
            wp_send_json_error(['message' => 'Thiếu alert_id']);
        }

        $result = ProjectAlert::resolve($alertId, $projectId);
        if ($result) {
            wp_send_json_success(['message' => 'Đã đánh dấu xử lý']);
        } else {
            wp_send_json_error(['message' => 'Không tìm thấy cảnh báo']);
        }
    }

    // -------------------------------------------------------------------------

    public function deleteLog(): void
    {
        check_ajax_referer('laca_project_manager', 'nonce');

        $logId     = absint($_POST['log_id'] ?? 0);
        $projectId = absint($_POST['project_id'] ?? 0);

        if (!$projectId || get_post_type($projectId) !== 'project') {
            wp_send_json_error(['message' => 'Project không hợp lệ'], 400);
        }

        if (!current_user_can('edit_post', $projectId)) {
            wp_send_json_error(['message' => 'Không có quyền'], 403);
        }

        if (!$logId) {
            wp_send_json_error(['message' => 'Thiếu log_id'], 400);
        }

        $result = ProjectLog::delete($logId, $projectId);
        if ($result) {
            wp_send_json_success(['message' => 'Đã xoá']);
        } else {
            wp_send_json_error(['message' => 'Không tìm thấy']);
        }
    }

    // -------------------------------------------------------------------------

    public function addLog(): void
    {
        check_ajax_referer('laca_project_manager', 'nonce');

        $projectId = absint($_POST['project_id'] ?? 0);
        $content   = sanitize_textarea_field($_POST['log_content'] ?? '');
        $type      = sanitize_key($_POST['log_type'] ?? 'note');

        if (!$projectId || get_post_type($projectId) !== 'project') {
            wp_send_json_error(['message' => 'Project không hợp lệ'], 400);
        }

        if (!current_user_can('edit_post', $projectId)) {
            wp_send_json_error(['message' => 'Không có quyền'], 403);
        }

        if (!$content) {
            wp_send_json_error(['message' => 'Vui lòng nhập nội dung']);
        }

        $logId = ProjectLog::add([
            'project_id'  => $projectId,
            'log_content' => $content,
            'log_type'    => $type,
        ]);

        if ($logId) {
            wp_send_json_success(['message' => 'Đã thêm log thành công']);
        } else {
            wp_send_json_error(['message' => 'Không thể lưu log']);
        }
    }

    // -------------------------------------------------------------------------

    public function addAlert(): void
    {
        check_ajax_referer('laca_project_manager', 'nonce');

        $projectId = absint($_POST['project_id'] ?? 0);
        $msg       = sanitize_textarea_field($_POST['alert_msg'] ?? '');
        $type      = sanitize_key($_POST['alert_type'] ?? 'other');
        $level     = sanitize_key($_POST['alert_level'] ?? 'info');

        if (!$projectId || get_post_type($projectId) !== 'project') {
            wp_send_json_error(['message' => 'Project không hợp lệ'], 400);
        }

        if (!current_user_can('edit_post', $projectId)) {
            wp_send_json_error(['message' => 'Không có quyền'], 403);
        }

        if (!$msg) {
            wp_send_json_error(['message' => 'Vui lòng nhập nội dung cảnh báo']);
        }

        $alertId = ProjectAlert::add([
            'project_id'  => $projectId,
            'alert_msg'   => $msg,
            'alert_type'  => $type,
            'alert_level' => $level,
        ]);

        if ($alertId) {
            wp_send_json_success(['message' => 'Đã gửi cảnh báo']);
        } else {
            wp_send_json_error(['message' => 'Không thể lưu cảnh báo']);
        }
    }
}
