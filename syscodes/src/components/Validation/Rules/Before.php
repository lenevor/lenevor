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

namespace Syscodes\Components\Validation\Rules;

use Exception;
use Syscodes\Components\Validation\Rules;
use Syscodes\Components\Validation\Rules\Traits\DateUtils;

/**
 * Gets the attribute must be before.
 */
class Before extends Rules
{
    use DateUtils;
    
    /** 
     * Get the message.
     * 
     * @var string $message
     */
    protected $message = "The :attribute must be a date before :time";
    
    /**
     * The fillable params.
     * 
     * @var array $fillableParams
     */
    protected $fillableParams = ['time'];
    
    /**
     * Check the value is valid.
     * 
     * @param  mixed  $value
     * 
     * @return bool
     * 
     * @throws Exception
     */
    public function check($value): bool
    {
        $this->requireParameters($this->fillableParams);
        
        $time = $this->parameter('time');
        
        if ( ! $this->isValidDate($value)) {
            throw $this->throwException($value);
        }
        
        if ( ! $this->isValidDate($time)) {
            throw $this->throwException($time);
        }
        
        return $this->getTimeStamp($time) > $this->getTimeStamp($value);
    }
}