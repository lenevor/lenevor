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

namespace Syscodes\Components\Contracts\Pagination;

/**
 * A simple paginator for show all the register of a database.
 */
interface SimplePaginator
{
    /**
     * Get the URL for a given page number.
     * 
     * @param  int  $page
     * 
     * @return string
     */
    public function url(int $page): string;

    /**
     * Get the URL for the next page.
     * 
     * @return string|null
     */
    public function nextPageUrl();

    /**
     * Add a set of query string values to the paginator.
     * 
     * @param  array|string  $keys
     * @param  string|null  $value
     * 
     * @return static
     */
    public function appends($keys, $value = null): static;

    /**
     * Get the slice of items being paginated.
     * 
     * @return array
     */
    public function items(): array;

    /**
     * Get the number of the first item in the slice.
     * 
     * @return int
     */
    public function firstItem();

    /**
     * Get the number of the last item in the slice.
     * 
     * @return int
     */
    public function lastItem();

    /**
     * Determine if there are enough items to split into multiple pages.
     * 
     * @return bool
     */
    public function hasPages(): bool;

    /**
     * Determine if there are more items in the data source.
     * 
     * @return bool
     */
    public function hasMorePages(): bool;

    /**
     * Get the number of items shown per page.
     * 
     * @return int
     */
    public function perPage(): int;

    /**
     * Get the current page.
     * 
     * @return int
     */
    public function currentPage(): int;

    /**
     * Get the base path to assign to all URLs.
     * 
     * @return string
     */
    public function getPath(): string;

    /**
     * Determine if the list of items is empty or not.
     * 
     * @return bool
     */
    public function isEmpty(): bool;

    /**
     * Determine if the list of items is not empty.
     *
     * @return bool
     */
    public function isNotEmpty();

    /**
     * Render the paginator using the given view.
     * 
     * @param  string|null  $view
     * @param  array  $data
     * 
     * @return string
     */
    public function render($view = null, $data = []);
}