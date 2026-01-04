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

namespace Syscodes\Components\Support\Traits;

use Closure;
use Syscodes\Components\Core\Application;

/**
 * Initialize the callback with the given locale.
 */
trait Localizable
{
    /**
     * Run the callback with the given locale.
     * 
     * @param  string  $locale
     * @param  \Closure  $callback
     * 
     * @return mixed
     */
    public function assignLocale(string $locale, Closure $callback)
    {
        if ( ! $locale) {
            return $callback();
        }
        
        $app = Application::getInstance();
        
        $original = $app->getLocale();
        
        try {
            $app->setLocale($locale);
            
            return $callback();
        } finally {
            $app->setLocale($original);
        }
    }
}