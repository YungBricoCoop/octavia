<?php

namespace ybc\octavia\Utils;

use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;

class Log
{
	private static $instance;

	private $logger;
	private $name;
	private $log_dir;
	private $log_file;
	private $log_date_format;
	private $log_date_timezone;
	private $log_format;
	private $log_level;
	private $max_files;

	public static function get_instance()
	{
		if (!isset(self::$instance)) {
			self::$instance = new Log();
		}
		return self::$instance;
	}

	private function __construct()
	{
		$this->name = OCTAVIA_LOG_NAME;
		$this->log_file = OCTAVIA_LOG_FILE;
		$this->log_format = OCTAVIA_LOG_FORMAT;
		$this->log_date_format = OCTAVIA_LOG_DATE_FORMAT;
		$this->log_date_timezone = new \DateTimeZone(OCTAVIA_LOG_TIMEZONE); 
		$this->log_level = OCTAVIA_LOG_LEVEL;
		$this->max_files = OCTAVIA_LOG_MAX_FILES;
		$this->log_dir = $this->get_log_dir(OCTAVIA_LOG_DIR);

		$this->logger = new Logger($this->name);
		$this->init_logger();
	}

	private function init_logger()
	{
		$formatter = new LineFormatter($this->log_format, $this->log_date_format, true, true);
		$rotating_handler = new RotatingFileHandler($this->log_dir . DIRECTORY_SEPARATOR . $this->log_file, $this->max_files, $this->log_level);
		$rotating_handler->setFormatter($formatter);
		
		$this->logger->setTimezone($this->log_date_timezone);
		$this->logger->setHandlers([$rotating_handler]);
	}


	/**
	 * Get the log directory
	 * @param string $log_dir
	 * @return string
	 */
	private function get_log_dir($log_dir = null)
	{
		if (!Utils::is_path_absolute($log_dir)) {
			return dirname($_SERVER["SCRIPT_FILENAME"]) . DIRECTORY_SEPARATOR . $log_dir;
		} else {
			return $log_dir;
		}
	}

	/**
	 * Log a message at the INFO level
	 * @param string $message
	 * @param array $context
	 * @return void
	 */
	public static function info($message, array $context = [])
	{
		$log = self::get_instance();
		$log->logger->info($message, $context);
	}

	/**
	 * Log a message at the ERROR level
	 * @param string $message
	 * @param array $context
	 * @return void
	 */
	public static function error($message, array $context = [])
	{
		$log = self::get_instance();
		$log->logger->error($message, $context);
	}

	/**
	 * Log a message at the WARNING level
	 * @param string $message
	 * @param array $context
	 * @return void
	 */
	public static function warning($message, array $context = [])
	{
		$log = self::get_instance();
		$log->logger->warning($message, $context);
	}

	/**
	 * Log a message at the DEBUG level
	 * @param string $message
	 * @param array $context
	 * @return void
	 */
	public static function debug($message, array $context = [])
	{
		$log = self::get_instance();
		$log->logger->debug($message, $context);
	}
}
