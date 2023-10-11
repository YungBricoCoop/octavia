<?php

namespace ybc\octavia\Constants;

class Config
{
	public static function get()
	{
		return [
			// UPLOAD
			"UPLOAD_DIR" => "uploads",
			"UPLOAD_MAX_SIZE" => "10MB",
			"UPLOAD_ALLOW_MULTIPLE_FILES" => true,

			// SESSION
			"SESSION_NAME" => "octavia_session",
			"SESSION_LIFETIME" => 3600,
			"SESSION_IS_LOGGED_KEY" => "is_logged",
			"SESSION_IS_ADMIN_KEY" => "is_admin",

			// LOG
			"LOG_DIR" => "logs",
			"LOG_FILE" => "octavia.log",
			"LOG_FILE_FORMAT" => "d-m-Y",
			"LOG_LEVEL" => "DEBUG",
			"LOG_FORMAT" => "[%level_name%] %datetime% : %message% %context% %extra%\n",
			"LOG_DATE_FORMAT" => "d-m-Y H:i:s",
			"LOG_MAX_FILES" => 60,

			// HEALTH
			"HEALTH_AUTH_REQUIRED" => true,
			"HEALTH_USERNAME" => "octavia",
			"HEALTH_PASSWORD" => "blake",
		];
	}
}
