<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class WC_Clover_Logger {

	/**
	 * @var bool Indicates if logging is enabled.
	 */
	private static bool $is_logging_enabled = true;

	/**
	 * Sets the logging enabled status.
	 *
	 * @param bool $debug_mode Indicates if debug mode is enabled.
	 * @return void
	 */
	public static function set_is_logging_enabled( bool $debug_mode ): void {
		self::$is_logging_enabled = $debug_mode;
	}

	/**
	 * Logs a message with a given level and context.
	 *
	 * @param string $level The log level.
	 * @param string $message The log message.
	 * @param array $context The log context.
	 * @return void
	 */
	private static function log( string $level, string $message, array $context ): void {
		if ( self::$is_logging_enabled ) {
			wc_get_logger()->log( $level, $message, $context );
		}
	}

	/**
	 * Logs an emergency message.
	 *
	 * @param string $message The log message.
	 * @param array $context The log context.
	 * @return void
	 */
	public static function emergency( string $message, array $context = array() ): void {
		self::log( WC_Log_Levels::EMERGENCY, $message, $context );
	}

	/**
	 * Logs an alert message.
	 *
	 * @param string $message The log message.
	 * @param array $context The log context.
	 * @return void
	 */
	public static function alert( string $message, array $context = array() ): void {
		self::log( WC_Log_Levels::ALERT, $message, $context );
	}

	/**
	 * Logs a critical message.
	 *
	 * @param string $message The log message.
	 * @param array $context The log context.
	 * @return void
	 */
	public static function critical( string $message, array $context = array() ): void {
		self::log( WC_Log_Levels::CRITICAL, $message, $context );
	}

	/**
	 * Logs an error message.
	 *
	 * @param string $message The log message.
	 * @param array $context The log context.
	 * @return void
	 */
	public static function error( string $message, array $context = array() ): void {
		self::log( WC_Log_Levels::ERROR, $message, $context );
	}

	/**
	 * Logs a warning message.
	 *
	 * @param string $message The log message.
	 * @param array $context The log context.
	 * @return void
	 */
	public static function warning( string $message, array $context = array() ): void {
		self::log( WC_Log_Levels::WARNING, $message, $context );
	}

	/**
	 * Logs a notice message.
	 *
	 * @param string $message The log message.
	 * @param array $context The log context.
	 * @return void
	 */
	public static function notice( string $message, array $context = array() ): void {
		self::log( WC_Log_Levels::NOTICE, $message, $context );
	}

	/**
	 * Logs an info message.
	 *
	 * @param string $message The log message.
	 * @param array $context The log context.
	 * @return void
	 */
	public static function info( string $message, array $context = array() ): void {
		self::log( WC_Log_Levels::INFO, $message, $context );
	}

	/**
	 * Logs a debug message.
	 *
	 * @param string $message The log message.
	 * @param array $context The log context.
	 * @return void
	 */
	public static function debug( string $message, array $context = array() ): void {
		self::log( WC_Log_Levels::DEBUG, $message, $context );
	}
}
