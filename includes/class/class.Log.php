<?php
/**
 * Log
 *
 * Log plugin errors.
 *
 * @since   1.0.0
 *
 * @package WP_wpsync
 */

namespace WP_Wpsync\App;

if (!defined('ABSPATH')) {
    exit;
}

class Log
{

    const FILE_KEY = 'wp_wpsync_log_file';
    const ALLOWED_KEY = 'wp_wpsync_allow_logging';

    /**
     * Write the log to the file.
     *
     * @param string $key
     * @param string|array|object $data
     * @param string $action
     */

    public static function write($key, $data, $action = '')
    {

        if (self::is_active()) {

            $msg = self::message($data, $action);
            $date = date('Y-m-d');
            $hash = self::log_hash();
            $error_file = WP_WPSYNC_PLUGIN_LOG_DIR . "{$key}-{$date}-{$hash}.log";

            // Create the log file dir if we do not already have one.
            if (!file_exists(WP_WPSYNC_PLUGIN_LOG_DIR)) {
                mkdir(WP_WPSYNC_PLUGIN_LOG_DIR, 0755, true);
            }

            if (!file_exists($error_file)) {

                fopen($error_file, 'w');

                // Schedule deletion of log file in 10 days
                wp_schedule_single_event(time() + (10 * DAY_IN_SECONDS), 'wpds_delete_log_file', [$error_file]);
            }

            error_log($msg, 3, $error_file);

        }

    }

    /**
     * Is log active.
     *
     * @return bool
     */

    public static function is_active()
    {
        return WP_WPSYNC_PLUGIN_LOG;
    }

    /**
     * Log message..
     *
     * @param string|array|object $data
     * @param string $action
     *
     * @return string
     */

    public static function message($data, $action)
    {

        ob_start();

        $date = date("Y-m-d H:i:s");

        echo "[{$date}] - $action - ";

        if (is_array($data) || is_object($data)) {
            print_r($data);
        } else {
            echo $data;
        }

        echo "\n";
        echo '__________________________________________________________________________';
        echo "\n";

        return ob_get_clean();

    }

    /**
     * Log Hash
     *
     * Random hash for log file name.
     *
     * @return bool|false|mixed|string|void
     */

    public static function log_hash()
    {

        if (!$log_hash = get_option('wp_wpsync_log_hash')) {

            $log_hash = wp_hash(home_url() . rand());

            add_option('wp_wpsync_log_hash', $log_hash);

        }

        return $log_hash;

    }

    /**
     * Log file contents.
     *
     * @return string
     */

    public static function log_file()
    {

        if ($file_name = get_option(Log::FILE_KEY)) {

            if ($contents = self::contents($file_name)) {
                return $contents;
            }

        }

        return __('Please choose a file and save changes.', 'wpsync-webspark');

    }

    /**
     * Contents.
     *
     * @param $file_name
     *
     * @return false|string
     */

    public static function contents($file_name)
    {

        $file = WP_WPSYNC_PLUGIN_LOG_DIR . $file_name;

        if (file_exists($file)) {
            return file_get_contents($file);
        }

        return false;

    }

    /**
     * Log files.
     *
     * @return array|bool
     */

    public static function log_files()
    {

        $files = glob(WP_WPSYNC_PLUGIN_LOG_DIR . '*.log');

        if (is_array($files) && !empty($files)) {
            return array_map('basename', $files);
        }

        return false;

    }

}