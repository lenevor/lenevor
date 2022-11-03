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
 * @copyright   Copyright (c) 2019 - 2022 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Debug\Handlers;

use Exception;
use Traversable;
use ErrorException;
use RuntimeException;
use InvalidArgumentException;
use UnexpectedValueException;
use Syscodes\Components\Debug\Util\Misc;
use Syscodes\Components\Contracts\Debug\Table;
use Syscodes\Components\Debug\Util\ArrayTable;
use Syscodes\Components\Debug\Util\TemplateHandler;
use Syscodes\Components\Debug\FrameHandler\Formatter;  

/**
 * Generates exceptions in mode of graphic interface.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class PleasingPageHandler extends MainHandler
{
	/**
	 * The brand main of handler.
	 * 
	 * @var string $brand
	 */
	protected $brand = 'Lenevor Debug';

	/**
	 * A string identifier for a known IDE/text editor, or a closure
	 * that resolves a string that can be used to open a given file
	 * in an editor.
	 * 
	 * @var mixed $editor
	 */
	protected $editor;

	/**
	 * A list of known editor strings.
	 * 
	 * @var array $editors
	 */
	protected $editors = [
		"vscode"   => "vscode://file/%file:%line",
		"sublime"  => "subl://open?url=file://%file&line=%line",
		"phpstorm" => "phpstorm://open?file://%file&line=%line",
		"textmate" => "txmt://open?url=file://%file&line=%line",
		"atom"     => "atom://core/open/file?filename=%file&line=%line",
	];
	
	/**
	 * The page title main of handler.
	 * 
	 * @var string $pageTitle
	 */
	protected $pageTitle = 'Lenevor Debug! There was an error.';
	
	/**
	 * Fast lookup cache for known resource locations.
	 * 
	 * @var array $resourceCache
	 */
	protected $resourceCache = [];
	
	/**
	 * The path to the directory containing the html error template directories.
	 * 
	 * @var array $searchPaths
	 */
	protected $searchPaths = [];

	/**
	 * Gets the table of data.
	 * 
	 * @var array $tables
	 */
	protected $tables = [];
	
	/**
	 * The template handler system.
	 * 
	 * @var string|object $template
	 */
	protected $template;	
	
	/**
	 * Constructor. The PleasingPageHandler class.
	 * 
	 * @return void
	 */
	public function __construct()
	{
		$this->template      = new TemplateHandler;
		$this->searchPaths[] = dirname(__DIR__).DIRECTORY_SEPARATOR.'Resources';
	}

	/**
	 * Adds an editor resolver, identified by a string name, and that may be a 
	 * string path, or a callable resolver.
	 * 
	 * @param  string            $identifier
	 * @param  string|\Callable  $resolver
	 * 
	 * @return void
	 */
	public function addEditor($identifier, $resolver): void
	{
		$this->editors[$identifier] = $resolver;
	}

	/**
	 * Adds an entry to the list of tables displayed in the template.
	 * The expected data is a simple associative array. Any nested arrays
	 * will be flattened with print_r.
	 * 
	 * @param  \Syscodes\Components\Contracts\Debug\Table  $table
	 * 
	 * @return void
	 */
	public function addTables(Table $table): void
	{
		$this->tables[] = $table;
	}
	
	/**
	 * Gathers the variables that will be made available to the view.
	 * 
	 * @return  array
	 */
	protected function collectionVars(): array
	{
		$supervisor = $this->getSupervisor();
		$style      = file_get_contents($this->getResource('css/debug.base.css'));
		$jscript    = file_get_contents($this->getResource('js/debug.base.js'));
		$tables     = array_merge($this->getDefaultTables(), $this->tables);
		
		return [ 
			'class'             => explode('\\', $supervisor->getExceptionName()),
			'stylesheet'        => preg_replace('#[\r\n\t ]+#', ' ', $style),
			'javascript'        => preg_replace('#[\r\n\t ]+#', ' ', $jscript),
			'header'            => $this->getResource('views/header.php'),
			'sidebar'           => $this->getResource('views/sidebar.php'),
			'frame_main'        => $this->getResource('views/frame_main.php'),
			'frame_description' => $this->getResource('views/frame_description.php'),
			'frame_list'        => $this->getResource('views/frame_list.php'),
			'details_panel'     => $this->getResource('views/details_panel.php'),
			'code_source'       => $this->getResource('views/code_source.php'),
			'details_content'   => $this->getResource('views/details_content.php'),
			'footer'            => $this->getResource('views/footer.php'),
			'plain_exception'   => Formatter::formatExceptionAsPlainText($this->getSupervisor()),
			'handler'           => $this,
			'handlers'          => $this->getDebug()->getHandlers(),
			'debug'             => $this->getDebug(),
			'code'              => $this->getExceptionCode(),
			'message'           => $supervisor->getExceptionMessage(),
			'frames'            => $this->getExceptionFrames(),
			'tables'            => $this->getProcessTables($tables),
		];
	}
	
	/**
	 * The way in which the data sender (usually the server) can tell the recipient
	 * (the browser, in general) what type of data is being sent in this case, html format tagged.
	 * 
	 * @return string
	 */
	public function contentType(): string
	{
		return 'text/html;charset=UTF-8';
	}

	/**
	 * Gets the brand of project.
	 * 
	 * @return string
	 */
	public function getBrand(): string
	{
		return $this->brand;
	}

	/**
	 * Returns the default tables.
	 * 
	 * @return \Syscodes\Components\Contracts\Debug\Table[]
	 */
	protected function getDefaultTables()
	{
		return [
			new ArrayTable('GET Data', $_GET),
			new ArrayTable('POST Data', $_POST),
			new ArrayTable('Files', $_FILES),
			new ArrayTable('Cookie', $_COOKIE),
			new ArrayTable('Session', isset($_SESSION) ? $_SESSION : []),
			new ArrayTable('Server/Request Data', $_SERVER),
			new ArrayTable(__('exception.environmentVars'), $_ENV),
		];
	}

	/**
	 * Get the code of the exception that is currently being handled.
	 * 
	 * @return string
	 */
	protected function getExceptionCode()
	{
		$exception = $this->getException();
		$code      = $exception->getCode();

		if ($exception instanceof ErrorException) {
			$code = Misc::translateErrorCode($exception->getSeverity());
		}

		return (string) $code;
	}

	/**
	 * Get the stack trace frames of the exception that is currently being handled.
	 * 
	 * @return \Syscodes\Components\Debug\Engine\Supervisor;
	 */
	protected function getExceptionFrames()
	{
		$frames = $this->getSupervisor()->getFrames();
		
		return $frames;
	}
	
	/**
	 * Gets the page title web.
	 * 
	 * @return string
	 */
	public function getPageTitle(): string
	{
		return $this->pageTitle;
	}

	/**
	 * Processes an array of tables making sure everything is allright.
	 * 
	 * @param  \Syscodes\Components\Contracts\Debug\Table[]  $tables
	 * 
	 * @return array
	 */
	protected function getProcessTables(array $tables): array
	{
		$processTables = [];

		foreach ($tables as $table) {
			if ( ! $table instanceof Table) {
				continue;
			}

			$label = $table->getLabel();

			try {
				$data = $table->getData();

				if ( ! (is_array($data) || $data instanceof Traversable)) {
					$data = [];
				}
			} catch (Exception $e) {
				$data = [];
			}

			$processTables[$label] = $data;
		}

		return $processTables;
	}

	/**
	 * Finds a resource, by its relative path, in all available search paths.
	 *
	 * @param  string  $resource
	 * 
	 * @return string
	 * 
	 * @throws \RuntimeException
	 */
	protected function getResource($resource)
	{
		if (isset($this->resourceCache[$resource])) {
			return $this->resourceCache[$resource];
		}

		foreach ($this->searchPaths as $path) {
			$fullPath = $path.DIRECTORY_SEPARATOR.$resource;

			if (is_file($fullPath)) {
				// Cache:
				$this->resourceCache[$resource] = $fullPath;

				return $fullPath;
			}
		}

		throw new RuntimeException( 
				"Could not find resource '{$resource}' in any resource paths.". 
				"(searched: ".join(", ", $this->searchPaths).")");
	}
	
	/**
	 * Given an exception and status code will display the error to the client.
	 * 
	 * @return int|null
	 */
	public function handle()
	{	
		$templatePath = $this->getResource('debug.layout.php');

		$vars = $this->collectionVars();
		
		if (empty($vars['message'])) $vars['message'] = __('exception.noMessage');
		
		$this->template->setVariables($vars);
		$this->template->render($templatePath);
		
		return MainHandler::QUIT;
	}

	/**
	 * Set the editor to use to open referenced files, by a string identifier or callable
	 * that will be executed for every file reference. Should return a string.
	 * 
	 * @example  $debug->setEditor(function($file, $line) { return "file:///{$file}"; });
	 * @example  $debug->setEditor('vscode');
	 * 
	 * @param  string  $editor
	 * 
	 * @return void
	 * 
	 * @throws \InvalidArgumentException
	 */
	public function setEditor($editor)
	{
		if ( ! is_callable($editor) && ! isset($this->editors[$editor])) {
			throw new InvalidArgumentException("Unknown editor identifier: [{$editor}]. Known editors: " .
				implode(', ', array_keys($this->editors))
			);
		}

		$this->editor = $editor;
	}

	/**
	 * Given a string file path, and an integer file line,
	 * executes the editor resolver and returns.
	 * 
	 * @param  string  $file
	 * @param  int	   $line
	 * 
	 * @return string|bool
	 * 
	 * @throws \UnexpectedValueException
	 */
	public function getEditorAtHref($file, $line)
	{
		$editor = $this->getEditor($file, $line);

		if (empty($editor))	{
			return false;
		}

		if ( ! isset($editor['url']) || ! is_string($editor['url'])) {
			throw new UnexpectedValueException(__METHOD__.'should always resolve to a string or a valid editor array');
		}

		$editor['url'] = str_replace("%file", rawurldecode($file), $editor['url']);
		$editor['url'] = str_replace("%line", rawurldecode($line), $editor['url']);

		return $editor['url'];
	}

	/**
	 * The editor must be a valid callable function/closure.
	 * 
	 * @param  string  $file
	 * @param  int	   $line
	 * 
	 * @return array
	 */
	protected function getEditor($file, $line): array
	{
		if ( ! $this->editor || ( ! is_string($this->editor) && ! is_callable($this->editor))) {
			return [];
		}

		if (is_string($this->editor) && isset($this->editors[$this->editor]) && ! is_callable($this->editors[$this->editor])) {
			return ['url' => $this->editors[$this->editor]];
		}

		if (is_callable($this->editor) || (isset($this->editors[$this->editor]) && is_callable($this->editors[$this->editor]))) {
			if (is_callable($this->editor)) {
				$callback = call_user_func($this->editor, $file, $line);
			} else {
				$callback = call_user_func($this->editors[$this->editor], $file, $line);
			}

			if (empty($callback)) {
				return [];
			}

			if (is_string($callback)) {
				return ['url' => $callback];
			}
			
			return ['url' => isset($callback['url']) ? $callback['url'] : $callback];
		}
		
		return [];
	}

	/**
	 * Registered the editor.
	 * 
	 * @return string
	 */
	public function getEditorcode(): string
	{
		return $this->editor;
	}
	
	/**
	 * Sets the brand of project.
	 * 
	 * @param  string  $brand
	 * 
	 * @return void
	 */
	public function setBrand($brand): void
	{
		$this->brand = (string) $brand;
	}
	
	/**
	 * Sets the page title web.
	 * 
	 * @param  string  $title
	 * 
	 * @return void
	 */
	public function setPageTitle($title): void
	{
		$this->pageTitle = (string) $title;
	}
}