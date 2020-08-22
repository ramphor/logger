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
use Monolog\Handler\SlackHandler;

$composer_file = sprintf( '%s/vendor/autoload.php', dirname( __FILE__ ) );
if ( file_exists( $composer_file ) ) {
	require_once $composer_file;
}

if ( ! class_exists( Logger::class ) ) {
	return error_log( sprintf( 'The class %s is not found to register custom logger', Logger::class ) );
}

function load_ramphor_logger_firstly() {
	$path = substr(
		__FILE__,
		strlen( __FILE__ ) - 33
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

function ramphor_logger_error_trigger( $errno, $errstr, $errfile, $errline, $errcontext = array() ) {
	$ignore_wp_core = apply_filters(
		'ramphor_logger_ignore_wordpress_core_warning',
		true
	);
	$errdir         = preg_replace(
		'/([^\/]+)(.+)?/',
		'$1',
		str_replace( ABSPATH, '', $errfile )
	);
	if ( $ignore_wp_core && in_array( $errdir, array( 'wp-includes', 'wp-admin' ) ) ) {
		return;
	}

	ob_start();
	debug_print_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS );
	$backtrace = ob_get_clean();
	$message   = sprintf( "%s in %s line %s\n%s", $errstr, $errfile, $errline, $backtrace );
	$logger    = Logger::instance();

	$logger->get()->warning( $message );
}
set_error_handler( 'ramphor_logger_error_trigger' );

function ramphor_logger_register_logger( $logger, $id ) {
	if ( ! constant( 'RAMPHOR_LOGGER_SLACK_HANDLER_ENABLE' ) ) {
		return;
	}
	$token   = constant( 'RAMPHOR_LOGGER_SLACK_TOKEN' );
	$channel = constant( 'RAMPHOR_LOGGER_SLACK_CHANNEL' );
	$botname = constant( 'RAMPHOR_LOGGER_SLACK_BOT_NAME' );

	if ( isset( $token, $channel ) ) {
		$slackHandler = new SlackHandler( $token, $channel, $botname );
		$slackHandler->setLevel(
			apply_filters(
				'ramphor_logger_slack_handler_log_level',
				$logger::NOTICE
			)
		);
		$logger->pushHandler( $slackHandler );
	}
}
add_action( 'ramphor_logger_register_logger', 'ramphor_logger_register_logger', 10, 2 );

// Init the Ramphor Logger instance
add_action( 'loaded_plugins', array( Logger::class, 'instance' ) );
