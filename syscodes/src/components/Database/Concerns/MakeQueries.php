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

namespace Syscodes\Components\Database\Concerns;

use Syscodes\Components\Container\Container;
use Syscodes\Components\Database\Exceptions\RecordNotFoundException;
use Syscodes\Components\Pagination\Paginator;
use Syscodes\Components\Pagination\SimplePaginator;

/**
 * Trait MakeQueries.
 */
trait MakeQueries
{
    /**
     * Execute the query and get the first result.
     * 
     * @param  array|string|int  $columns
     * 
     * @return \Syscodes\Components\Database\Eloquent\Model|object|static|null
     */
    public function first($columns = ['*'])
    {
        return $this->limit(1)->get($columns)->first();
    }

    /**
     * Execute the query and get the first result or throw an exception.
     *
     * @param  array|string  $columns
     * @param  string|null  $message
     * 
     * @return mixed
     *
     * @throws \Syscodes\Components\Database\Exceptions\RecordNotFoundException
     */
    public function firstOrFail($columns = ['*'], $message = null)
    {
        if ( ! is_null($result = $this->first($columns))) {
            return $result;
        }

        throw new RecordNotFoundException($message ?: 'No record found for the given query.');
    }

    /**
     * Create a new Paginator instance.
     * 
     * @param  \Syscodes\Components\Support\Collection  $items
     * @param  int  $total
     * @param  int  $perPage
     * @param  int|null  $currentPage
     * @param  array  $options
     * 
     * @return \Syscodes\Components\Pagination\Paginator
     */
    protected function paginator($items, $total, $perPage, $currentPage, $options)
    {
        return Container::getInstance()->makeAssign(Paginator::class, compact(
            'items', 'total', 'perPage', 'currentPage', 'options'
        ));
    }

    /**
     * Create a new SimplePaginator instance.
     * 
     * @param  \Syscodes\Components\Support\Collection  $items
     * @param  int  $perPage
     * @param  int|null  $currentPage
     * @param  array  $options
     * 
     * @return \Syscodes\Components\Pagination\SimplePaginator
     */
    protected function simplePaginator($items, $perPage, $currentPage, $options)
    {
        return Container::getInstance()->makeAssign(SimplePaginator::class, compact(
            'items', 'perPage', 'currentPage', 'options'
        ));
    }
}