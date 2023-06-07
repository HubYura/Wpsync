<?php

/*
Plugin Name: Wpsync Webspark
Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
Description: A brief description of the Plugin.
Version: 0.0.1
Author: Moisiu Yurii
Author URI: http://URI_Of_The_Plugin_Author
License: A "Slug" license name e.g. GPL2
*/

namespace WP_Wpsync;

use WP_Wpsync\Dependency\CheckPluginDependencies;

if (!defined('ABSPATH')) {
    exit;
}

$uploads = wp_get_upload_dir();

$defines = [
    'WP_WPSYNC_VERSION' => '0.0.1',
    'WP_WPSYNC_PLUGIN_FILE' => __FILE__,
    'WP_WPSYNC_PLUGIN_PATH' => plugin_dir_path(__FILE__),
    'WP_WPSYNC_PLUGIN_BASE' => plugin_basename(__FILE__),
    'WP_WPSYNC_PLUGIN_URI' => plugins_url(__FILE__),
    'WP_WPSYNC_PLUGIN_LOG_DIR' => $uploads['basedir'] . '/wp-wpsync-logs/',
    'WP_WPSYNC_PLUGIN_LOG' => true,
    'WP_WPSYNC_NAME' => 'Wpsync Webspark',
    'WP_WPSYNC_TEXTDOMAIN' => 'wpsync-webspark',
];

foreach ($defines as $define => $value) {
    if (!defined($define)) {
        define($define, $value);
    }
}

add_action('plugins_loaded', function () {

    foreach (glob(plugin_dir_path(__FILE__) . 'includes/**/*.php') as $file) {
        require_once $file;
    }

    $check_plugin_dependencies = new CheckPluginDependencies();
    $check_plugin_dependencies->setup();

    add_action('init', function () {
        load_plugin_textdomain('wp-wpsync', false, basename(dirname(__FILE__)) . '/languages');
    });

});