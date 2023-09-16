<?php

namespace ybc\octavia\Utils;

use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;
use \Monolog\Level;

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

	public function __construct($name_or_logger, $level = Level::Debug)
	{
		$this->log_file = "app.log";
		$this->date_format = "d-m-Y H:i:s";
		$this->log_format = "[%level_name%] %datetime% : %message% %context% %extra%\n";
		$this->level = $level;
		$this->max_files = 7;
		$this->log_dir = $this->get_log_dir("logs");

		if ($name_or_logger instanceof Logger) {
			$this->logger = $name_or_logger;
		} else {
			$this->name = $name_or_logger;
			$this->logger = new Logger($this->name);
			$this->init_logger();
		}
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
	 * Set the log directory, path can be absolute or relative
	 * @example set_log_dir("logs")
	 * @param string $log_dir
	 */
	public function set_log_dir($log_dir)
	{

		$this->log_dir = $this->get_log_dir($log_dir);
		$this->init_logger();
	}

	/**
	 * Set the log file, name of the file with extension
	 * @param string $log_file
	 * @example set_log_file("app.log")
	 * @return void
	 */
	public function set_log_file($log_file)
	{
		$this->log_file = $log_file;
		$this->init_logger();
	}

	/**
	 * Set the date format displayed in the log
	 * @param string $date_format
	 * @example set_date_format("d-m-Y H:i:s")
	 * @return void
	 */
	public function set_date_format($date_format)
	{
		$this->date_format = $date_format;
		$this->init_logger();
	}

	/**
	 * Set the log format
	 * @param string $log_format
	 * @example set_log_format("[%level_name%] %datetime% : %message% %context% %extra%\n")
	 * @return void
	 */
	public function set_log_format($log_format)
	{
		$this->log_format = $log_format;
		$this->init_logger();
	}

	/**
	 * Set the log level
	 * @param string $level
	 * @example set_level(Level::Debug)
	 * @return void
	 */
	public function set_level($level)
	{
		$this->level = $level;
		$this->init_logger();
	}

	/**
	 * Set the max number of files
	 * @param int $max_files
	 * @example set_max_files(7)
	 * @return void
	 */
	public function set_max_files($max_files)
	{
		$this->max_files = $max_files;
		$this->init_logger();
	}


	/**
	 * Log a message at the INFO level
	 * @param string $message
	 * @param array $context
	 * @return void
	 */
	public function info($message, array $context = [])
	{
		$this->logger->info($message, $context);
	}

	/**
	 * Log a message at the ERROR level
	 * @param string $message
	 * @param array $context
	 * @return void
	 */
	public function error($message, array $context = [])
	{
		$this->logger->error($message, $context);
	}

	/**
	 * Log a message at the WARNING level
	 * @param string $message
	 * @param array $context
	 * @return void
	 */
	public function warning($message, array $context = [])
	{
		$this->logger->warning($message, $context);
	}

	/**
	 * Log a message at the DEBUG level
	 * @param string $message
	 * @param array $context
	 * @return void
	 */
	public function debug($message, array $context = [])
	{
		$this->logger->debug($message, $context);
	}
}
