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

namespace Syscodes\Core\Console;

use Closure;
use Exception;
use Throwable;
use ReflectionClass;
use Syscodes\Support\Str;
use Syscodes\Support\Finder;
use Syscodes\Collections\Arr;
use Syscodes\Contracts\Core\Application;
use Syscodes\Contracts\Events\Dispatcher;
use Syscodes\Contracts\Debug\ExceptionHandler;
use Syscodes\Console\Application as LenevorConsole;
use Syscodes\Debug\FatalExceptions\FatalThrowableError;

/**
 * The Lenevor class is the heart of the system when use 
 * the console of commands in framework.
 *
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class Lenevor extends LenevorConsole
{

}