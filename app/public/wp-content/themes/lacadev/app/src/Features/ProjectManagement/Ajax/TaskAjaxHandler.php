<?php

namespace App\Features\ProjectManagement\Ajax;

use App\Models\ProjectLog;

/**
 * AJAX Handler: Task Checklist
 *
 * Đăng ký tại project.php __construct():
 *   new TaskAjaxHandler();
 */
class TaskAjaxHandler
{
    public function __construct()
    {
        add_action('wp_ajax_laca_add_task',    [$this, 'addTask']);
        add_action('wp_ajax_laca_toggle_task', [$this, 'toggleTask']);
        add_action('wp_ajax_laca_delete_task', [$this, 'deleteTask']);
        add_action('wp_ajax_laca_sync_pages',  [$this, 'syncPages']);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Public static: dùng được từ view template (meta-box-col2.php)
     * và từ các AJAX methods nội bộ.
     */
    public static function getTaskList(int $projectId): array
    {
        $raw = get_post_meta($projectId, '_laca_task_list', true);
        if (is_string($raw) && $raw !== '') {
            return json_decode($raw, true) ?: [];
        }
        return [];
    }

    private function saveTaskList(int $projectId, array $tasks): void
    {
        update_post_meta(
            $projectId,
            '_laca_task_list',
            wp_json_encode($tasks, JSON_UNESCAPED_UNICODE)
        );
    }

    // -------------------------------------------------------------------------
    // AJAX Actions
    // -------------------------------------------------------------------------

    /** AJAX: Sync các trang từ design_pages CF vào task list (chỉ thêm, không xóa) */
    public function syncPages(): void
    {
        check_ajax_referer('laca_project_manager', 'nonce');
        $projectId = absint($_POST['project_id'] ?? 0);

        if (!$projectId || get_post_type($projectId) !== 'project' || !current_user_can('edit_post', $projectId)) {
            wp_send_json_error(['message' => 'Không hợp lệ'], 403);
        }

        $pages = (array) carbon_get_post_meta($projectId, 'design_pages');
        $tasks = self::getTaskList($projectId);

        // Index existing page tasks by source_id để tránh trùng lặp
        $existingPageIds = [];
        foreach ($tasks as $t) {
            if (($t['source'] ?? '') === 'page') {
                $existingPageIds[] = $t['source_id'] ?? '';
            }
        }

        $added = 0;
        foreach ($pages as $idx => $page) {
            $pageName = trim($page['page_name'] ?? '');
            if (!$pageName) {
                continue;
            }
            $sourceId = 'page_' . $idx;
            if (in_array($sourceId, $existingPageIds, true)) {
                continue; // Already synced
            }
            $tasks[] = [
                'id'        => uniqid('t_', true),
                'name'      => $pageName,
                'demo_url'  => esc_url_raw($page['page_demo_url'] ?? ''),
                'source'    => 'page',
                'source_id' => $sourceId,
                'done'      => false,
                'added_at'  => date('Y-m-d'),
            ];
            $added++;
        }

        $this->saveTaskList($projectId, $tasks);
        wp_send_json_success(['message' => "Đã sync $added trang mới", 'tasks' => $tasks]);
    }

    // -------------------------------------------------------------------------

    /** AJAX: Thêm task thủ công từ logs section */
    public function addTask(): void
    {
        check_ajax_referer('laca_project_manager', 'nonce');
        $projectId   = absint($_POST['project_id'] ?? 0);
        $name        = sanitize_text_field($_POST['task_name'] ?? '');
        $category    = sanitize_key($_POST['task_category'] ?? 'other');
        $description = sanitize_textarea_field($_POST['task_description'] ?? '');
        $imageId     = absint($_POST['task_image_id'] ?? 0);

        $validCategories = ['page', 'bug', 'content', 'seo', 'feature', 'other'];
        if (!in_array($category, $validCategories, true)) {
            $category = 'other';
        }

        if (!$projectId || get_post_type($projectId) !== 'project' || !current_user_can('edit_post', $projectId)) {
            wp_send_json_error(['message' => 'Không hợp lệ'], 403);
        }
        if (!$name) {
            wp_send_json_error(['message' => 'Vui lòng nhập tên task']);
        }

        $tasks    = $this->getTaskList($projectId);
        $imageUrl = $imageId ? wp_get_attachment_image_url($imageId, 'medium') : '';

        $newTask = [
            'id'          => uniqid('t_', true),
            'name'        => $name,
            'description' => $description,
            'image_id'    => $imageId ?: 0,
            'image_url'   => $imageUrl ?: '',
            'category'    => $category,
            'demo_url'    => '',
            'source'      => 'manual',
            'done'        => false,
            'added_at'    => date('Y-m-d H:i:s'),
        ];
        $tasks[] = $newTask;
        $this->saveTaskList($projectId, $tasks);

        wp_send_json_success(['message' => 'Đã thêm task', 'task' => $newTask]);
    }

    // -------------------------------------------------------------------------

    /** AJAX: Toggle done/undone một task */
    public function toggleTask(): void
    {
        check_ajax_referer('laca_project_manager', 'nonce');
        $projectId = absint($_POST['project_id'] ?? 0);
        $taskId    = sanitize_text_field($_POST['task_id'] ?? '');

        if (!$projectId || get_post_type($projectId) !== 'project' || !current_user_can('edit_post', $projectId)) {
            wp_send_json_error(['message' => 'Không hợp lệ'], 403);
        }

        $tasks    = $this->getTaskList($projectId);
        $found    = false;
        $taskName = '';
        $justDone = false;
        foreach ($tasks as &$task) {
            if ($task['id'] === $taskId) {
                $taskName        = $task['name'] ?? '';
                $task['done']    = !($task['done'] ?? false);
                $task['done_at'] = $task['done'] ? date('Y-m-d H:i:s') : null;
                $justDone        = (bool) $task['done'];
                $found = true;
                break;
            }
        }
        unset($task);

        if (!$found) {
            wp_send_json_error(['message' => 'Task không tìm thấy']);
        }

        $this->saveTaskList($projectId, $tasks);

        // Auto-log khi hoàn thành task
        if ($justDone && $taskName && class_exists('\App\Models\ProjectLog')) {
            $doneTime = date('H:i d/m/Y');
            ProjectLog::add([
                'project_id'  => $projectId,
                'log_type'    => 'task_done',
                'log_content' => "✅ Hoàn thành task: {$taskName} — {$doneTime}",
                'log_by'      => wp_get_current_user()->display_name ?: 'Admin',
                'is_auto'     => 1,
            ]);
        }

        // Tính lại progress
        $total    = count($tasks);
        $done     = count(array_filter($tasks, fn($t) => $t['done'] ?? false));
        $progress = $total > 0 ? (int) round($done / $total * 100) : 0;

        // Gửi kèm log mới nhất để JS render
        $latestLogs = class_exists('\App\Models\ProjectLog')
            ? ProjectLog::getByProject($projectId, 20)
            : [];

        wp_send_json_success([
            'message'   => 'OK',
            'tasks'     => $tasks,
            'progress'  => $progress,
            'logs'      => $latestLogs,
            'just_done' => $justDone,
        ]);
    }

    // -------------------------------------------------------------------------

    /** AJAX: Xoá task */
    public function deleteTask(): void
    {
        check_ajax_referer('laca_project_manager', 'nonce');
        $projectId = absint($_POST['project_id'] ?? 0);
        $taskId    = sanitize_text_field($_POST['task_id'] ?? '');

        if (!$projectId || get_post_type($projectId) !== 'project' || !current_user_can('edit_post', $projectId)) {
            wp_send_json_error(['message' => 'Không hợp lệ'], 403);
        }

        $tasks  = $this->getTaskList($projectId);
        $before = count($tasks);
        $tasks  = array_values(array_filter($tasks, fn($t) => $t['id'] !== $taskId));

        if (count($tasks) === $before) {
            wp_send_json_error(['message' => 'Không tìm thấy task']);
        }

        $this->saveTaskList($projectId, $tasks);
        wp_send_json_success(['message' => 'Đã xoá task', 'tasks' => $tasks]);
    }
}
