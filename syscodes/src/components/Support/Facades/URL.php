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

/**
 * Initialize the URL class facade.
 *
 * @author Alexander Campo <jalexcam@gmail.com>
 * 
 * @method static string current()
 * @method static string previous(mixed $fallback = false)
 * @method static string to(string $path, mixed $options = [], bool $secure = null)
 * @method static string secure(string $path, array $parameters = [])
 * @method static string asset(string $path, bool $secure = null)
 * @method static string secureAsset(string $path)
 * @method static string getScheme(bool $secure)
 * @method static void forcedSchema(string $schema)
 * @method static void forcedRoot(string $root)
 * @method static bool isValidUrl(string $path)
 * @method static \Syscodes\Components\Http\Request getRequest()
 * @method static void setRequest(\Syscodes\Components\Http\Request $request)
 * 
 * @see \Syscodes\Components\Routing\UrlGenerator
 */
class URL extends Facade
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
        return 'url';
    }
}