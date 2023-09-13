<?php

namespace ybc\octavia\Utils;


use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\Loader\FilesystemLoader;

class Templater
{
	private $twig;

	public function __construct($template_dir = null)
	{
		$loader = $template_dir ? new FilesystemLoader($template_dir) : new ArrayLoader();
		$this->twig = new Environment($loader);
	}

	public function render_from_string($string, $params)
	{
		$template = $this->twig->createTemplate($string);
		return $template->render($params);
	}

	public function render_from_file($file, $params)
	{
		return $this->twig->render($file, $params);
	}
}
