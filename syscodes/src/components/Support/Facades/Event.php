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
 * @since       0.6.1
 */

namespace Syscodes\Support\Facades;

/**
 * Initialize the Event class facade.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 * 
 * @method static void listen(string|array $events, \Closure|string $listener, int $priority = 0)
 * @method static \Closure makeListener(\Closure|string $listener, bool $wilcard = false)
 * @method static \Closure createClassListener(string $listener, bool $wilcard = false)
 * @method static bool hasListeners(string $eventName)
 * @method static void subscribe(object|string $subscriber)
 * @method static array|null dispatch(string|object $event, mixed $payload = [], bool $halt = false)
 * @method static array getListeners(string $eventName)
 * @method static void delete(string $event)
 * 
 * @see \Syscodes\Events\Dispatcher
 */
class Event extends Facade
{
    /**
     * Get the registered name of the component.
     * 
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'events';
    }
}