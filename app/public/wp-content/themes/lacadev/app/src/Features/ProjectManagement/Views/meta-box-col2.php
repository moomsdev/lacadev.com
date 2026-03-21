<?php
/**
 * View: Project Meta Box — Cột 2 (Tasks + Logs + Remote Update)
 *
 * Variables available (từ renderLogsMetaBox()):
 *
 * @var int   $projectId
 * @var array $logs
 */

use App\Models\ProjectLog;

$tasks     = \App\Features\ProjectManagement\Ajax\TaskAjaxHandler::getTaskList($projectId);
$totalTask = count($tasks);
$doneTask  = count(array_filter($tasks, fn($t) => $t['done'] ?? false));
$progress  = $totalTask > 0 ? (int) round($doneTask / $totalTask * 100) : 0;
?>
<!-- TRACKER & PORTAL SECTION -->
<div class="laca-pm-col" style="display:flex; flex-direction:column; gap:20px;">
    <div>
        <h3 style="margin-top:0">Auto Activity Tracker</h3>
        <p style="margin:0 0 10px 0; color:#666; font-size:12px;">Cài đặt plugin vào <code>wp-content/mu-plugins/</code> ở web khách để auto tracking.</p>
        <div class="laca-form-group">
            <label style="font-weight:600;font-size:12px;">Endpoint URL</label>
            <input type="text" readonly value="<?php echo esc_url($endpoint); ?>" class="laca-copyable-input">
        </div>
        <div class="laca-form-group">
            <label style="font-weight:600;font-size:12px;">Secret Key</label>
            <input type="text" readonly value="<?php echo esc_attr($secretKey); ?>" class="laca-copyable-input">
        </div>
        <div style="margin-top:10px;">
            <button type="button" class="button button-primary" id="btn_download_tracker">⬇️ Tải xuống MU-Plugin</button>
            <button type="button" class="button" id="btn_view_tracker_code">👁 Xem code PHP</button>
        </div>
    </div>

    <?php
    $_portalPages2 = get_posts([
        'post_type'      => 'page',
        'posts_per_page' => 1,
        'meta_key'       => '_wp_page_template',
        'meta_value'     => 'page_templates/template-client-portal.php',
        'post_status'    => 'publish',
    ]);
    $_portalUrl2 = !empty($_portalPages2) ? get_permalink($_portalPages2[0]->ID) : '';

    if ($_portalUrl2):
        $_cPortalUrl = add_query_arg('key', $secretKey, $_portalUrl2);
        $_cAliasUrl  = $portalAlias ? add_query_arg('key', $portalAlias, $_portalUrl2) : '';
    ?>
    <hr style="border:0; border-top:1px dashed #ddd; margin:18px 0 14px;">
    <div>
        <h3 style="margin-top:0">Client Portal</h3>
        <p style="margin:0 0 10px 0; color:#666; font-size:12px;">Link theo dõi tiến độ dành riêng cho khách. Copy và gửi cho khách.</p>
        <div class="laca-form-group">
            <label style="font-weight:600;font-size:12px;">🔑 Link Portal</label>
            <input type="text" readonly value="<?php echo esc_url($_cPortalUrl); ?>" class="laca-copyable-input" id="client_portal_url2">
        </div>
        <div style="margin-top:8px; display:flex; gap:8px;">
            <a href="<?php echo esc_url($_cPortalUrl); ?>" target="_blank" class="button">👁 Xem Portal</a>
        </div>
    </div>
    <?php else: ?>
    <div style="padding:12px; background:#fff8e1; border-radius:4px;">
        <p style="margin:0; color:#795548; font-size:12px;">⚠️ <strong>Client Portal chưa cấu hình.</strong></p>
    </div>
    <?php endif; ?>
</div>

<!-- TASK CHECKLIST SECTION -->
<div class="laca-pm-col">

    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
        <h3 style="margin:0">✅ Task & Tiến độ</h3>
        <button type="button" class="button" id="btn_sync_pages" title="Import trang từ tab Design Scope">
            🔄 Sync trang
        </button>
    </div>

    <!-- Progress bar -->
    <div style="margin-bottom:14px;">
        <div style="display:flex;justify-content:space-between;font-size:12px;color:#555;margin-bottom:4px;">
            <span id="task_progress_label"><?php echo $doneTask; ?>/<?php echo $totalTask; ?> task hoàn thành</span>
            <strong id="task_progress_pct"><?php echo $progress; ?>%</strong>
        </div>
        <div style="background:#e0e0e0;border-radius:4px;height:8px;overflow:hidden;">
            <div id="task_progress_bar" style="height:100%;background:#2271b1;border-radius:4px;width:<?php echo $progress; ?>%;transition:width .3s;"></div>
        </div>
    </div>

    <!-- Task list -->
    <div class="laca-pm-list" id="task_list_container">
        <?php if (empty($tasks)): ?>
            <p style="color:#888;font-size:13px;">Chưa có task nào. Nhấn "🔄 Sync trang" để import từ danh sách trang, hoặc thêm task thủ công bên dưới.</p>
        <?php else: ?>
            <?php foreach ($tasks as $t): ?>
            <div class="laca-task-item <?php echo $t['done'] ? 'task-done' : ''; ?>" data-id="<?php echo esc_attr($t['id']); ?>">
                <input type="checkbox" class="task-checkbox" data-id="<?php echo esc_attr($t['id']); ?>" <?php checked($t['done']); ?>>
                <div style="flex:1;min-width:0;">
                    <span class="task-name">
                        <?php
                        $catIcons = ['bug'=>'🐛','page'=>'🖼️','content'=>'📝','seo'=>'🔍','feature'=>'⭐','other'=>'📌'];
                        $cat = $t['category'] ?? (($t['source'] ?? '') === 'page' ? 'page' : 'other');
                        echo $catIcons[$cat] ?? '📌';
                        ?>
                        <?php echo esc_html($t['name']); ?>
                    </span>
                    <?php if (!empty($t['demo_url'])): ?>
                        <a href="<?php echo esc_url($t['demo_url']); ?>" target="_blank" style="font-size:11px;color:#0073aa;margin-left:6px;" title="Mẫu giao diện">↗ mẫu</a>
                    <?php endif; ?>
                </div>
                <a class="task-delete-btn" data-id="<?php echo esc_attr($t['id']); ?>" title="Xoá task">✕</a>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Add task form -->
    <div style="margin-top:12px;">
        <div style="display:flex;gap:6px;margin-bottom:6px;">
            <select id="new_task_category" style="padding:6px 8px;border:1px solid #ddd;border-radius:4px;font-size:13px;min-width:140px;">
                <option value="bug">🐛 Bug / Lỗi</option>
                <option value="page">🖼️ Trang giao diện</option>
                <option value="content">📝 Nội dung</option>
                <option value="seo">🔍 SEO</option>
                <option value="feature">⭐ Tính năng</option>
                <option value="other" selected>📌 Khác</option>
            </select>
            <input type="text" id="new_task_name" placeholder="Tên task (vd: Fix menu mobile...)" style="flex:1;padding:6px 10px;border:1px solid #ddd;border-radius:4px;font-size:13px;">
            <button type="button" class="button" id="btn_add_task">+ Thêm</button>
        </div>
    </div>

    <!-- REMOTE UPDATE SECTION -->
    <hr style="border:0; border-top:1px dashed #ddd; margin:18px 0 14px;">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
        <h3 style="margin:0">🚀 Cập nhật từ xa</h3>
        <button type="button" class="button button-secondary" id="btn_load_pending" style="font-size:12px; padding:4px 10px;">
            🔄 Tải danh sách plugin chờ update
        </button>
    </div>

    <!-- Danh sách plugin pending -->
    <div id="pending_plugins_list" style="margin-bottom:14px; display:none;">
        <table style="width:100%; border-collapse:collapse; font-size:13px;">
            <thead>
                <tr style="background:#f9f9f9; border-bottom:2px solid #e1e1e1;">
                    <th style="padding:7px 10px; text-align:left; font-weight:600;">Plugin</th>
                    <th style="padding:7px 10px; text-align:center; width:80px;">Hiện tại</th>
                    <th style="padding:7px 10px; text-align:center; width:80px;">Bản mới</th>
                    <th style="padding:7px 10px; text-align:center; width:110px;">Hành động</th>
                </tr>
            </thead>
            <tbody id="pending_plugins_tbody">
                <tr><td colspan="4" style="padding:10px; text-align:center; color:#999;">Chưa có dữ liệu</td></tr>
            </tbody>
        </table>
    </div>
    <div id="pending_plugins_empty" style="display:none; color:#888; font-size:13px; margin-bottom:10px; padding:8px 12px; background:#f9f9f9; border-radius:4px;">
        ✅ Không có plugin nào cần cập nhật.
    </div>

    <!-- Form nhanh: update_core hoặc nhập tay -->
    <details style="margin-top:6px;">
        <summary style="cursor:pointer; font-size:12px; color:#555; user-select:none;">⚙️ Gửi lệnh thủ công (update_core, theme, hoặc nhập slug)</summary>
        <div style="margin-top:10px;">
            <p style="color:#666; font-size:12px; margin:0 0 10px 0;">Gửi lệnh cập nhật plugin, theme hoặc WordPress core đến site client.</p>
            <div style="display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
                <select id="remote_update_action" style="padding:6px 10px; border:1px solid #ddd; border-radius:4px; font-size:13px;">
                    <option value="update_plugin">🔌 Cập nhật Plugin</option>
                    <option value="update_theme">🎨 Cập nhật Theme</option>
                    <option value="update_core">⚡ Cập nhật WordPress Core</option>
                </select>
                <input type="text" id="remote_update_slug"
                    placeholder="slug (vd: woocommerce/woocommerce.php)"
                    style="flex:1; min-width:180px; padding:6px 10px; border:1px solid #ddd; border-radius:4px; font-size:13px;">
                <button type="button" class="button button-primary" id="btn_remote_update" style="white-space:nowrap;">
                    🚀 Gửi lệnh
                </button>
            </div>
            <p style="font-size:11px; color:#888; margin:6px 0 0 0;">⟶ Với <em>update_core</em>, bỏ trống slug. Plugin slug dạng <code>folder/file.php</code>, theme slug dạng <code>folder-name</code>.</p>
        </div>
    </details>
    <div id="remote_update_msg" style="margin-top:10px; display:none;"></div>

</div>
