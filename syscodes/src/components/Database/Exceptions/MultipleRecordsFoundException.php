<?php

/**
 * Lenevor PHP Framework
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

namespace Syscodes\Components\Database\Exceptions;

use RuntimeException;

/**
 * The multiple records found exception.
 */
class MultipleRecordsFoundException extends RuntimeException
{
    /**
     * The number of records found.
     *
     * @var int
     */
    public $count;

    /**
     * Constructor. Create a new exception instance.
     *
     * @param  int  $count
     * @param  int  $code
     * @param  \Throwable|null  $previous
     * 
     * @return void
     */
    public function __construct($count, $code = 0, $previous = null)
    {
        $this->count = $count;

        parent::__construct("$count records were found.", $code, $previous);
    }

    /**
     * Get the number of records found.
     *
     * @return int
     */
    public function getCount(): int
    {
        return $this->count;
    }
}