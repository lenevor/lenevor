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

namespace Syscodes\Components\Container\Attributes;

use Attribute;
use Syscodes\Components\Contracts\Container\Container;
use Syscodes\Components\Contracts\Container\ContextualAttribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
class Log implements ContextualAttribute
{
    /**
     * Constructor. Create a new class instance.
     * 
     * @param  string|null  $name
     * 
     * @return void
     */
    public function __construct(public ?string $name = null)
    {
    }

    /**
     * Resolve the log store.
     *
     * @param  self  $attribute
     * @param  \Syscodes\Components\Contracts\Container\Container  $container
     * 
     * @return \Psr\Log\LoggerInterface
     */
    public static function resolve(self $attribute, Container $container)
    {
        return $container->make('log')->store($attribute->name);
    }
}