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

namespace Syscodes\Components\Core;

use Syscodes\Components\Support\Collection;

/**
 * Example of information for console.
 */
class Inspiration
{
    /**
     * Get an inspiring quote.
     * 
     * @return string
     */
    public static function quote()
    {
        return static::quotes()->random();
    }
    
    /**
     * Get the collection of inspiring quotes.
     * 
     * @return \Syscodes\Components\Support\Collection
     */
    public static function quotes()
    {
        return Collection::make([
            '“When you innovate, you make mistakes. What makes the difference is realizing them, correcting them, and continuing to innovate.” - Steve Jobs',
            '“Perform each of your actions as if it were the last of your life.” - Marco Aurelio',
            '“Life is not what one lived, but what one remembers, and how one remembers it in order to recount it.” - Gabriel Garcia Marquez',
            '“If you knew the magnificence of 3, 6, and 9, then you would have the key to the universe.” - Nikola Tesla',
        ]);
    }
}