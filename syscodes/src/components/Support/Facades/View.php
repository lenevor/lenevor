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
 * @copyright   Copyright (c) 2019 - 2021 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Support\Facades;

/**
 * Initialize the View class facade.
 *
 * @author Alexander Campo <jalexcam@gmail.com>
 * 
 * @method static bool viewExists(string $view)
 * @method static \Syscodes\Contracts\View\View make(string $view, array $data = [])
 * @method static \Syscodes\Contracts\View\Engine getEngineFromPath(string $path)
 * @method static array getExtensions()
 * @method static mixed share(array|string $key, mixed $value = null)
 * @method static void getShared()
 * @method static \Syscodes\Contracts\Container\Container getContainer()
 * @method static \Syscodes\Contracts\Container\Container setContainer(\Syscodes\Contracts\Container\Container $container)
 * 
 * @see \Syscodes\View\Factory
 */
class View extends Facade
{
    /**
     * Get the registered name of the component.
     * 
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'view';
    }
}