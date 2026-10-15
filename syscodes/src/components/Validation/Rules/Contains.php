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

namespace Syscodes\Components\Validation\Rules;

use Stringable;
use Syscodes\Components\Contracts\Support\Arrayable;

use function Syscodes\Components\Support\enum_value;

/**
 * Allows specify the constains rules.
 */
class Contains implements Stringable
{
    /**
     * The values that should be contained in the attribute.
     *
     * @var array
     */
    protected $values;

    /**
     * Constructor. Create a new contains rule instance.
     *
     * @param  \Syscodes\Components\Contracts\Support\Arrayable|\UnitEnum|array|string  $values
     * @return void
     */
    public function __construct($values)
    {
        if ($values instanceof Arrayable) {
            $values = $values->toArray();
        }

        $this->values = is_array($values) ? $values : func_get_args();
    }

    /**
     * Magic method.
     * 
     * Convert the rule to a validation string.
     *
     * @return string
     */
    public function __toString(): string
    {
        $values = array_map(function ($value) {
            $value = enum_value($value);

            return '"'.str_replace('"', '""', (string) $value).'"';
        }, $this->values);

        return 'contains:'.implode(',', $values);
    }
}