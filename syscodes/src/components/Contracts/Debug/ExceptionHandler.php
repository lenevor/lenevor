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
 * @copyright   Copyright (c) 2019 - 2026 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Contracts\Debug;

use Throwable;

/**
 * Handles an exception into an HTTP response for to render.
 */
interface ExceptionHandler
{
    /**
     * Report or log an exception.
     * 
     * @param  \Throwable  $e
     * 
     * @return mixed
     * 
     * @throws \Exception
     */
    public function report(Throwable $e);

    /**
     * Register a reportable callback.
     * 
     * @param  \callable  $callback
     * 
     * @return static
     */
    public function reportable(callable $callback): static;

    /**
     * Determine if the exception should be reported.
     * 
     * @param  \Throwable  $e
     * 
     * @return bool
     */
    public function shouldReport(Throwable $e): bool;

    /**
     * Determine if the exception is in the "do not report" list.
     * 
     * @param  \Throwable  $e
     * 
     * @return bool
     */
    public function shouldntReport(Throwable $e): bool;

    /**
     * Render an exception into an HTTP response.
     * 
     * @param  \Syscodes\Components\Http\Request  $request
     * @param  \Throwable  $e
     * 
     * @return \Syscodes\Components\Http\Response
     */
    public function render($request, Throwable $e);

    /**
     * Register a renderable callback.
     * 
     * @param  \callable  $callback
     * 
     * @return static
     */
    public function renderable(callable $callback): static;

    /**
     * Render an exception to the console.
     * 
     * @param  \Syscodes\Components\Contracts\Console\Output\ConsoleOutput  $output
     * @param  \Throwable  $e
     * 
     * @return void
     */
    public function renderForConsole($output, Throwable $e);
}