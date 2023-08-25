<?php

namespace Vendor\YbcFramework;

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
		$this->log_dir = $this->getLogDir("logs");

		if ($name_or_logger instanceof Logger) {
			$this->logger = $name_or_logger;
		} else {
			$this->name = $name_or_logger;
			$this->logger = new Logger($this->name);
			$this->initializeLogger();
		}
	}

	private function initializeLogger()
	{
		$formatter = new LineFormatter($this->log_format, $this->date_format);
		$rotating_handler = new RotatingFileHandler($this->log_dir . DIRECTORY_SEPARATOR . $this->log_file, $this->max_files, $this->level);
		$rotating_handler->setFormatter($formatter);

		$this->logger->setHandlers([$rotating_handler]);
	}

	private function getLogDir($log_dir = null)
	{
		if (!Utils::is_path_absolute($log_dir)) {
			return dirname($_SERVER["SCRIPT_FILENAME"]) . DIRECTORY_SEPARATOR . $log_dir;
		} else {
			return $log_dir;
		}
	}

	public function setLogDir($log_dir)
	{

		$this->log_dir = $this->getLogDir($log_dir);
		$this->initializeLogger();
	}

	public function setLogFile($log_file)
	{
		$this->log_file = $log_file;
		$this->initializeLogger();
	}

	public function setDateFormat($date_format)
	{
		$this->date_format = $date_format;
		$this->initializeLogger();
	}

	public function setLogFormat($log_format)
	{
		$this->log_format = $log_format;
		$this->initializeLogger();
	}

	public function setLevel($level)
	{
		$this->level = $level;
		$this->initializeLogger();
	}

	public function setMaxFiles($max_files)
	{
		$this->max_files = $max_files;
		$this->initializeLogger();
	}


	public function info($message, array $context = null)
	{
		$this->logger->info($message, $context);
	}

	public function error($message, array $context = null)
	{
		$this->logger->error($message, $context);
	}

	public function warning($message, array $context = null)
	{
		$this->logger->warning($message, $context);
	}

	public function debug($message, array $context = null)
	{
		$this->logger->debug($message, $context);
	}
}
