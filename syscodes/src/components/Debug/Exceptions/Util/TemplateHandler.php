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
 * @author      Javier Alexander Campo M. <jalexcam@gmail.com>
 * @link        https://lenevor.com 
 * @copyright   Copyright (c) 2019-2021 Lenevor Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.1.0
 */

namespace Syscodes\Debug\Util;

use Syscodes\Debug\Benchmark;
use Syscodes\Debug\FrameHandler\Frame;

/**
 * Exposes useful tools for working with/in templates.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
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
	 * @var string $system
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
	public function cleanPath($file)
	{
		if (strpos($file, APP_PATH) === 0)
		{
			$file = 'APP_PATH'.DIRECTORY_SEPARATOR.substr($file, strlen(APP_PATH));
		}
		elseif (strpos($file, SYS_PATH) === 0)
		{
			$file = 'SYS_PATH'.DIRECTORY_SEPARATOR.substr($file, strlen(SYS_PATH));
		}
		elseif (strpos($file, CON_PATH) === 0)
		{
			$file = 'CON_PATH'.DIRECTORY_SEPARATOR.substr($file, strlen(CON_PATH));
		}
		elseif (strpos($file, RES_PATH) === 0)
		{
			$file = 'RES_PATH'.DIRECTORY_SEPARATOR.substr($file, strlen(RES_PATH));
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
	public function displayMemory(int $bytes)
	{
		if ($bytes < 1024)
		{
			return $bytes.'B';
		}
		else if ($bytes < 1048576)
		{
			return round($bytes/1024, 2).'KB';
		}

		return round($bytes/1048576, 2).'MB';
	}
	
	/**
	 * Format the given value into a human readable string.
	 * 
	 * @param  mixed  $value
	 * 
	 * @return string
	 */
	public function dump($value)
	{
		return htmlspecialchars(print_r($value, true));
	}
	
	/**
	 * Format the args of the given Frame as a human readable html string.
	 * 
	 * @param  \Syscodes\Debug\FrameHandler\Frame  $frame
	 * 
	 * @return string  The rendered html
	 */
	public function dumpArgs(Frame $frame)
	{
		$html      = '';
		$numFrames = count($frame->getArgs());
		
		if ($numFrames > 0)
		{
			$html = '<ol class="linenums">';
			
			foreach ($frame->getArgs() as $j => $frameArg)
			{
				$html .= '<li>'.$this->dump($frameArg).'</li>';
			}
			
			$html .= '</ol>';
		}
		
		return $html;
	}

	/**
	 * Escapes a string for output in an HTML document.
	 * 
	 * @param  string  $text
	 * 
	 * @return string
	 */
	public function escape($text)
	{
		$flags = ENT_QUOTES;
		
		// HHVM has all constants defined, but only ENT_IGNORE
		// works at the moment
		if (defined("ENT_SUBSTITUTE") && ! defined("HHVM_VERSION"))
		{
			$flags |= ENT_SUBSTITUTE;
		}
		else
		{
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
	public function getVariables()
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
		if (empty ($file) || ! is_readable($file))
		{
			return false;
		}

		// Set our highlight colors:
		if (function_exists('ini_set'))
		{
			ini_set('highlight.comment', '#C5C5C5');
			ini_set('highlight.default', '#5399BA');
			ini_set('highlight.html', '#06B');
			ini_set('highlight.keyword', '#7081A5;');
			ini_set('highlight.string', '#d8A134');
		}

		try
		{
			$origin = file_get_contents($file);
		}
		catch (Exception $e)
		{
			return false;
		}

		$origin  = str_replace(["\r\n", "\r"], "\n", $origin);
		$origin  = explode("\n", highlight_string($origin , true));
		$origin  = str_replace('<br />', "\n", $origin [1]);

		$origin  = explode("\n", str_replace("\r\n", "\n", $origin));

		// Get just the part to show
		$start = $lineNumber - (int)round($lines / 2);
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

		foreach ($origin as $n => $row)
		{
			$spans += substr_count($row, '<span') - substr_count($row, '</span');
			$row = str_replace(["\r", "\n"], ['', ''], $row);

			if (($n+$start+1) == $lineNumber)
			{
				preg_match_all('#<[^>]+>#', $row, $tags);
				$out .= sprintf("<span class='line highlight'><span class='number'>{$format}</span> %s\n</span>%s",
						$n + $start + 1,
						strip_tags($row),
						implode('', $tags[0])
				);
			}
			else
			{
				$out .= sprintf('<span class="number">'.$format.'</span> %s <span class="line">', $n + $start +1, $row) ."\n";
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
	public function setVariables(array $variables)
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
	public function slug($original)
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
	public function render($template)
	{
		$vars = $this->getVariables();

		$vars['template'] = $this;
		
		if ($this->system->getOutputBufferLevel() > $this->obLevel + 1)
		{
			@$this->system->endOutputBuffering();
		}

		// Instantiate the error view and prepare the vars
		call_user_func(function () {
			extract(func_get_arg(1));
			include func_get_arg(0);
		}, $template, $vars);
	}
}