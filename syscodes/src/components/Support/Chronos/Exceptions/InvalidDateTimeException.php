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

namespace Syscodes\Components\Support\Chronos\Exceptions;

use Exception;
use InvalidArgumentException;

/**
 * Invalid datetime exception.
 */
class InvalidDateTimeException extends InvalidArgumentException
{
    /**
     * Constructor. The InvalidDateTimeException class instance.
     * 
     * @param  string  $message
     * @param  int  $code
     * @param  \Exception|null  $previous 
     * 
     * @return void
     */
    public function __construct(string $message, int $code = 0, Exception $previous = null)
    {
        return parent::__construct($message, $code, $previous);
    }
}