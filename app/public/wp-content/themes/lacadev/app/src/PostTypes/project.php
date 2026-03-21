<?php

namespace App\PostTypes;

use App\Models\ProjectLog;
use App\Models\ProjectAlert;
use App\PostTypes\Concerns\BlockSyncSender;
use App\PostTypes\Concerns\HasEncryption;
use App\PostTypes\Concerns\HasBrandColors;
use App\PostTypes\Concerns\HasCurrencyFormat;
use App\PostTypes\Concerns\HasPortalAlias;
use App\Features\ProjectManagement\Ajax\LogAjaxHandler;
use App\Features\ProjectManagement\Ajax\TaskAjaxHandler;
use App\Features\ProjectManagement\Ajax\RemoteAjaxHandler;
use App\Features\ProjectManagement\ProjectAdminColumns;
use App\Features\ProjectManagement\ProjectPaymentService;

class Project extends \App\Abstracts\AbstractPostType
{
    use BlockSyncSender;
    use HasEncryption;
    use HasBrandColors;
    use HasCurrencyFormat;
    use HasPortalAlias;

    public function __construct()
    {
        $this->showThumbnailOnList = true;
        $this->supports            = ['title', 'thumbnail'];
        $this->menuIcon            = 'dashicons-layout';
        $this->post_type           = 'project';
        $this->singularName        = $this->pluralName = __('Quản lý dự án', 'laca');
        $this->titlePlaceHolder    = __('Tên dự án / website', 'laca');
        $this->slug                = 'projects';
        parent::__construct();

        // Admin list columns
        (new ProjectAdminColumns())->register();

        // AJAX handlers
        new LogAjaxHandler();
        new TaskAjaxHandler();
        new RemoteAjaxHandler();

        // Meta box Logs & Alerts
        add_action('add_meta_boxes', [$this, 'registerLogsMetaBox']);

        // Carbon Fields: mã hóa mật khẩu
        add_filter('carbon_fields_post_meta_value_save', [$this, 'encryptPasswordsOnSave'], 10, 4);
        add_filter('carbon_fields_post_meta_value_load', [$this, 'decryptPasswordsOnLoad'], 10, 4);

        // Carbon Fields: format tiền tệ
        add_filter('carbon_fields_post_meta_value_save', [$this, 'formatCurrencyOnSave'], 11, 4);
        add_filter('carbon_fields_post_meta_value_load', [$this, 'formatCurrencyOnLoad'], 11, 4);
        add_action('admin_footer', [$this, 'addCurrencyFormatterScript']);

        // Carbon Fields: chuẩn hoá HEX màu thương hiệu
        add_filter('carbon_fields_post_meta_value_save', [$this, 'normalizeBrandColorsOnSave'], 12, 4);
        add_filter('carbon_fields_post_meta_value_load', [$this, 'normalizeBrandColorsOnLoad'], 12, 4);

        // Payment status tự động (priority 9999: sau khi CF save xong)
        (new ProjectPaymentService())->register();

        // Portal alias
        add_action('save_post_project', [$this, 'savePortalAlias'], 10, 1);

        // Block Sync
        $this->registerBlockSyncHooks();
    }

    // =========================================================================
    // CARBON FIELDS META BOXES
    // =========================================================================

    public function metaFields(): void
    {
        (new \App\Features\ProjectManagement\ProjectFields($this->post_type))->register();
    }

    // =========================================================================
    // NATIVE META BOX (LOGS & ALERTS)
    // =========================================================================

    public function registerLogsMetaBox(): void
    {
        add_meta_box(
            'laca_project_logs_alerts',
            'Logs & Alerts',
            [$this, 'renderLogsMetaBox'],
            'project',
            'normal',
            'high'
        );
    }

    public function renderLogsMetaBox(\WP_Post $post): void
    {
        if (!class_exists(\App\Models\ProjectLog::class) || !class_exists(\App\Models\ProjectAlert::class)) {
            echo '<p>Các bảng DB chưa được tạo. Vui lòng kích hoạt lại theme.</p>';
            return;
        }

        $projectId   = $post->ID;
        $logs        = ProjectLog::getByProject($projectId);
        $alerts      = ProjectAlert::getActive($projectId);

        $secretKey = get_post_meta($projectId, '_tracker_secret_key', true);
        if (empty($secretKey)) {
            $secretKey = wp_generate_password(24, false);
            update_post_meta($projectId, '_tracker_secret_key', $secretKey);
        }

        $portalAlias = get_post_meta($projectId, '_portal_alias', true);
        $endpoint    = rest_url('laca/v1/tracker/log');

        wp_nonce_field('laca_project_manager', 'laca_pm_nonce');
        ?>
        <div class="laca-pm-wrap laca-logs-container" data-project-id="<?php echo esc_attr($projectId); ?>">
            <?php include __DIR__ . '/../Features/ProjectManagement/Views/meta-box-col1.php'; ?>
            <?php include __DIR__ . '/../Features/ProjectManagement/Views/meta-box-col2.php'; ?>
        </div>
        <?php
    }
}
