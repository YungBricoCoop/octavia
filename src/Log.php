<?php

namespace Vendor\YbcFramework;

use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;

class Log
{

	private $logger;
	public $level;
	public $log_file;
	public $log_format;
	public $log_date_format;


	/**
	 * Initializes with an existing logger or creates a new one.
	 *
	 * @param Logger|null $logger
	 */
	public function __construct(?string $name = null, ?Logger $logger = null, ?string $level = null)
	{
		$this->level = $level ?? Logger::DEBUG;
		$this->log_file =  __DIR__ . "/log.log";
		$this->log_format = "[%level_name%] %datetime% : %message% %context% %extra%\n";
		$this->log_date_format = 'd-m-Y H:i:s';

		$this->logger = $logger ?? new Logger($name ?? 'DEFAULT_LOGGER');
		$this->logger->pushHandler(new StreamHandler($this->log_file, $level ?? Logger::DEBUG));
		$this->setLogFormat($this->log_format, $this->log_date_format);
	}

	/**
	 * Configures RotatingFileHandler.
	 *
	 * @param string $directory
	 * @param string $name
	 * @param int $maxFiles
	 * @param int $level
	 * @param bool $bubble
	 * @param int $filePermission
	 */
	public function configureRotatingFileHandler(
		string $directory,
		string $name = 'log',
		int $maxFiles = 0,
		int $level = Logger::DEBUG,
		bool $bubble = true,
		int $filePermission = 0664
	) {
		$logFile = $directory . "/" . $name . ".log";
		$rotatingHandler = new RotatingFileHandler($logFile, $maxFiles, $level, $bubble, $filePermission);
		$this->logger->pushHandler($rotatingHandler);
	}

	/**
	 * Configures the log format
	 *
	 * @param string $format
	 * @param string $dateFormat
	 */
	public function setLogFormat(string $format, string $dateFormat)
	{
		$handlers = $this->logger->getHandlers();
		$handler = end($handlers);
		if ($handler) {
			$this->log_format = $format;
			$this->log_date_format = $dateFormat;

			$formatter = new LineFormatter($format, $dateFormat, true, true);
			$handler->setFormatter($formatter);
		}
	}

	/**
	 * Configures the log level
	 *
	 * @param string $level
	 */
	public function setLogLevel(string $level)
	{
		$handler = end($this->logger->getHandlers());
		if ($handler) {
			$this->level = $level;
			$handler->setLevel($level);
		}
	}

	/**
	 * Configures filename format for the latest handler.
	 *
	 * @param string $filenameFormat
	 * @param string $dateSuffix
	 */
	public function setFilenameFormat(string $filenameFormat, string $dateSuffix)
	{
		$handler = end($this->logger->getHandlers());
		if ($handler && method_exists($handler, 'setFilenameFormat')) {
			$handler->setFilenameFormat($filenameFormat, $dateSuffix);
		}
	}

	/**
	 * Logs a message.
	 *
	 * @param string $level
	 * @param string $message
	 * @param array $context
	 */
	public function log(string $level, string $message, array $context = [])
	{
		$this->logger->log($level, $message, $context);
	}

	public function info(string $message, array $context = [])
	{
		$this->logger->log("INFO", $message, $context);
	}

	public function error(string $message, array $context = [])
	{
		$this->logger->log("ERROR", $message, $context);
	}

	public function warning(string $message, array $context = [])
	{
		$this->logger->log("WARNING", $message, $context);
	}

	public function debug(string $message, array $context = [])
	{
		$this->logger->log("DEBUG", $message, $context);
	}
}
