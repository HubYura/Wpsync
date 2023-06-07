<?php

namespace WP_Wpsync\Dependency;

use WP_Wpsync\Exception\MissingDependenciesException;

if (!defined('ABSPATH')) {
    exit;
}

class DependencyChecker
{

    /**
     * @const array
     */
    const REQUIRED_PLUGINS = array(
        'WooCommerce' => [
            'name' => 'WooCommerce',
            'file' => 'woocommerce/woocommerce.php',
            'link' => 'https://wordpress.org/plugins/woocommerce/'
        ],
    );

    /**
     * @throws MissingDependenciesException
     */
    public function check()
    {
        $missing_plugins = $this->get_missing_plugin_list();

        if ($missing_plugins) {
            throw new MissingDependenciesException($missing_plugins);
        }
    }

    private function get_missing_plugin_list()
    {
        $missing_plugins_list_file = array_map(function ($plugins) {
            return $plugins['file'];
        }, self::REQUIRED_PLUGINS);

        $missing_plugins = array_filter(
            $missing_plugins_list_file,
            array($this, 'is_plugin_inactive'),
            ARRAY_FILTER_USE_BOTH
        );

        return (!empty($missing_plugins)) ? array_map(
            function ($plugins) use ($missing_plugins) {
                if (in_array($plugins['name'], array_keys($missing_plugins)))
                    return $plugins;
            }, self::REQUIRED_PLUGINS)
            : false;
    }

    private function get_plugin_active_data($main_plugin_file_name)
    {
        return !in_array($main_plugin_file_name, self::REQUIRED_PLUGINS);
    }

    private function is_plugin_inactive($main_plugin_file_path)
    {
        return !in_array($main_plugin_file_path, $this->get_active_plugins());
    }

    private function get_active_plugins()
    {
        return apply_filters('active_plugins', get_option('active_plugins'));
    }

}