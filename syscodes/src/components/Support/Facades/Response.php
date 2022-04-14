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
 * @copyright   Copyright (c) 2019 - 2022 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Support\Facades;

use Syscodes\Components\Contracts\Routing\RouteResponse as ResponseContract;

/**
 * Initialize the Response class facade.
 *
 * @author Alexander Campo <jalexcam@gmail.com>
 * 
 * @method static \Syscodes\Components\Http\Response make(string $body = '', int $status = 200, array $headers = [])
 * @method static \Syscodes\Components\Http\Response noContent(string $status = 204, array $headers = [])
 * @method static \Syscodes\Components\Http\Response view(string $view, array $data = [], int $status = 200, array $headers = [])
 * @method static \Syscodes\Components\Http\Response json(mixed $data = [], int $status = 200, array $headers = [], int $options = 0)
 * @method static \Syscodes\Components\Http\Response redirectTo(string $path, int $status = 302, array $headers = [], bool $secure = null)
 * 
 * @see \Syscodes\Components\Contracts\Routing\RouteResponse
 */
class Response extends Facade
{
    /**
     * Get the registered name of the component.
     * 
     * @return string
     * 
     * @throws \RuntimeException
     */
    protected static function getFacadeAccessor(): string
    {
        return ResponseContract::class;
    }
}