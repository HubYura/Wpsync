<?php

namespace WP_Wpsync\Dependency;

use WP_Wpsync\App\SetupPlugin;
use WP_Wpsync\Exception\MissingDependenciesException;

if (!defined('ABSPATH')) {
    exit;
}

class CheckPluginDependencies
{

    public function setup()
    {
        try {
            $this->check_dependencies();
            $this->run();
        } catch (MissingDependenciesException $e) {
            $this->display_missing_dependencies_notice($e);
        }
    }

    /**
     * @throws MissingDependenciesException
     */
    private function check_dependencies()
    {
        $dependency_checker = new DependencyChecker();
        $dependency_checker->check();
    }

    private function run()
    {
        /**
         * The main function to load the only instance
         * of our master class.
         *
         * @return object|SetupPlugin
         */
        return SetupPlugin::instance();
    }

    private function display_missing_dependencies_notice(MissingDependenciesException $e)
    {
        $missing_dependency_reporter = new MissingDependencyReporter($e->get_missing_plugin_list());
        $missing_dependency_reporter->init();
    }
}