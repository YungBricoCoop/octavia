<?php

namespace ybc\octavia\Utils;


use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\Loader\FilesystemLoader;

class Templater
{
	private $twig;

	public function __construct(string $template_dir = null)
	{
		$loader = $template_dir ? new FilesystemLoader($template_dir) : new ArrayLoader();
		$this->twig = new Environment($loader);
	}

	/**
	 * Render a template from a string
	 * @param string $string
	 * @param array $params
	 * @example $templater->render_from_string("{{ name }} kon {{kru}}", ["name" => "Octavia", "kru" => "Skaikru"]);
	 * @return string
	 */
	public function render_from_string(string $string, array $params)
	{
		$template = $this->twig->createTemplate($string);
		return $template->render($params);
	}

	/**
	 * Render a template from a file
	 * The file must be in the template directory
	 * @param string $file
	 * @param array $params
	 * @example $templater->render_from_file("index.html", ["name" => "Octavia", "kru" => "Skaikru"]);
	 * @return string
	 */
	public function render_from_file(string $file, array $params)
	{
		return $this->twig->render($file, $params);
	}
}
