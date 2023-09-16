<?php

namespace ybc\octavia;

class Response
{
	public $data;
	public $headers = [];
	public $status_code;

	public function __construct($data = '', $status_code = 200)
	{
		$this->data = $data;
		$this->status_code = $status_code;
	}

	/**
	 * Send the response to the client
	 * The data type is determined by the middleware
	 * @return void
	 */
	public function send()
	{
		foreach ($this->headers as $header => $value) {
			header("$header: $value");
		}
		http_response_code($this->status_code);

		//INFO: No need to encode here since the middleware are already doing it
		echo $this->data;
		exit;
	}
}
