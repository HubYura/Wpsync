<?php

namespace WP_Wpsync\Dependency;

if (!defined('ABSPATH')) {
    exit;
}

class MissingDependencyReporter
{
    const CAPABILITY_REQUIRED_TO_SEE_NOTICE = 'activate_plugins';

    private $missing_plugin_list;

    public function __construct($missing_plugin_list)
    {
        $this->missing_plugin_list = $missing_plugin_list;
    }

    public function init()
    {
        add_action('admin_notices', array($this, 'display_admin_notice'));
    }

    public function display_admin_notice()
    {
        if (current_user_can(self::CAPABILITY_REQUIRED_TO_SEE_NOTICE)) {
            $this->render_template();
        }
    }

    private function render_template()
    {
        $missing_plugin_names = array_map(function ($plugins) {
            return "<a href='" . $plugins['link'] . "' target='_blank'>" . $plugins['name'] . "</a>";
        }, $this->missing_plugin_list);

        include WP_WPSYNC_PLUGIN_PATH . '/views/admin/missing-dependencies-notice.php';
    }
}