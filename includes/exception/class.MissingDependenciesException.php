<?php
/**
 * Exceptions: MissingDependenciesException class
 *
 */

namespace WP_Wpsync\Exception;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Indicates that plugin dependencies were not met.
 *
 * Holds the names of the plugins that are our plugin depends on that are not active.
 *
 * @since 1.0.0
 *
 * @see \WP_Wpsync\abstracts\Exception
 */
class MissingDependenciesException extends Exception
{

    /**
     * Names of the plugins that are required but are inactive.
     *
     * @since 1.0.0
     * @var string[]
     */
    private $missing_plugin_names;

    /**
     * Data list of the plugins that are required but are inactive.
     *
     * @since 1.0.0
     * @var []
     */
    private $missing_plugin_list;

    /**
     * Missing_Dependencies_Exception constructor.
     *
     * @param $missing_plugin
     * @since 1.0.0
     *
     */
    public function __construct($missing_plugin)
    {
        parent::__construct();
        $this->missing_plugin_names = array_map(function ($plugins) {
            return $plugins['name'];
        }, $missing_plugin);
        $this->missing_plugin_list = $missing_plugin;
    }

    /**
     * Returns the list of names of plugins that are required but are inactive.
     *
     * @return string[] Names of the plugins that are required but are inactive.
     * @since 1.0.0
     *
     */
    public function get_missing_plugin_names()
    {
        return $this->missing_plugin_names;
    }

    /**
     * Returns the list of data of plugins that are required but are inactive.
     *
     * @return string[] Data of the plugins that are required but are inactive.
     * @since 1.0.0
     *
     */
    public function get_missing_plugin_list()
    {
        return $this->missing_plugin_list;
    }

}