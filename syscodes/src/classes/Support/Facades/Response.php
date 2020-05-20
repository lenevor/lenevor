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
 * @author      Javier Alexander Campo M. <jalexcam@gmail.com>
 * @link        https://lenevor.com 
 * @copyright   Copyright (c) 2019-2020 Lenevor Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.1.0
 */

namespace Syscodes\Support\Facades;

use Syscodes\Contracts\Routing\RouteResponse as ResponseContract;

/**
 * Initialize the Response class facade.
 *
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 * 
 * @method static \Syscodes\Http\Response make(string $body = '', int $status = 200, array $headers = [])
 * @method static \Syscodes\Http\Response noContent(string $status = 204, array $headers = [])
 * @method static \Syscodes\Http\Response view(string $view, array $data = [], int $status = 200, array $headers = [])
 * @method static \Syscodes\Http\Response json(mixed $data = [], int $status = 200, array $headers = [], int $options = 0)
 * @method static \Syscodes\Http\Response redirectTo(string $path, int $status = 302, array $headers = [], bool $secure = null)
 * 
 * @see \Syscodes\Contracts\Routing\RouteResponse
 */
class Response extends Facade
{
    /**
     * Get the registered name of the component.
     * 
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return ResponseContract::class;
    }
}