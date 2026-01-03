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

namespace Syscodes\Components\Finder\Comparators;

use InvalidArgumentException;

/**
 * Allows realize compiles a simple comparison to an anonymous
 * subroutine with number.
 */
class NumberComparator extends Comparator
{
    /**
     * Constructor. Create a new NumberComparator instance,
     * 
     * @param  string|null  $value  A comparison string or null
     * 
     * @throws \InvalidArgumentException
     */
    public function __construct(?string $value)
    {
        if (null === $value || !preg_match('#^\s*(==|!=|[<>]=?)?\s*([0-9\.]+)\s*([kmg]i?)?\s*$#i', $value, $matches)) {
            throw new InvalidArgumentException(sprintf('Don\'t understand "%s" as a number test.', $value ?? 'null'));
        }
        
        $target = $matches[2];
        
        if ( ! is_numeric($target)) {
            throw new InvalidArgumentException(sprintf('Invalid number "%s"', $target));
        }
        
        if (isset($matches[3])) {
            // magnitude
            switch (strtolower($matches[3])) {
                case 'k':
                    $target *= 1000;
                    break;
                case 'ki':
                    $target *= 1024;
                    break;
                case 'm':
                    $target *= 1000000;
                    break;
                case 'mi':
                    $target *= 1024 * 1024;
                    break;
                case 'g':
                    $target *= 1000000000;
                    break;
                case 'gi':
                    $target *= 1024 * 1024 * 1024;
                    break;
            }
        }
        
        parent::__construct($target, $matches[1] ?: '==');
    }
}