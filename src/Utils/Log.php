<?php

namespace ybc\octavia\Utils;

use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;

class Log
{
	private $logger;

	private $name;
	private $log_dir;
	private $log_file;
	private $date_format;
	private $log_format;
	private $level;
	private $max_files;
	private static $instance;

	public static function get_instance()
	{
		if (!isset(self::$instance)) {
			self::$instance = new Log();
		}
		return self::$instance;
	}

	private function __construct()
	{
		$this->log_file = OCTAVIA_LOG_FILE;
		$this->log_format = OCTAVIA_LOG_FORMAT;
		$this->date_format = OCTAVIA_LOG_DATE_FORMAT;
		$this->level = OCTAVIA_LOG_LEVEL;
		$this->max_files = OCTAVIA_LOG_MAX_FILES;
		$this->log_dir = $this->get_log_dir(OCTAVIA_LOG_DIR);

		$this->logger = new Logger($this->name);
		$this->init_logger();
	}

	private function init_logger()
	{
		$formatter = new LineFormatter($this->log_format, $this->date_format, true, true);
		$rotating_handler = new RotatingFileHandler($this->log_dir . DIRECTORY_SEPARATOR . $this->log_file, $this->max_files, $this->level);
		$rotating_handler->setFormatter($formatter);

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
		$log->info($message, $context);
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
		$log->error($message, $context);
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
		$log->warning($message, $context);
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
		$log->debug($message, $context);
	}
}
