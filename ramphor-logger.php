<?php
/**
 * Plugin Name: Ramphor Logger
 * Plugin URI: https://github.com/ramphor/logger
 * Author: Ramphor Premium
 * Author URI: https://puleeno.com
 * Version: 0.2.0
 * Description: The logger library for themes, plugins in WordPress and tracking all errors of PHP/WordPress
 * Tags: logger, monitor, errors
 */

use Ramphor\Logger\Logger;

$composer_file = sprintf( '%s/vendor/autoload.php', dirname( __FILE__ ) );
if ( file_exists( $composer_file ) ) {
	require_once $composer_file;
}

if ( ! class_exists( Logger::class ) ) {
	return error_log( sprintf( 'The class %s is not found to register custom logger', Logger::class ) );
}

function load_ramphor_logger_firstly() {
	$me = substr(
		RAMPHOR_LOGGER_PLUGIN_FILE,
		strlen( RAMPHOR_LOGGER_PLUGIN_FILE ) - 33
	);

	// Set me at first index
	if ( $plugins = get_option( 'active_plugins' ) ) {
		if ( $key = array_search( $path, $plugins ) ) {
			array_splice( $plugins, $key, 1 );
			array_unshift( $plugins, $path );
			update_option( 'active_plugins', $plugins );
		}
	}
}
add_action( 'activated_plugin', 'load_ramphor_logger_firstly', 100 );

function ramphor_logger_exception_trigger( $e ) {
	$message = sprintf(
		"%s\n%s",
		$e->getMessage(),
		$e->getTraceAsString()
	);
	$logger  = Logger::instance();
	$logger->get()->error( $message );

	_e( 'Your website has errors. Please contact to webadmin or your developer to get more informations', 'ramphor_logger' );
}
set_exception_handler( 'ramphor_logger_exception_trigger' );

// Init the Ramphor Logger instance
add_action( 'loaded_plugins', array( Logger::class, 'instance' ) );
