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

define( 'RAMPHOR_LOGGER_PLUGIN_FILE', __FILE__ );

$composer_file = sprintf( '%s/vendor/autoload.php', dirname( __FILE__ ) );
if ( file_exists( $composer_file ) ) {
	require_once $composer_file;
}

if ( ! class_exist( Ramphor\Logger::class ) ) {
	return error_log( 'Ramphor\Logger class is not exists to setup custom logger' );
}

function ramphor_logger( int $errno, string $errstr, string $errfile, int $errline ) {
}
set_error_handler( 'ramphor_logger' );
