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
use Syscodes\Components\Validation\Rules\Traits\Size;

/**
 * Gets the attribute must be between.
 */
class Between extends Rules
{
    use Size;
    
    /** 
     * Get the message.
     * 
     * @var string $message
     */
    protected $message = "The :attribute must be between :min and :max";
    
    /**
     * The fillable params.
     * 
     * @var array $fillableParams
     */
    protected $fillableParams = ['min', 'max'];
    
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
        
        $min = $this->getBytesSize($this->parameter('min'));
        $max = $this->getBytesSize($this->parameter('max'));
        
        $valueSize = $this->getValueSize($value);
        
        if ( ! is_numeric($valueSize)) {
            return false;
        }
        
        return ($valueSize >= $min && $valueSize <= $max);
    }
}