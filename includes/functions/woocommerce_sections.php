<?php
/**
 * Woocommerce
 *
* @package WP_wpsync
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Create the section beneath the advanced tab
 **/
add_filter( 'woocommerce_get_sections_advanced', 'wpsync_add_section' );
function wpsync_add_section( $sections ) {

    $sections['wpsync'] = __( WP_WPSYNC_NAME, WP_WPSYNC_TEXTDOMAIN );
    return $sections;

}

/**
 * Add settings to the specific section we created before
 */
add_filter( 'woocommerce_get_settings_advanced', 'wpsync_all_settings', 10, 2 );
function wpsync_all_settings( $settings, $current_section ) {
    if ( $current_section == 'wpsync' ) {
        $settings_wpsync = array();

        $settings_wpsync[] = array( 'name' => __( WP_WPSYNC_NAME . ' Settings', WP_WPSYNC_TEXTDOMAIN ), 'type' => 'title', 'desc' => __( 'This Product Synchronization API containing the following fields', WP_WPSYNC_TEXTDOMAIN ), 'id' => 'wpsync' );

        $settings_wpsync[] = array(
            'name'     => __( 'Activate Synchronization API', WP_WPSYNC_TEXTDOMAIN ),
            'id'       => 'wpsync_activate',
            'type'     => 'checkbox',
            'css'      => 'min-width:300px;',
            'desc'     => __( 'Activate', WP_WPSYNC_TEXTDOMAIN ),
        );

        $settings_wpsync[] = array(
            'name'     => __( 'wpsync API URL', WP_WPSYNC_TEXTDOMAIN ),
            'id'       => 'wpsync_api_url',
            'type'     => 'text',
       );

        $settings_wpsync[] = array( 'type' => 'sectionend', 'id' => 'wpsync' );
        return $settings_wpsync;

    } else {
        return $settings;
    }
}