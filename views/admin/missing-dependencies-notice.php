<?php
// views/admin/missing-dependencies-notice.php
?>

<div class="error notice">
    <p>
        <?php
        printf(
            wp_kses(
                __(
                    '<strong>Error: ' . WP_WPSYNC_NAME . '</strong> plugin cannot execute because'
                    . ' the following required plugins are not active: %s. Please activate these plugins.',
                    'check-plugin-dependencies'
                ),
                array(
                    'strong' => array(),
                    'em' => array(),
                )
            ),
            implode(', ', $missing_plugin_names),
        );
        ?>
    </p>
</div>