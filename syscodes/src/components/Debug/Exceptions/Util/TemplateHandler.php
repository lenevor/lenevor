<?php 

/**
 * Lenevor Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file license.md.
 * It is also available through the world-wide-web at this URL:
 * https://lenevor.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@Lenevor.com so we can send you a copy immediately.
 *
 * @package     Lenevor
 * @subpackage  Base
 * @link        https://lenevor.com
 * @copyright   Copyright (c) 2019 - 2023 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Debug\Util;

use Exception;
use Syscodes\Components\Stopwatch\Benchmark;

/**
 * Exposes useful tools for working with/in templates.
 */
class TemplateHandler
{
	/**
	 * Benchmark instance.
	 * 
	 * @var string $benchmark
	 */
	protected $benchmark;

	/**
	 * Nesting level of the output buffering mechanism.
	 *
	 * @var string $obLevel
	 */
	public $obLevel;
	
	/**
	 * The functions of system what control errors and exceptions.
	 * 
	 * @var string|object $system
	 */
	protected $system;
	
	/**
	 * An array of variables to be passed to all templates.
	 * 
	 * @var array $variables
	 */
	protected $variables = [];

	/**
	 * Constructor. The TemplateHandler class instance.
	 * 
	 * @return void
	 */
	public function __construct()
	{
		$this->system    = new System;
		$this->benchmark = new Benchmark;
		$this->obLevel   = $this->system->getOutputBufferLevel();
	}

	/**
	 * Clean Path: This makes nicer looking paths for the error output.
	 *
	 * @param  string  $file
	 *
	 * @return string
	 */
	public function cleanPath($file): string
	{
		if (strpos($file, appPath().DIRECTORY_SEPARATOR) === 0) {
			$file = appPath().DIRECTORY_SEPARATOR.substr($file, strlen(appPath().DIRECTORY_SEPARATOR));
		} elseif (strpos($file, basePath().DIRECTORY_SEPARATOR) === 0) {
			$file = basePath().DIRECTORY_SEPARATOR.substr($file, strlen(basePath().DIRECTORY_SEPARATOR));
		} elseif (strpos($file, configPath().DIRECTORY_SEPARATOR) === 0) {
			$file = configPath().DIRECTORY_SEPARATOR.substr($file, strlen(configPath().DIRECTORY_SEPARATOR));
		} elseif (strpos($file, resourcePath().DIRECTORY_SEPARATOR) === 0) {
			$file = resourcePath().DIRECTORY_SEPARATOR.substr($file, strlen(resourcePath().DIRECTORY_SEPARATOR));
		}

		return $file;
	}

	/**
	 * Display memory usage in real-world units. Intended for use
	 * with memory_get_usage, etc.
	 *
	 * @param  int  $bytes
	 *
	 * @return string
	 */
	public function displayMemory(int $bytes): string
	{
		if ($bytes < 1024) {
			return $bytes.'B';
		} else if ($bytes < 1048576) {
			return round($bytes/1024, 2).'KB';
		}

		return round($bytes/1048576, 2).'MB';
	}

	/**
	 * Escapes a string for output in an HTML document.
	 * 
	 * @param  string  $text
	 * 
	 * @return string
	 */
	public function escape($text): string
	{
		$flags = ENT_QUOTES;
		
		// HHVM has all constants defined, but only ENT_IGNORE
		// works at the moment
		if (defined("ENT_SUBSTITUTE") && ! defined("HHVM_VERSION")) {
			$flags |= ENT_SUBSTITUTE;
		} else {
			$flags |= ENT_IGNORE;
		}
		
		$text = str_replace(chr(9), '    ', $text);
		
		return htmlspecialchars($text, $flags, "UTF-8");
	}

	/**
	 * Returns all variables for this helper.
	 * 
	 * @return array
	 */
	public function getVariables(): array
	{
		return $this->variables;
	}

	/**
	 * Creates a syntax-highlighted version of a PHP file.
	 *
	 * @param  string  $file
	 * @param  int     $lineNumber
	 * @param  int     $lines
	 *
	 * @return bool|string
	 * 
	 * @throws \Exception
	 */
	public function highlightFile($file, $lineNumber, $lines = 15)
	{
		if (empty ($file) || ! is_readable($file)) {
			return false;
		}

		// Set our highlight colors:
		if (function_exists('ini_set')) {
			ini_set('highlight.bg', '#000');
			ini_set('highlight.comment', '#959595');
			ini_set('highlight.default', '#818CF8');
			ini_set('highlight.html', '#06B');
			ini_set('highlight.keyword', '#F47E3A');
			ini_set('highlight.string', '#57C60D');
		}

		try {
			$origin = file_get_contents($file);
		} catch (Exception $e) {
			return false;
		}

		$origin  = str_replace(["\r\n", "\r"], "\n", $origin);
		$origin  = explode("\n", highlight_string($origin , true));
		$origin  = str_replace('<br />', "\n", $origin [1]);

		$origin  = explode("\n", str_replace("\r\n", "\n", $origin));

		// Get just the part to show
		$start = $lineNumber - (int) round($lines / 2);
		$start = $start < 0 ? 0 : $start;

		// Get just the lines we need to display, while keeping line numbers...
		$origin  = array_splice($origin, $start, $lines, true);

		// Used to format the line number in the source
		$format = '% '.strlen($start + $lines).'d';

		$out = '';
		// Because the highlighting may have an uneven number
		// of open and close span tags on one line, we need
		// to ensure we can close them all to get the lines
		// showing correctly.
		$spans = 1;

		foreach ($origin as $n => $row) {
			$spans += substr_count($row, '<span') - substr_count($row, '</span');
			$row = str_replace(["\r", "\n"], ['', ''], $row);

			if (($n+$start+1) == $lineNumber) {
				preg_match_all('#<[^>]+>#', $row, $tags);
				$out .= sprintf("<span class='line highlight'><span class='number'>{$format}</span> %s\n</span>%s",
						$n + $start + 1,
						strip_tags($row),
						implode('', $tags[0])
				);
			} else {
				$out .= sprintf('<span class="number">'.$format.'</span> %s <span class="line">', $n + $start + 1, $row) ."\n";
			}
		}

		$out .= str_repeat('</span>', $spans);

		return '<pre class="code-blocks"><code>'.$out.'</code></pre>';
	}

	/**
	 * Sets the variables to be passed to all templates rendered 
	 * by this template helper.
	 * 
	 * @param  array  $variables
	 * 
	 * @return void
	 */
	public function setVariables(array $variables): void
	{
		$this->variables = $variables;
	}

	/**
	 * Convert a string to a slug version of itself.
	 * 
	 * @param  string  $original
	 * 
	 * @return string
	 */
	public function slug($original): string
	{
		$slug = str_replace(" ", "-", $original);
		$slug = preg_replace('/[^\w\d\-\_]/i',' ', $slug);

		return strtolower($slug);
	}

	/**
	 * Given an exception and status code will display the error to the client.
	 *
	 * @param  string  $template
	 * 
	 * @return void
	 */
	public function render($template): void
	{
		$vars = $this->getVariables();

		$vars['template'] = $this;
		
		if ($this->system->getOutputBufferLevel() > $this->obLevel + 1) {
			@$this->system->endOutputBuffering();
		}

		// Instantiate the error view and prepare the vars
		call_user_func(function () {
			extract(func_get_arg(1));
			include func_get_arg(0);
		}, $template, $vars);
	}
}