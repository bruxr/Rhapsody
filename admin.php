<?php
/**
 * Vanilla
 *
 * admin.php
 * Admin-related functions
 * 
 * @package Vanilla
 * @author brux <brux.romuar@gmail.com>
 */

/**
 * Adds the Theme Settings page under the Appearance group.
 *
 * @since  0.3
 */
function vanilla_admin_menus()
{

    if ( vanilla_has_feature('settings') )
    {

        $theme = wp_get_theme();

        // The page title
        $page_title = sprintf('%s Settings', $theme->display('Name'));
        $page_title = apply_filters('vanilla_settings_page_title', $page_title);

        // Menu title
        $menu_title = apply_filters('vanilla_settings_page_menu_title', 'Settings');

        // Page slug
        $page_slug = sprintf('%s-settings', VANILLA_THEME_SLUG);
        $page_slug = apply_filters('vanilla_settings_page_slug', $page_slug);

        // Capability
        $capability = apply_filters('vanilla_settings_page_capability', 'edit_theme_options');

        add_theme_page($page_title, $menu_title, $capability, $page_slug, 'vanilla_settings_page');

    }

}
add_action('admin_menu', 'vanilla_admin_menus');

/**
 * Creates the Theme Settings page.
 *
 * @since  0.3
 */
function vanilla_settings_page()
{

    $theme = wp_get_theme();

    // Generate the page title
    $page_title = sprintf('%s Settings', $theme->display('Name'));
    $page_title = apply_filters('vanilla_settings_page_title', $page_title);

    $features = wp_cache_get('vanilla_features');
    $settings = $features['settings'];

?>

<div class="wrap">

    <?php screen_icon(); ?>
    <h2><?php echo $page_title; ?></h2>
    
    <?php settings_errors(); ?>

    <form action="options.php" method="post">
    
        <?php if ( $settings ) $settings->do_settings(); ?>

        <p class="submit">
            <input type="submit" name="submit" value="<?php echo apply_filters('vanilla_settings_page_submit', 'Save Changes'); ?>" class="button-primary">
            <?php do_action('vanilla_settings_page_submit'); ?>
        </p>
    
    </form>

</div>

<?php

}