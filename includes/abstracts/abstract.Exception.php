<?php
/**
 * Exceptions: Exception class
 *
 */

namespace WP_Wpsync\Exception;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Abstract base class to group all of this plugin's exceptions.
 *
 */
abstract class Exception extends \Exception
{

}