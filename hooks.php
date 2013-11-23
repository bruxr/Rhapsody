<?php
/**
 * Vanilla
 *
 * hooks.php
 * Defines functions that hook to Wordpress.
 *
 * @package Vanilla
 * @author brux <brux.romuar@gmail.com>
 */

/**
 * Registers all javascript variables/localizations that were
 * registered via vanilla_localize_script().
 *
 * @since  0.3
 */
function vanilla_localize_scripts()
{

    $l10n = wp_cache_get('vanilla_scripts_l10n');

    if ( ! $l10n ) return;

    foreach ( $l10n as $handle => $data )
    {
        foreach ( $data as $object_name => $l10n )
        {
            wp_localize_script($handle, $object_name, $l10n);
        }
    }

}
add_action('wp_enqueue_scripts', 'vanilla_localize_scripts', 99);