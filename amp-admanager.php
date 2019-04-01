<?php
/**
 * AMP Ad Manager plugin bootstrap file.
 *
 * @wordpress-plugin
 * Plugin Name:  AMP AdManager
 * Plugin URI:   https://github.com/rtCamp/amp-admanager
 * Description:  AMP ads for all WordPress sites (AMP and Non-AMP)
 * Version:      1.0.0
 * Author:       rtCamp
 * Author URI:   https://rtcamp.com
 * Text Domain:  amp-admanager
 *
 * @package AMP_AdManager
 */

define( 'AMP_ADMANAGER_VERSION', '1.0.0' );
define( 'AMP_ADMANAGER_ROOT', __DIR__ );

require_once AMP_ADMANAGER_ROOT . '/classes/class-amp-admanager.php';
require_once AMP_ADMANAGER_ROOT . '/classes/class-shortcode.php';

new AMP_AdManager\AMP_AdManager();
new AMP_AdManager\Shortcode();
