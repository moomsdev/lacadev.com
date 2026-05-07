<?php

namespace App\Settings\LacaTools\Management;

/**
 * AdminUxService
 * Handles admin UX: merchant simplification and media submenu.
 * Extracted from ManagementExperience (lines 1592–1691).
 */
class AdminUxService
{
    public function register(): void
    {
        add_action('admin_menu', [$this, 'registerUnattachedMediaMenu']);
        $this->simplifyMerchantAdmin();
    }

    /**
     * Registers a submenu under 'Media' for Unattached media.
     */
    public function registerUnattachedMediaMenu(): void
    {
        add_submenu_page(
            'upload.php',
            'Media Không Dùng',
            'Media Không Dùng',
            'manage_options',
            'upload.php?detached=1&mode=list'
        );
    }

    /**
     * Simplifies the admin for non-developer roles (hides dev-only menus).
     */
    private function simplifyMerchantAdmin(): void
    {
        add_action('admin_head', function () {
            if (current_user_can('manage_options') && !in_array(wp_get_current_user()->user_login, ['lacadev'])) {
                echo '<style>
                    #toplevel_page_laca-admin { display: none !important; }
                    #menu-settings, #menu-tools, #menu-plugins { display: none !important; }
                    .update-nag, .notice-warning, .notice-info.is-dismissible { display: none !important; }
                    #contextual-help-link-wrap { display: none !important; }
                    #wp-admin-bar-updates, #wp-admin-bar-comments, #wp-admin-bar-new-content { display: none !important; }
                </style>';
            }
        });
    }
}
