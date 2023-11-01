<?php

namespace ybc\octavia;

class CustomException extends \Exception
{
	protected $status_code = 200;
	protected $detail = null;

	public function __construct($detail = null, $message = null, $code = 0, \Exception $previous = null)
	{
		$this->detail = $detail;
		if (is_null($message)) {
			$message = $this->get_msg();
		}
		parent::__construct($message, $code, $previous);
	}

	public function getStatusCode()
	{
		return $this->status_code;
	}

	public function getDetail()
	{
		return $this->detail;
	}

	protected function get_msg()
	{
		return "CUSTOM_EXCEPTION";
	}
}

class UnauthorizedException extends CustomException
{
	protected $status_code = 401;
	protected function get_msg()
	{
		return "UNAUTHORIZED";
	}
}

class ForbiddenException extends CustomException
{
	protected $status_code = 403;
	protected function get_msg()
	{
		return "FORBIDDEN";
	}
}

class NotFoundException extends CustomException
{
	protected $status_code = 404;
	protected function get_msg()
	{
		return "NOT_FOUND";
	}
}

class MethodNotAllowedException extends CustomException
{
	protected $status_code = 405;
	protected function get_msg()
	{
		return "METHOD_NOT_ALLOWED";
	}
}

class ConflictException extends CustomException
{
	protected $status_code = 409;
	protected function get_msg()
	{
		return "CONFLICT";
	}
}

class InternalServerErrorException extends CustomException
{
	protected $status_code = 500;
	protected function get_msg()
	{
		return "INTERNAL_SERVERERROR";
	}
}

class MissingQueryParameterException extends CustomException
{
	protected $status_code = 400;
	protected function get_msg()
	{
		return "MISSING_QUERY_PARAMETER";
	}
}

class WrongQueryParameterTypeException extends CustomException
{
	protected $status_code = 400;
	protected function get_msg()
	{
		return "WRONG_QUERY_PARAMETER_TYPE";
	}
}

class MissingBodyParameterException extends CustomException
{
	protected $status_code = 400;
	protected function get_msg()
	{
		return "MISSING BODY PARAMETER";
	}
}

class MissingObjectPropertyException extends CustomException
{
	protected $status_code = 400;
	protected function get_msg()
	{
		return "MISSING_OBJECT_PROPERTY";
	}
}

class WrongObjectPropertyTypeException extends CustomException
{
	protected $status_code = 400;
	protected function get_msg()
	{
		return "WRONG_OBJECT_PROPERTY_TYPE";
	}
}

class WrongPathParameterTypeException extends CustomException
{
	protected $status_code = 400;
	protected function get_msg()
	{
		return "WRONG_PATH_PARAMETER_TYPE";
	}
}

class MultipleFilesNotAllowedException extends CustomException
{
	protected $status_code = 400;

	protected function get_msg()
	{
		return "MULTIPLE_FILES_NOT_ALLOWED";
	}
}

class FileUploadErrorException extends CustomException
{
	protected $status_code = 400;

	protected function get_msg()
	{
		return "FILE_UPLOAD_ERROR";
	}
}

class FileSizeExceededException extends CustomException
{
	protected $status_code = 400;

	protected function get_msg()
	{
		return "FILE_SIZE_EXCEEDED";
	}
}

class FileTypeNotAllowedException extends CustomException
{
	protected $status_code = 400;

	protected function get_msg()
	{
		return "FILE_TYPE_NOT_ALLOWED";
	}
}

class ErrorPromptingGoogleOAuthException extends CustomException
{
	protected $status_code = 400;

	protected function get_msg()
	{
		return "ERROR_PROMPTING_GOOGLE_OAUTH";
	}
}

class RateLimitExceededException extends CustomException
{
	protected $status_code = 429;

	protected function get_msg()
	{
		return "RATE_LIMIT_EXCEEDED";
	}
}