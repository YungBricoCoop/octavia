<?php

namespace Vendor\YbcFramework;

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
