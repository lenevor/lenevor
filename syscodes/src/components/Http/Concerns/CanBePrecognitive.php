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

namespace Syscodes\Components\Http\Concerns;

/**
 * Lets know if it is precognitive.
 */
trait CanBePrecognitive
{
    /**
     * Determine if the request is attempting to be precognitive.
     * 
     * @return bool
     */
    public function isAttemptingPrecognition(): bool
    {
        return $this->header('Precognition') === 'true';
    }
    
    /**
     * Determine if the request is precognitive.
     * 
     * @return bool
     */
    public function isPrecognitive(): bool
    {
        return $this->attributes->get('precognitive', false);
    }
}