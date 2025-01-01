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

namespace Syscodes\Components\Pagination;

/**
 * Allows get the links of pagination for know your type of state in the link.
 */
class PaginationState
{
    /**
     * Bind the pagination state resolvers using the given application container as a base.
     * 
     * @param  \Syscodes\Components\Contracts\Core\Application  $app
     * 
     * @return void
     */
    public static function resolvingUsing($app)
    {
        Paginator::viewFactoryResolver(fn () => $app['view']);
        
        Paginator::currentPathResolver(fn () => $app['request']->url());
        
        Paginator::currentPageResolver(function ($pageName = 'page') use ($app) {
            $page = $app['request']->input($pageName);
            
            if (filter_var($page, FILTER_VALIDATE_INT) !== false && (int) $page >= 1) {
                return (int) $page;
            }
            
            return 1;
        });

        Paginator::queryStringResolver(fn () => $app['request']->query());
    }
}