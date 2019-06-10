<?php 

namespace Syscode\Debug\Handlers;

use Throwable;
use Traversable;
use ErrorException;
use RuntimeException;
use Syscode\Debug\Util\{ 
	ArrayTable, 
	Misc, 
	TemplateHandler 
};
use Syscode\Contracts\Debug\Table;

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
 * @copyright   Copyright (c) 2019 Lenevor Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.1.0
 */
class PleasingPageHandler extends MainHandler
{
	/**
	 * The brand main of handler.
	 * 
	 * @var string $brand
	 */
	public $brand = 'Lenevor Debug';
	
	/**
	 * The page title main of handler.
	 * 
	 * @var string $pageTitle
	 */
	public $pageTitle = 'Lenevor Debug! There was an error.';
	
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
	 * @var string $template
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
	 * Adds an entry to the list of tables displayed in the template.
	 * The expected data is a simple associative array. Any nested arrays
     * will be flattened with print_r.
	 * 
	 * @param  \Syscode\Contracts\Debug\Table  $table
	 * 
	 * @return array
	 */
	public function addTables(Table $table)
	{
		$this->tables[] = $table;
	}
	
	/**
	 * Gathers the variables that will be made available to the view.
	 * 
	 * @param  \Throwable  $exception
	 * 
	 * @return  array
	 */
	protected function collectionVars(Throwable $exception)
	{
		$style   = file_get_contents($this->getResource('css/debug.base.css'));
		$jscript = file_get_contents($this->getResource('js/debug.base.js'));
		$tables  = array_merge($this->getDefaultTables(), $this->tables);
		
		return [
			'brand'             => $this->getBrand(),
			'class'             => explode('\\', getClass($exception)),
			'title'             => $this->getPageTitle(),
			'stylesheet'        => preg_replace('#[\r\n\t ]+#', ' ', $style),
			'javascript'        => preg_replace('#[\r\n\t ]+#', ' ', $jscript),
			'header'            => $this->getResource('views/header.php'),
			'sidebar'           => $this->getResource('views/sidebar.php'),
			'frame_description' => $this->getResource('views/frame_description.php'),
			'frame_list'        => $this->getResource('views/frame_list.php'),
			'details_panel'     => $this->getResource('views/details_panel.php'),
			'code_source'       => $this->getResource('views/code_source.php'),
			'details_content'   => $this->getResource('views/details_content.php'),
			'footer'            => $this->getResource('views/footer.php'),
			'handlers'          => $this->getDebug()->getHandlers(),
			'debug'             => $this->getDebug(),
			'code'              => $this->getExceptionCode(),
			'file'              => $exception->getFile(),
			'line'              => $exception->getLine(),
			'message'           => $exception->getMessage(),
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
	public function contentType()
	{
		return 'text/html;charset=UTF-8';
	}
	
	/**
	 * Gets the brand of project.
	 * 
	 * @return string
	 */
	protected function getBrand()
	{
		return $this->brand;
	}

	/**
	 * Returns the default tables.
	 * 
	 * @return \Syscode\Contracts\Debug\Table[]
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

		if ($exception instanceof ErrorException)
		{
			$code = Misc::translateErrorCode($exception->getSeverity());
		}

		return (string) $code;
	}

	/**
     * Get the stack trace frames of the exception that is currently being handled.
     *
     * @return \Syscode\Debug\Engine\Supervisor;
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
	protected function getPageTitle()
	{
		return $this->pageTitle;
	}

	/**
	 * Processes an array of tables making sure everything is allright.
	 * 
	 * @param  \Syscode\Contracts\Debug\Table[]  $tables
	 * 
	 * @return array
	 */
	protected function getProcessTables(array $tables)
	{
		$processTables = [];

		foreach ($tables as $table)
		{
			if ( ! $table instanceof Table)
			{
				continue;
			}

			$label = $table->getLabel();

			try
			{
				$data = $table->getData();

				if ( ! (is_array($data) || $data instanceof Traversable))
				{
					$data = [];
				}
			}
			catch (Exception $e)
			{
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
		if (isset($this->resourceCache[$resource]))
		{
			return $this->resourceCache[$resource];
		}

		foreach ($this->searchPaths as $path)
		{
			$fullPath = $path.DIRECTORY_SEPARATOR.$resource;

			if (is_file($fullPath))
			{
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

		$vars = $this->collectionVars($this->getException());
		
		if (empty($vars['message'])) $vars['message'] = __('exception.noMessage');
		
		$this->template->setVariables($vars);
		$this->template->render($templatePath);
		
		return MainHandler::QUIT;
	}
	
	/**
	 * Sets the brand of project.
	 * 
	 * @param  string  $brand
	 * 
	 * @return void
	 */
	public function setBrand($brand)
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
	public function setPageTitle($title)
	{
		$this->pageTitle = (string) $title;
	}
}