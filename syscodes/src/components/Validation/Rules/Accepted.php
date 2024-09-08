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

use Syscodes\Components\Validation\Rules;

/**
 * Gets the attribute must be accepted.
 */
final class Accepted extends Rules
{
    /**
     * The attribute if is implicit.
     * 
     * @var bool $implicit
     */
    protected $implicit = true;
    
    /**
     * Gets the message of the attribute.
     * 
     * @var string $message
     */
    protected $message = "The :attribute must be accepted";
    
    /**
     * Check the $value is accepted.
     * 
     * @param  mixed  $value
     * 
     * @return bool
     */
    public function check($value): bool
    {
        $acceptables = ['yes', 'on', '1', 1, true, 'true'];
        
        return in_array($value, $acceptables, true);
    }
}