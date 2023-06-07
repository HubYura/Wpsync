<?php

namespace WP_Wpsync\App;

if (!defined('ABSPATH')) {
    exit;
}

class SetupPlugin
{
    /**
     * instance
     *
     * @var SetupPlugin
     */
    private static $instance;

    /**
     * Main SetupPlugin Instance.
     *
     * @staticvar array $instance
     * @return object|SetupPlugin|false The one true SetupPlugin
     */
    public static function instance()
    {
        if (!isset(self::$instance) && !(self::$instance instanceof SetupPlugin)) {
            self::$instance = new SetupPlugin;

            if (!self::$instance->activation())
                return false;

            self::$instance->includes();
            self::$instance->base_hooks();

            do_action('WP_WPSYNC_plugin_configured');

            if (!wp_next_scheduled('wpsync_daily_maintenance')) {
                wp_schedule_event(time(), 'hourly', 'wpsync_daily_maintenance');
            }

            add_action('wpsync_daily_maintenance', array(self::$instance, 'import'));

            do_action('WP_WPSYNC_plugin_loaded');
        }

        return self::$instance;
    }

    /**
     * Include required files.
     *
     * @return void
     */
    private function includes()
    {

    }

    /**
     * Add base hooks for the core functionality
     *
     * @return void
     */
    private function base_hooks()
    {
        add_action('plugins_loaded', array(self::$instance, 'load_textdomain'));
        register_deactivation_hook(WP_WPSYNC_PLUGIN_FILE, array(self::$instance, 'register_deactivation_hook_callback'));
    }

    /**
     * Activation
     *
     * @return bool
     */
    public function activation() {
        $activate = get_option('wpsync_activate');
        $api_url = get_option('wpsync_api_url');

        return ($activate === 'yes' && !empty($api_url));
    }

    /**
     * Import
     *
     * @return void
     */
    public function import()
    {
        (new ProductRequest)->get_products();
    }

    /**
     * Loads the plugin language files.
     *
     * @return void
     */
    public function load_textdomain()
    {
        load_plugin_textdomain('wp-wpsync', false, basename(dirname(__FILE__)) . '/languages/');
    }

    /**
     * Register the plugin deactivation callback
     *
     * @return void
     */
    public function register_deactivation_hook_callback()
    {
        $next_event = wp_next_scheduled('wpsync_daily_maintenance');

        if ($next_event) {
            wp_unschedule_event($next_event, 'wpsync_daily_maintenance');
        }
    }
}