<?php

class CustomException extends Exception
{
	protected $statusCode = 200;
	protected $detail = null;

	public function __construct($detail = null, $message = null, $code = 0, Exception $previous = null)
	{
		$this->detail = $detail;
		if (is_null($message)) {
			$message = $this->getMsg();
		}
		parent::__construct($message, $code, $previous);
	}

	public function getStatusCode()
	{
		return $this->statusCode;
	}

	public function getDetail()
	{
		return $this->detail;
	}

	protected function getMsg()
	{
		return "CUSTOM_EXCEPTION";
	}
}

class UnauthorizedException extends CustomException
{
	protected $statusCode = 401;
	protected function getMsg()
	{
		return "UNAUTHORIZED";
	}
}

class ForbiddenException extends CustomException
{
	protected $statusCode = 403;
	protected function getMsg()
	{
		return "FORBIDDEN";
	}
}

class NotFoundException extends CustomException
{
	protected $statusCode = 404;
	protected function getMsg()
	{
		return "NOT_FOUND";
	}
}

class MethodNotAllowedException extends CustomException
{
	protected $statusCode = 405;
	protected function getMsg()
	{
		return "METHOD_NOT_ALLOWED";
	}
}

class ConflictException extends CustomException
{
	protected $statusCode = 409;
	protected function getMsg()
	{
		return "CONFLICT";
	}
}

class InternalServerErrorException extends CustomException
{
	protected $statusCode = 500;
	protected function getMsg()
	{
		return "INTERNAL_SERVERERROR";
	}
}

class MissingQueryParameterException extends CustomException
{
	protected $statusCode = 400;
	protected function getMsg()
	{
		return "MISSING_QUERY_PARAMETER";
	}
}

class WrongQueryParameterTypeException extends CustomException
{
	protected $statusCode = 400;
	protected function getMsg()
	{
		return "WRONG_QUERY_PARAMETER_TYPE";
	}
}

class MissingBodyParameterException extends CustomException
{
	protected $statusCode = 400;
	protected function getMsg()
	{
		return "MISSING BODY PARAMETER";
	}
}

class MissingObjectPropertyException extends CustomException
{
	protected $statusCode = 400;
	protected function getMsg()
	{
		return "MISSING_OBJECT_PROPERTY";
	}
}

class WrongObjectPropertyTypeException extends CustomException
{
	protected $statusCode = 400;
	protected function getMsg()
	{
		return "WRONG_OBJECT_PROPERTY_TYPE";
	}
}
