<?php

namespace App\Databases;

/**
 * Quản lý custom table wp_laca_project_logs
 * Ghi lại toàn bộ lịch sử hoạt động của mỗi dự án:
 * cập nhật plugin, sửa code, deploy, ghi chú, v.v.
 */
class ProjectLogTable
{
    const TABLE_NAME    = 'laca_project_logs';
    const TABLE_VERSION = '1.0.0';
    const VERSION_KEY   = 'laca_project_log_table_version';

    /**
     * Lấy tên table có prefix của WordPress
     */
    public static function getTableName(): string
    {
        global $wpdb;
        return $wpdb->prefix . self::TABLE_NAME;
    }

    /**
     * Tạo bảng nếu chưa tồn tại hoặc cần upgrade
     * Gọi qua after_switch_theme hook
     */
    public static function install(): void
    {
        global $wpdb;

        $currentVersion = get_option(self::VERSION_KEY, '0');
        if (version_compare($currentVersion, self::TABLE_VERSION, '>=')) {
            return; // Đã đúng version, không cần tạo lại
        }

        $tableName   = self::getTableName();
        $charsetCollate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$tableName} (
            id          BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            project_id  BIGINT(20) UNSIGNED NOT NULL,
            log_date    DATE NOT NULL,
            log_type    VARCHAR(50) NOT NULL DEFAULT 'note',
            log_content TEXT NOT NULL,
            log_by      VARCHAR(150) NOT NULL DEFAULT '',
            is_auto     TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '1 = ghi tự động qua tracker, 0 = ghi thủ công',
            meta        TEXT NULL COMMENT 'JSON metadata tuỳ chọn (version cũ, version mới, ...)',
            created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY project_id (project_id),
            KEY log_date   (log_date),
            KEY log_type   (log_type)
        ) {$charsetCollate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);

        update_option(self::VERSION_KEY, self::TABLE_VERSION);
    }

    /**
     * Xoá bảng khi cần (dùng khi uninstall)
     */
    public static function uninstall(): void
    {
        global $wpdb;
        $tableName = self::getTableName();
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $wpdb->query("DROP TABLE IF EXISTS {$tableName}");
        delete_option(self::VERSION_KEY);
    }
}
