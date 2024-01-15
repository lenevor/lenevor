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

namespace Syscodes\Components\Http\Exceptions;

use Throwable;
use LogicException;

/**
 * SessionNotFoundException.
 */
class SessionNotFoundException extends LogicException
{
    /**
     * Initialize constructor.
     * 
     * @param  string  $message
     * @param  int  $code
     * @param  \Throwable  $previous
     * 
     * @return void 
     */    
    public function __construct(string $message = '', int $code = 0, Throwable $previous = null)
    {
        if ($message === '') {
            $message = 'There is currently no session available';
        }

        parent::__construct($message, $code, $previous);
    }
}