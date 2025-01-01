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

namespace Syscodes\Components\Finder\Comparators;

use Exception;
use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Allows realize the comparison operator with date.
 */
class DateComparator extends Comparator
{
    /**
     * Constructor. Create a new DateComparator class instance.
     * 
     * @param  string  $comparison
     * 
     * @return void
     */
    public function __construct(string $comparison)
    {
        if ( ! preg_match('~^\s*(==|!=|[<>]=?|after|since|before|until)?\s*(.+?)\s*$~i', $comparison, $matches)) {
            throw new InvalidArgumentException(sprintf('The date comparison "%s" isn\'t correct', $comparison));
        }
        
        try {
            $date   = new DateTimeImmutable($matches[2]);
            $target = $date->format('U');
        } catch (Exception) {
            throw new InvalidArgumentException(sprintf('"%s" is not a valid date', $matches[2]));
        }

        $operator = $matches[1] ?? '==';

        if ('since' === $operator || 'after' === $operator) {
            $operator = '>';
        }

        if ('until' === $operator || 'before' === $operator) {
            $operator = '<';
        }

        parent::__construct($target, $operator);
    }
}