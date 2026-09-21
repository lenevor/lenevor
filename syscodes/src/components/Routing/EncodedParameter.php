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

namespace Syscodes\Components\Routing;

use Stringable;

/**
 * Allows the encoded parameter.
 */
class EncodedParameter implements Stringable
{
    /**
     * Constructor. Create a new encoded parameter instance.
     *
     * @param  string  $value
     * @return void
     */
    public function __construct(protected string $value)
    {
        //
    }

    /**
     * Get the encoded parameter value.
     *
     * @return string
     */
    public function value(): string
    {
        return $this->value;
    }

    /**
     * Magic method.
     * 
     * Get the encoded parameter value.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->value;
    }
}