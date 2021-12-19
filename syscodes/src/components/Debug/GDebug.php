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
 * @copyright   Copyright (c) 2019 - 2021 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Debug;

use Throwable;
use ErrorException;
use InvalidArgumentException;
use Syscodes\Components\Debug\Benchmark;
use Syscodes\Components\Debug\Util\Misc;
use Syscodes\Components\Debug\Util\System;
use Syscodes\Components\Debug\Handlers\MainHandler;
use Syscodes\Components\Debug\Util\TemplateHandler;
use Syscodes\Components\Debug\FrameHandler\Supervisor;
use Syscodes\Components\Debug\Handlers\CallbackHandler;
use Syscodes\Components\Contracts\Debug\Handler as DebugContract;

/**
 * Allows automatically load everything related to exception handlers.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
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
	 * @var string $benchmark
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
	 * {@inheritdoc}
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
			foreach ($this->handlerStack as $handler) {			
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
	
			$Quit = $handlerResponse == MainHandler::QUIT && $this->allowQuit();
		}
		finally {
			// Returns the contents of the output buffer
			$output = $this->system->CleanOutputBuffer();	
		}

		// Returns the contents of the output buffer for loading time of page
		$totalTime = $this->benchmark->getElapsedTime('total_execution');
		$output    = str_replace('{elapsed_time}', $totalTime, $output);

		if ($this->writeToOutput()) {
			if ($Quit) {
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

		if ($Quit) {
			$this->system->flushOutputBuffer();
			$this->system->stopException(1);
		}

		return $output;
	}

	/**
	 * {@inheritdoc}
	 */
	public function allowQuit($exit = null)
	{
		if (func_num_args() == 0) {
			return $this->allowQuit;
		}

		return $this->allowQuit = (bool) $exit;
	}

	/**
	 * {@inheritdoc}
	 */
	public function writeToOutput($send = null)
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
	 * @return self
	 */
	protected function writeToOutputBuffer($output): self
	{
		if ($this->sendHttpCode() && Misc::sendHeaders()) {
			$this->system->setHttpResponseCode($this->sendHttpCode());
		}
		
		echo $output;
		
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function handleError(
		int $level, 
		string $message, 
		string $file = null, 
		int $line = null
	) {
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
	 * {@inheritdoc}
	 */
	public function pushHandler($handler)
	{
		return $this->prependHandler($handler);
	}

	/**
	 * {@inheritdoc}
	 */
	public function appendHandler($handler): self
	{
		array_unshift($this->handlerStack, $this->resolveHandler($handler));

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function prependHandler($handler): self
	{
		array_unshift($this->handlerStack, $this->resolveHandler($handler));

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
	 * {@inheritdoc}
	 */
	public function getHandlers(): array
	{
		return $this->handlerStack;
	}

	/**
	 * {@inheritdoc}
	 */
	public function clearHandlers(): self
	{
		$this->handlerStack = [];

		return $this;
	}

	/**
	 * {@inheritdoc}
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
	 * {@inheritdoc}
	 */
	public function off(): void
	{
		$this->system->restoreExceptionHandler();
		$this->system->restoreErrorHandler();
	}
	
	/**
	 * {@inheritdoc}
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
	 * {@inheritdoc}
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
	 * {@inheritdoc}
	 */
	public function handleShutdown()
	{
		$this->throwExceptions = false;

		$error = $this->system->getLastError();

		// If we've got an error that hasn't been displayed, then convert
		// it to an Exception and use the Exception handler to display it
		// to the user
		if ($error && Misc::isFatalError($error['type'])) {
			$this->errorHandler($error['type'], $error['message'], $error['file'], $error['line']);
		}
	}
}