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
 * @copyright   Copyright (c) 2019 - 2025 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Contracts\Debug;

use Throwable;

/**
 * Sets any uncaught errors and exceptions, including most Fatal errors and HTTP code submissions.
 */
interface Handler
{
	const ERROR_HANDLER     = "HandleError";
	const EXCEPTION_HANDLER = "handleException";
	const SHUTDOWN_HANDLER  = "handleShutdown"; 
	
	/**
	 * Catches any uncaught errors and exceptions, including most Fatal errors. Will log the 
	 * error, display it if display_errors is on, and fire an event that allows custom actions 
	 * to be taken at this point.
	 *
	 * @param  \Throwable  $exception
	 *
	 * @return string
	 */
	public function handleException(Throwable $exception): string;
	
	/**
	 * Pushes a handler to the end of the stack.
	 * 
	 * @param  string|callable  $handler
	 * 
	 * @return \Syscodes\Components\Contracts\Debug\Handler
	 */
	public function pushHandler($handler);

	/**
	 * Appends a handler to the end of the stack.
	 * 
	 * @param  \Callable|\Syscodes\Components\Contracts\Debug\Handler  $handler
	 * 
	 * @return self
	 */
	public function appendHandler($handler): self;

	/**
	 * Prepends a handler to the start of the stack.
	 * 
	 * @param  \Callable|\Syscodes\Components\Contracts\Debug\Handler  $handler
	 * 
	 * @return self
	 */
	public function prependHandler($handler): self;
	
	/**
	 * Unregisters all handlers registered by this Debug instance.
	 * 
	 * @return void
	 */
	public function off(): void;
	
	/**
	 * Registers this instance as an error handler.
	 * 
	 * @return void
	 */
	public function on(): void;

	/**
	 * Allow Handlers to force the script to quit.
	 * 
	 * @param  bool|int|null  $exit
	 * 
	 * @return bool
	 */
	public function allowQuit($exit = null);
	
	/**
	 * Lenevor Exception push output directly to the client it the data  
	 * if they are true, but if it is false, the output will be returned 
	 * by exception.
	 * 
	 * @param  bool|int|null  $send
	 *
	 * @return bool
	 */
	public function writeToOutput($send = null);

	/**
	 * Returns an array with all handlers, in the order they were added to the stack.
	 * 
	 * @return array
	 */
	public function getHandlers(): array;

	/**
	 * Clears all handlers in the handlerStack, including the default PleasingPage handler.
	 * 
	 * @return self
	 */
	public function clearHandlers(): self;

	/**
	 * Removes the last handler in the stack and returns it.
	 * 
	 * @return array|null
	 */
	public function popHandler();
	
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
		?string $file = null, 
		?int $line = null
	);
	
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
	public function sendHttpCode($code = null);
	
	/**
	 * This will catch errors that are generated at the shutdown level of execution.
	 *
	 * @return void
	 *
	 * @throws \ErrorException
	 */
	public function handleShutdown();
}