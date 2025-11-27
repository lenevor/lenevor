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

namespace Syscodes\Components\Console\View\Components;

use InvalidArgumentException;

/**
 * Calls of components.
 */
class Factory
{
    /**
     * The output interface implementation.
     * 
     * @var \Syscodes\Components\Console\OutputStyle
     */
    protected $output;

    /**
     * Constructor. Create a new Factory class instance.
     * 
     * @param  \Syscodes\Components\Console\OutputStyle  $output
     * 
     * @return void
     */
    public function __construct($output)
    {
        $this->output = $output;
    }

    /**
     * Magic method.
     * 
     * Dynamically handle calls into the component instance.
     *
     * @param  string  $method
     * @param  array  $parameters
     * 
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    public function __call(string $method, array $parameters): mixed
    {
        $component = '\Syscodes\Components\Console\View\Components\\'.ucfirst($method);

        throw_unless(class_exists($component), new InvalidArgumentException(sprintf(
            'Console component [%s] not found.', $method
        )));

        return (new $component($this->output))->render(...$parameters);
    }
}