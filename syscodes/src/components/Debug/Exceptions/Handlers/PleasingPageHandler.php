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
 * @copyright   Copyright (c) 2019 - 2024 Alexander Campo <jalexcam@gmail.com>
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
 */
class PleasingPageHandler extends Handler
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
		"netbeans" => "netbeans://open/?f=%file:%line",
		"idea"     => "idea://open?file=%file&line=%line",
		"sublime"  => "subl://open?url=file://%file&line=%line",
		"phpstorm" => "phpstorm://open?file://%file&line=%line",
		"textmate" => "txmt://open?url=file://%file&line=%line",
		"emacs"    => "emacs://open?url=file://%file&line=%line",
        "macvim"   => "mvim://open/?url=file://%file&line=%line",
		"atom"     => "atom://core/open/file?filename=%file&line=%line",
	];
	
	/**
	 * The page title main of handler.
	 * 
	 * @var string $pageTitle
	 */
	protected $pageTitle = 'Lenevor Debug! There was an error';
	
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
	 * Gets the theme default.
	 * 
	 * @var string $theme
	 */
	protected $theme;
	
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
		$this->searchPaths[] = dirname(dirname(__DIR__)).DIRECTORY_SEPARATOR.'Resources';
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
		$style      = file_get_contents($this->getResource('compiled/css/debug.base.css'));
		$jscript    = file_get_contents($this->getResource('compiled/js/debug.base.js'));
		$servers    = array_merge($this->getDefaultServers(), $this->tables);
		$routing    = array_merge($this->getDefaultRouting(), $this->tables);
		$databases  = array_merge($this->getDefaultDatabase(), $this->tables);
		$context    = array_merge($this->getDefaultContext(), $this->tables);
		
		return [ 
			'class' => explode('\\', $supervisor->getExceptionName()),
			'stylesheet' => preg_replace('#[\r\n\t ]+#', ' ', $style),
			'javascript' => preg_replace('#[\r\n\t ]+#', ' ', $jscript),
			'header' => $this->getResource('views/partials/updown/header.php'),
			'footer' => $this->getResource('views/partials/updown/footer.php'),
			'info_exception' => $this->getResource('views/partials/info/info_exception.php'),
			'section_stack_exception' => $this->getResource('views/partials/section_stack_exception.php'),
			'section_frame' => $this->getResource('views/partials/section_frame.php'),
			'frame_description' => $this->getResource('views/partials/frames/frame_description.php'),
			'frame_list' => $this->getResource('views/partials/frames/frame_list.php'),
			'section_code' => $this->getResource('views/partials/section_code.php'),
			'code_source' => $this->getResource('views/partials/codes/code_source.php'),
			'request_info' => $this->getResource('views/partials/request_info.php'),
			'navigation' => $this->getResource('views/components/navBar.php'),
			'settings' => $this->getResource('views/components/settingsDropdown.php'),
			'section_detail_context' => $this->getResource('views/partials/details/section_detail_context.php'),
			'plain_exception' => Formatter::formatExceptionAsPlainText($this->getSupervisor()),
			'handler' => $this,
			'handlers' => $this->getDebug()->getHandlers(),
			'debug' => $this->getDebug(),
			'code' => $this->getExceptionCode(),
			'message' => $supervisor->getExceptionMessage(),
			'frames' => $this->getExceptionFrames(),
			'servers' => $this->getProcessTables($servers),
			'routes' => $this->getProcessTables($routing),
			'databases' => $this->getProcessTables($databases),
			'contexts' => $this->getProcessTables($context),
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
	 * Returns the default servers.
	 * 
	 * @return \Syscodes\Components\Contracts\Debug\Table[]
	 */
	protected function getDefaultServers()
	{
		$server = [
			'host' => $_SERVER['HTTP_HOST'], 
			'user-agent' => $_SERVER['HTTP_USER_AGENT'], 
			'accept' => $_SERVER['HTTP_ACCEPT'], 
			'accept-language' => $_SERVER['HTTP_ACCEPT_LANGUAGE'], 
			'accept-encoding' => $_SERVER['HTTP_ACCEPT_ENCODING'],
			'connection' => $_SERVER['HTTP_CONNECTION'],
			'upgrade-insecure-requests' => $_SERVER['HTTP_UPGRADE_INSECURE_REQUESTS'], 
			'sec-fetch-dest' => $_SERVER['HTTP_SEC_FETCH_DEST'],
			'sec-fetch-mode' => $_SERVER['HTTP_SEC_FETCH_MODE'],
			'sec-fetch-site' => $_SERVER['HTTP_SEC_FETCH_SITE'],
			'sec-fetch-user' => $_SERVER['HTTP_SEC_FETCH_USER'],
		];

		return [new ArrayTable($server)];
	}

	/**
	 * Returns the default routing.
	 * 
	 * @return \Syscodes\Components\Contracts\Debug\Table[]
	 */
	protected function getDefaultRouting()
	{
		$action = 'Closure' ?? app('request')->route()->parseControllerCallback()[0];

		$index = match (true) {
			array_key_exists('web', app('router')->getMiddlewareGroups()) => 0,
			array_key_exists('api', app('router')->getMiddlewareGroups()) => 1,
		};

		$routing = [
			'Controller' => $action,
			'Middleware' => array_keys(app('router')->getMiddlewareGroups())[$index],
		];

		return [new ArrayTable($routing)];
	}

	/**
	 * Returns the default database.
	 * 
	 * @return \Syscodes\Components\Contracts\Debug\Table[]
	 */
	protected function getDefaultDatabase()
	{
		$query = [
			'Sql' => null,
			'Time' => null,
			'Connection name' => null,
		];

		return [new ArrayTable($query)];
	}

	/**
	 * Returns the default context data.
	 * 
	 * @return \Syscodes\Components\Contracts\Debug\Table[]
	 */
	protected function getDefaultContext()
	{
		$context = [
			'Php Version' => PHP_VERSION,
			'Lenevor Version' => app()->version(),
			'Lenevor Locale' => config('app.locale'),
			'App Debug' => (1 == env('APP_DEBUG') ? 'true' : 'false'),
			'App Env' => env('APP_ENV'),
		];

		return [new ArrayTable($context)];
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
	 * Processes an array of tables making sure everything is all right.
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
				$data = (array) $table->getData();

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
		$templatePath = $this->getResource('views/debug.layout.php');

		$vars = $this->collectionVars();
		
		if (empty($vars['message'])) $vars['message'] = __('exception.noMessage');
		
		$this->template->setVariables($vars);
		$this->template->render($templatePath);
		
		return Handler::QUIT;
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
	 * Get the theme default.
	 * 
	 * @return string
	 */
	public function getTheme(): string
	{
		return $this->theme = config('gdebug.theme');
	}
	
	/**
	 * Sets the brand of project.
	 * 
	 * @param  string  $brand
	 * 
	 * @return void
	 */
	public function setBrand(string $brand): void
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
	public function setPageTitle(string $title): void
	{
		$this->pageTitle = (string) $title;
	}

	/**
	 * Set the theme manually.
	 * 
	 * @param  string  $theme
	 * 
	 * @return void
	 */
	public function setTheme(string $theme): void
	{
		$this->theme = (string) $theme;
	}
}