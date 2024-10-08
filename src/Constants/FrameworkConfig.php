<?php

namespace ybc\octavia\Constants;

class FrameworkConfig
{
	public static function get()
	{
		return [
			// CONFIG
			"CONFIG_PREFIX" => "OCTAVIA_",
			"CONFIG_ENV_FILE" => ".env",

			// PATHS
			"BASE_PATH" => $_SERVER['SCRIPT_FILENAME'],
			
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
			"LOG_NAME" => "octavia",
			"LOG_DIR" => "logs",
			"LOG_FILE" => "octavia.log",
			"LOG_FILE_FORMAT" => "d-m-Y",
			"LOG_TIMEZONE" => "Europe/Berlin",
			"LOG_LEVEL" => "DEBUG",
			"LOG_FORMAT" => "[%level_name%] %datetime% : %message% %context% %extra%\n",
			"LOG_DATE_FORMAT" => "d-m-Y H:i:s",
			"LOG_MAX_FILES" => 60,

			// HEALTH
			"HEALTH_AUTH_REQUIRED" => true,
			"HEALTH_USERNAME" => "octavia",
			"HEALTH_PASSWORD" => "blake",

			// GOOGLE OAUTH
			"GOOGLE_OAUTH_DATA_PATH" => "tokens.json",
			"GOOGLE_OAUTH_CONFIG_PATH" => "oauth2.json",
			"GOOGLE_OAUTH_SCOPES" => ["https://mail.google.com/"]
		];
	}
}
