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

namespace Syscodes\Components\Validation\Rules\Traits;

use Exception;
 
/**
 * Check the dates.
 */
trait DateUtils
{
    /**
     * Check the date is valid
     * 
     * @param  string  $date
     * 
     * @return bool
     */
    protected function isValidDate(string $date): bool
    {
        return (strtotime($date) !== false);
    }
    
    /**
     * Throw exception.
     * 
     * @param  string  $value
     * 
     * @return Exception
     */
    protected function throwException(string $value): Exception
    {
        $message = "Expected a valid date, got '{$value}' instead. 2016-12-08, 2016-12-02 14:58, tomorrow are considered valid dates";
        
        return new Exception($message);
    }
    
    /**
     * Given $date and get the timestamp.
     * 
     * @param  mixed  $date
     * 
     * @return int
     */
    protected function getTimeStamp($date): int
    {
        return strtotime($date);
    }
}