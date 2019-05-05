<?php 

namespace Syscode\Debug;

use Throwable;
use ErrorException;
use InvalidArgumentException;
use Syscode\Debug\Util\System;
use Syscode\Debug\Engine\Supervisor;
use Syscode\Debug\Handlers\MainHandler;
use Syscode\Debug\Handlers\CallbackHandler;
use Syscode\Contracts\Debug\Handler as HandlerContract;

/**
 * Lenevor PHP Framework
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
 * @copyright   Copyright (c) 2018-2019 Lenevor Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.8.0
 */
class Debug implements HandlerContract
{
	/**
	 * The handler stack.
	 * 
	 * @var array $handlerStack
	 */
	protected $handlerStack = [];

	/**
	 * The levels of error.
	 *
	 * @var array $levels
	 */
	protected $levels = [
		0                   => 'Error',
		E_ERROR             => 'Fatal Error',
		E_WARNING           => 'Warning',
		E_PARSE             => 'Parsing Error',
		E_NOTICE            => 'Notice',
		E_CORE_ERROR        => 'Core Error',
		E_CORE_WARNING      => 'Core Warning',
		E_COMPILE_ERROR     => 'Compile Error',
		E_COMPILE_WARNING   => 'Compile Warning',
		E_USER_ERROR        => 'User Error',
		E_USER_WARNING      => 'User Warning',
		E_USER_NOTICE       => 'User Notice',
		E_STRICT            => 'Runtime Notice',
		E_RECOVERABLE_ERROR => 'Runtime Recoverable error',
		E_DEPRECATED        => 'Runtime Deprecated code usage',
		E_USER_DEPRECATED   => 'User Deprecated code usage'		
	];

	/**
	 * The errors of php.
	 *
	 * @var array $phpErrors
	 */
	protected $phpErrors = [ 
		E_ERROR, 
		E_PARSE,
		E_CORE_ERROR,
		E_CORE_WARNING,
		E_USER_ERROR, 
		E_COMPILE_ERROR,
		E_COMPILE_WARNING
	];

	/**
	 * The send Http code by default: 500 Internal Server Error.
	 * 
	 * @var bool $sendHttpCode
	 */
	protected $sendHttpCode = 500;

	/**
	 * The send output.
	 * 
	 * @var bool $sendOutput
	 */
	protected $sendOutput = true;

	/**
	 * The functions of system what control errors and exceptions.
	 * 
	 * @var string $system
	 */
	protected $system;

	/**
	 * In certain scenarios, like in shutdown handler, we can not throw exceptions.
	 * 
	 * @var bool $throwExceptions
	 */
	protected $throwExceptions = true;

	/**
	 * Constructor. The ExceptionHandler class.
	 * 
	 * @param  string                           $http
	 * @param  \Syscode\Core\Debug\Util\System  $system
	 * 
	 * @return void
	 */
	public function __construct(System $system)
	{
		$this->system = $system;
	}

	/**
	 * Clears all handlers in the handlerStack, including the default PleasingPage handler.
	 * 
	 * @return $this
	 */
	public function clearHandlers()
	{
		$this->handlerStack = [];

		return $this;
	}

	/**
	 * Catches any uncaught errors and exceptions, including most Fatal errors. Will log the 
	 * error, display it if display_errors is on, and fire an event that allows custom actions 
	 * to be taken at this point.
	 *
	 * @param  \Throwable  $exception
	 *
	 * @return string
	 */
	public function exceptionHandler(Throwable $exception)
	{
		$supervisor = $this->getSupervisor($exception);

		// Start buffer
		$this->system->startOutputBuferring();

		$handlerResponse    = null;
		$handlerContentType = null;
		
		foreach (array_reverse($this->getHandlers()) as $handler)
		{			
			$handler->setDebug($this);
			$handler->setException($exception);
			$handler->setSupervisor($supervisor);
			
			$handlerResponse = $handler->handle();

			// Collect the content type for possible sending in the headers
			$handlerContentType = method_exists($handler, 'contentType') ? $handler->contentType() : null;

			if (in_array($handlerResponse, [MainHandler::QUIT]))
			{
				break;
			}
		}

		$Quit = $handlerResponse == MainHandler::QUIT;

		// Returns the contents of the output buffer
		$buffer = $this->system->CleanOutputBuffer();

		if ($this->writeToOutput())
		{
			if ($Quit)
			{
				while ($this->system->getOutputBufferLevel() > 0) 
				{
					// Cleanes the output buffer
					$this->system->endOutputBuffering();
				}

				if (System::sendHeaders() && $handlerContentType)
				{
					header("Content-Type: {$handlerContentType}");
				}
			}

			$this->writeToOutputBuffer($buffer);
		}

		if ($Quit)
		{
			$this->system->flushOutputBuffer();
			$this->system->stopException(1);
		}

		return $buffer;
	}

	/**
	 * Error handler
	 *
	 * This will catch the php native error and treat it as a exception which will 
	 * provide a full back trace on all errors.
	 *
	 * @param  int          $level
	 * @param  string       $message
	 * @param  string|null  $file
	 * @param  int|null     $line
	 *
	 * @throws \ErrorException
	 */
	public function errorHandler($level, $message, $file = null, $line = null)
	{
		if ($level & $this->system->getErrorReportingLevel()) 
		{
			$exception = new ErrorException($message, $level, $level, $file, $line);

			if ($this->throwExceptions)
			{
				throw $exception;
			}
			else
			{
				$this->exceptionHandler($exception);
			}

			return true;
		}

		return false;
	}

	/**
	 * Returns an array with all handlers, in the order they were added to the stack.
	 * 
	 * @return array
	 */
	public function getHandlers()
	{
		return $this->handlerStack;
	}

	/**
	 * Gets supervisor already specified.
	 * 
	 * @param  \Throwable  $exception
	 * 
	 * @return \Syscode\Debug\Engine\Supervisor
	 */
	protected function getSupervisor($exception)
	{
		return new Supervisor($exception);
	}

	/**
	 * Determine if the error level is fatal.
	 * 
	 * @param  int  $level
	 * 
	 * @return bool
	 */
	protected function isFatal(int $level)
	{
		return in_array($level, $this->phpErrors);
	}
	
	/**
	 * Registers this instance as an error handler.
	 * 
	 * @return void
	 */
	public function on() 
	{
		// Set the exception handler
		$this->system->setExceptionHandler([$this, self::EXCEPTION_HANDLER]);
		// Set the error handler
		$this->system->setErrorHandler([$this, self::ERROR_HANDLER]);
		// Set the handler for shutdown to catch Parse errors
		$this->system->registerShutdownFunction([$this, self::SHUTDOWN_HANDLER]);
	}

	/**
	 * Removes the last handler in the stack and returns it.
	 * 
	 * @return array|null
	 */
	public function popHandler()
	{
		return array_pop($this->handlerStack);
	}

	/**
	 * Pushes a handler to the end of the stack.
	 * 
	 * @param  string|callable  $handler
	 * 
	 * @return $this
	 */
	public function pushHandler($handler)
	{
		if (is_callable($handler))
		{
			$handler = new CallbackHandler($handler);
		}

		$this->handlerStack[] = $handler;

		return $this;
	}

	/**
	 * Lenevor Exception will by default send HTTP code 500, but you may wish
	 * to use 502, 503, or another 5xx family code.
	 * 
	 * @param  bool|int  $code
	 * 
	 * @return int|false
	 * 
	 * @throws \InvalidArgumentException
	 */
	public function sendHttpCode($code = null)
	{
		if (func_num_args() == 0)
		{
			return $this->sendHttpCode;
		}
		
		if ( ! $code)
		{
			return $this->sendHttpCode = false;
		}
		
		if ($code === true)
		{
			$code = 500;
		}
		
		if ($code < 400 || 600 <= $code)
		{
			throw new InvalidArgumentException("Invalid status code {$code}, must be 4xx or 5xx");
		}
		
		return $this->sendHttpCode = $code;
	}

	/**
	 * This will catch errors that are generated at the shutdown level of execution.
	 *
	 * @return void
	 *
	 * @throws \ErrorException
	 */
	public function shutdownHandler()
	{
		$this->throwExceptions = false;

		$error = $this->system->getLastError();

		// If we've got an error that hasn't been displayed, then convert
		// it to an Exception and use the Exception handler to display it
		// to the user
		if ($error && $this->isFatal($error['type']))
		{
			$this->errorHandler($error['type'], $error['message'], $error['file'], $error['line']);
		}
	}

	/**
	 * Lenevor Exception push output directly to the client it the data  
	 * if they are true, but if it is false, the output will be returned 
	 * by exception.
	 * 
	 * @param  bool|int  $send
	 *
	 * @return bool
	 */
	protected function writeToOutput($send = null)
	{
		if (func_num_args() == 0)
		{
			return $this->sendOutput;
		}
		
		return $this->sendOutput = (bool) $send;
	}
	
	/**
	 * Generate output to the browser.
	 * 
	 * @param  string  $output
	 * 
	 * @return $this
	 */
	protected function writeToOutputBuffer($output)
	{
		if ($this->sendHttpCode() && System::sendHeaders())
		{
			$this->system->setHttpResponseCode($this->sendHttpCode());
		}
		
		echo $output;
		
		return $this;
	}
}