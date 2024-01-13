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
 * @copyright   Copyright (c) 2019 - 2024 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Contracts\Pagination;

/**
 * A paginator for show all the register of a database.
 */
interface Paginator extends SimplePaginator
{
    /**
     * Create a range of pagination URLs.
     * 
     * @param  int  $start
     * @param  int  $end
     * 
     * @return array
     */
    public function getUrlRange(int $start, int $end): array;

    /**
     * Get the total number of items being paginated.
     * 
     * @return int
     */
    public function total(): int;

    /**
     * Get the last page.
     * 
     * @return int
     */
    public function lastPage(): int;
}