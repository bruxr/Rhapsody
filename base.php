<?php
/**
 * Vanilla
 *
 * base.php
 * Setups the Vanilla Theme Framework
 * 
 * @package Vanilla
 * @author brux <brux.romuar@gmail.com>
 */

// Load benchmarking as soon as possible
if ( WP_DEBUG )
{
    require_once 'benchmarking.php';
    vanilla_mark('setup_start');
}

// Declare the version ...
define('VANILLA_VERSION', 0.3);

// ... and the path to the vanilla files
define('VANILLA_PATH', dirname(__FILE__));

// Do any configuration before framework files are loaded
do_action('vanilla_config');

// Declare theme-specific constants
$theme = wp_get_theme();
if ( ! defined('VANILLA_THEME_SLUG') ) define('VANILLA_THEME_SLUG', $theme->get_template());
if ( ! defined('VANILLA_NO_SETTINGS_PAGE') ) define('VANILLA_NO_SETTINGS_PAGE', false);
unset($theme);

// Load framework files
require_once VANILLA_PATH . '/hooks.php';
require_once VANILLA_PATH . '/functions.php';
require_once VANILLA_PATH . '/bootstrap.php';
require_once VANILLA_PATH . '/admin.php';
require_once VANILLA_PATH . '/PostType.php';
require_once VANILLA_PATH . '/Settings.php';

// We're done
do_action('vanilla_ready');

// Mark the end of setup
if ( function_exists('vanilla_mark') ) vanilla_mark('setup_end');