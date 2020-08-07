<?php
/**
 * Plugin Name: Ramphor Logger
 * Plugin URI: https://github.com/ramphor/logger
 * Author: Ramphor Premium
 * Author URI: https://puleeno.com
 * Version: 0.2.0
 * Description: Wrap the Monolog library for themes and plugins in WordPress
 * Tags: logger
 */

use Ramphor\Logger\Logger;

define( 'RAMPHOR_LOGGER_PLUGIN_FILE', __FILE__ );

$composer_file = sprintf( '%s/vendor/autoload.php', dirname( __FILE__ ) );
if ( file_exists( $composer_file ) ) {
	require_once $composer_file;
}

if ( ! class_exists( Logger::class ) ) {
	return error_log( sprintf( 'The class %s is not found to register custom logger', Logger::class ) );
}

if ( ! function_exists( 'ramphor_logger' ) ) {
	function ramphor_logger() {
		return Logger::instance();
	}
}

$GLOBALS['ramphor_logger'] = ramphor_logger();

