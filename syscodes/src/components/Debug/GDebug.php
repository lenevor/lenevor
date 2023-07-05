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

namespace Syscodes\Components\Debug;

use Throwable;
use ErrorException;
use InvalidArgumentException;
use Syscodes\Components\Debug\Util\Misc;
use Syscodes\Components\Debug\Util\System;
use Syscodes\Components\Stopwatch\Benchmark;
use Syscodes\Components\Debug\Handlers\MainHandler;
use Syscodes\Components\Debug\FrameHandler\Supervisor;
use Syscodes\Components\Debug\Handlers\CallbackHandler;
use Syscodes\Components\Contracts\Debug\Handler as DebugContract;

/**
 * Allows automatically load everything related to exception handlers.
 */
class GDebug implements DebugContract
{
	/**
	 * Allow Handlers to force the script to quit.
	 * 
	 * @var bool $allowQuit
	 */
	protected $allowQuit = true;
	
	/**
	 * Benchmark instance.
	 * 
	 * @var string|object $benchmark
	 */
	protected $benchmark;

	/**
	 * The handler stack.
	 * 
	 * @var array $handlerStack
	 */
	protected $handlerStack = [];

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
	 * @var string|object $system
	 */
	protected $system;

	/**
	 * In certain scenarios, like in shutdown handler, we can not throw exceptions.
	 * 
	 * @var bool $throwExceptions
	 */
	protected $throwExceptions = true;

	/**
	 * Constructor. The Debug class instance.
	 * 
	 * @param  \Syscodes\Components\Debug\Util\System|null  $system
	 * 
	 * @return void
	 */
	public function __construct(System $system = null)
	{
		$this->system    = $system ?: new System;
		$this->benchmark = new Benchmark;
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
	public function handleException(Throwable $exception): string
	{	
		// The start benchmark
		$this->benchmark->start('total_execution', LENEVOR_START);

		$supervisor = $this->getSupervisor($exception);

		// Start buffer
		$this->system->startOutputBuferring();

		$handlerResponse    = null;
		$handlerContentType = null;
		
		try {
			foreach (array_reverse($this->handlerStack) as $handler) {			
				$handler->setDebug($this);
				$handler->setException($exception);
				$handler->setSupervisor($supervisor);
				
				$handlerResponse = $handler->handle();
	
				// Collect the content type for possible sending in the headers
				$handlerContentType = method_exists($handler, 'contentType') ? $handler->contentType() : null;
	
				if (in_array($handlerResponse, [MainHandler::LAST_HANDLER, MainHandler::QUIT])) {
					break;
				}
			}
	
			$quit = $handlerResponse == MainHandler::QUIT && $this->allowQuit();
		}
		finally {
			// Returns the contents of the output buffer
			$output = $this->system->CleanOutputBuffer();	
		}

		// Returns the contents of the output buffer for loading time of page
		$totalTime = $this->benchmark->getElapsedTime('total_execution');
		$output    = str_replace('{{ elapsed_time }}', $totalTime, $output);

		if ($this->writeToOutput()) {
			if ($quit) {
				while ($this->system->getOutputBufferLevel() > 0) {
					// Cleanes the output buffer
					$this->system->endOutputBuffering();
				}

				if (Misc::sendHeaders() && $handlerContentType)	{
					header("Content-Type: {$handlerContentType}");
				}
			}

			$this->writeToOutputBuffer($output);
		}

		if ($quit) {
			$this->system->flushOutputBuffer();
			$this->system->stopException($this->sendHttpCode());
		}

		return $output;
	}

	/**
	 * Allow Handlers to force the script to quit.
	 * 
	 * @param  bool|int|null  $exit
	 * 
	 * @return bool
	 */
	public function allowQuit($exit = null): bool
	{
		if (func_num_args() == 0) {
			return $this->allowQuit;
		}

		return $this->allowQuit = (bool) $exit;
	}

	/**
	 * Lenevor Exception push output directly to the client it the data  
	 * if they are true, but if it is false, the output will be returned 
	 * by exception.
	 * 
	 * @param  bool|int|null  $send
	 *
	 * @return bool
	 */
	public function writeToOutput($send = null): bool
	{
		if (func_num_args() == 0) {
			return $this->sendOutput;
		}
		
		return $this->sendOutput = (bool) $send;
	}
	
	/**
	 * Generate output to the browser.
	 * 
	 * @param  string  $output
	 * 
	 * @return static
	 */
	protected function writeToOutputBuffer($output): static
	{
		if ($this->sendHttpCode() && Misc::sendHeaders()) {
			$this->system->setHttpResponseCode($this->sendHttpCode());
		}
		
		echo $output;
		
		return $this;
	}

	/**
	 * Error handler
	 *
	 * This will catch the php native error and treat it as a exception which will 
	 * provide a full back trace on all errors.
	 *
	 * @param  int  $level
	 * @param  string  $message
	 * @param  string|null  $file
	 * @param  int|null  $line
	 * 
	 * @return bool
	 * 
	 * @throws \ErrorException
	 */
	public function handleError(
		int $level, 
		string $message, 
		string $file = null, 
		int $line = null
	): bool {
		if ($level & $this->system->getErrorReportingLevel()) {
			$exception = new ErrorException($message, $level, $level, $file, $line);

			if ($this->throwExceptions) {
				throw $exception;
			} else {
				$this->handleException($exception);
			}

			return true;
		}

		return false;
	}

	/**
	 * Appends a handler to the end of the stack.
	 * 
	 * @param  \Callable|\Syscodes\Components\Contracts\Debug\Handler  $handler
	 * 
	 * @return static
	 */
	public function appendHandler($handler): static
	{
		array_unshift($this->handlerStack, $this->resolveHandler($handler));

		return $this;
	}

	/**
	 * Prepends a handler to the start of the stack.
	 * 
	 * @param  \Callable|\Syscodes\Components\Contracts\Debug\Handler  $handler
	 * 
	 * @return static
	 */
	public function prependHandler($handler): static
	{
		return $this->pushHandler($handler);
	}

	/**
	 * Pushes a handler to the end of the stack.
	 * 
	 * @param  string|callable  $handler
	 * 
	 * @return static
	 */
	public function pushHandler($handler): static
	{
		$this->handlerStack[] = $this->resolveHandler($handler);

		return $this;
	}

	/**
	 * Create a CallbackHandler from callable and throw if handler is invalid.
	 * 
	 * @param  \Callable|\Syscodes\Components\Contracts\Debug\Handler  $handler
	 * 
	 * @return \Syscodes\Components\Contracts\Debug\Handler
	 * 
	 * @throws \InvalidArgumentException If argument is not callable or instance of \Syscodes\Components\Contracts\Debug\Handler
	 */
	protected function resolveHandler($handler)
	{
		if (is_callable($handler)) {
			$handler = new CallbackHandler($handler);
		}

		if ( ! $handler instanceof MainHandler) {
			throw new InvalidArgumentException(
				"Argument to " . __METHOD__ . " must be a callable, or instance of ".
				"Syscodes\Components\\Contracts\\Debug\\Handler"
			);
		}

		return $handler;
	}

	/**
	 * Returns an array with all handlers, in the order they were added to the stack.
	 * 
	 * @return array
	 */
	public function getHandlers(): array
	{
		return $this->handlerStack;
	}

	/**
	 * Clears all handlers in the handlerStack, including the default PleasingPage handler.
	 * 
	 * @return static
	 */
	public function clearHandlers(): static
	{
		$this->handlerStack = [];

		return $this;
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
	 * Gets supervisor already specified.
	 * 
	 * @param  \Throwable  $exception
	 * 
	 * @return \Syscodes\Components\Debug\Engine\Supervisor
	 */
	protected function getSupervisor(Throwable $exception)
	{
		return new Supervisor($exception);
	}

	/**
	 * Unregisters all handlers registered by this Debug instance.
	 * 
	 * @return void
	 */
	public function off(): void
	{
		$this->system->restoreExceptionHandler();
		$this->system->restoreErrorHandler();
	}
	
	/**
	 * Registers this instance as an error handler.
	 * 
	 * @return void
	 */
	public function on() : void
	{
		// Set the exception handler
		$this->system->setExceptionHandler([$this, self::EXCEPTION_HANDLER]);
		// Set the error handler
		$this->system->setErrorHandler([$this, self::ERROR_HANDLER]);
		// Set the handler for shutdown to catch Parse errors
		$this->system->registerShutdownFunction([$this, self::SHUTDOWN_HANDLER]);
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
		if (func_num_args() == 0) {
			return $this->sendHttpCode;
		}
		
		if ( ! $code) {
			return $this->sendHttpCode = false;
		}
		
		if ($code === true) {
			$code = 500;
		}
		
		if ($code < 400 || 600 <= $code) {
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
	public function handleShutdown()
	{
		$this->throwExceptions = false;

		$error = $this->system->getLastError();

		// If we've got an error that hasn't been displayed, then convert
		// it to an Exception and use the Exception handler to display it
		// to the user
		if ($error && Misc::isFatalError($error['type'])) {
			$this->allowQuit = false;
			
			$this->handleError($error['type'], $error['message'], $error['file'], $error['line']);
		}
	}
}