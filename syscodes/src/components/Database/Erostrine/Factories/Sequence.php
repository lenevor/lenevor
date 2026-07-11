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

namespace Syscodes\Components\Database\Erostrine\Factories;

use Countable;

/**
 * Gets the count of the sequence of elements.
 */
class Sequence implements Countable
{
    /**
     * The sequence of return values.
     *
     * @var array
     */
    protected $sequence;

    /**
     * The count of the sequence items.
     *
     * @var int
     */
    public $count;

    /**
     * The current index of the sequence iteration.
     *
     * @var int
     */
    public $index = 0;

    /**
     * Constructor. Create a new sequence instance.
     *
     * @param  array  $sequence 
     * @return void
     */
    public function __construct(...$sequence)
    {
        $this->sequence = $sequence;
        $this->count = count($sequence);
    }

    /**
     * Get the current count of the sequence items.
     *
     * @return int
     */
    public function count(): int
    {
        return $this->count;
    }

    /**
     * Magic method.
     * 
     * Get the next value in the sequence.
     *
     * @return mixed
     */
    public function __invoke()
    {
        return take(value($this->sequence[$this->index % $this->count], $this), function () {
            $this->index = $this->index + 1;
        });
    }
}