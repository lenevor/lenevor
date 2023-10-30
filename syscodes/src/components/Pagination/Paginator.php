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
 * @copyright   Copyright (c) 2019 - 2023 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Pagination;

use Countable;
use ArrayAccess;
use JsonSerializable;
use IteratorAggregate;
use Syscodes\Components\Contracts\Support\Jsonable;
use Syscodes\Components\Contracts\Support\Arrayable;

/**
 * Allows get the links of pagination of database data register.
 */
class Paginator extends AbstractPaginator implements Arrayable, Jsonable, JsonSerializable, ArrayAccess, Countable, IteratorAggregate
{
    /**
     * The last available page.
     * 
     * @var int $lastPage
     */
    protected $lastPage;
    
    /**
     * The total number of items.
     * 
     * @var int $total
     */
    protected $total;

    /**
     * Constructor. Create a new Paginator instance.
     * 
     * @param  array  $items
     * @param  int  $total
     * @param  int  $perPage
     * @param  int|null  $currentPage
     * @param  array  $options (path, query, fragment, pageName)
     * 
     * @return void
     */
    public function __construct(array $items, int $total, int $perPage, int $currentPage = null, array $options = [])
    {
        foreach ($options as $key => $value) {
            $this->{$key} = $value;
        }
        
        $this->total   = $total;
        $this->perPage = $perPage;
        
        $this->lastPage = (int) ceil($total / $perPage);
        
        $this->currentPage = $this->setCurrentPage($currentPage, $this->pageName);
        
        $this->items = $items;
        
        if ($this->path !== '/') {
            $this->path = rtrim($this->path, '/');
        }
    }
    
    /**
     * Get the current page for the request.
     * 
     * @param  int  $currentPage
     * @param  string  $pageName
     * 
     * @return int
     */
    protected function setCurrentPage(int $currentPage, string $pageName): int
    {
        $currentPage = $currentPage ?: static::resolveCurrentPage($pageName);
        
        return $this->isValidPageNumber($currentPage) ? (int) $currentPage : 1;
    }
    
    /**
     * Get the total number of items being paginated.
     * 
     * @return int
     */
    public function total(): int
    {
        return $this->total;
    }
    
    /**
     * Determine if there are pages to show.
     * 
     * @return bool
     */
    public function hasPages(): bool
    {
        return $this->lastPage() > 1;
    }
    
    /**
     * Determine if there are more items in the data source.
     * 
     * @return bool
     */
    public function hasMorePages(): bool
    {
        return $this->currentPage() < $this->lastPage();
    }
    
    /**
     * Get the URL for the next page.
     * 
     * @return string|null
     */
    public function nextPageUrl()
    {
        if ($this->lastPage() > $this->currentPage()) {
            return $this->url($this->currentPage() + 1);
        }
    }
    
    /**
     * Get the last page.
     * 
     * @return int
     */
    public function lastPage(): int
    {
        return $this->lastPage;
    }
    
    /**
     * Get the instance as an array.
     * 
     * @return array
     */
    public function toArray(): array
    {
        return [
            'total'         => $this->total(),
            'per_page'      => $this->perPage(),
            'current_page'  => $this->currentPage(),
            'last_page'     => $this->lastPage(),
            'next_page_url' => $this->nextPageUrl(),
            'prev_page_url' => $this->previousPageUrl(),
            'from'          => $this->firstItem(),
            'to'            => $this->lastItem(),
            'data'          => $this->items->toArray(),
        ];
    }
    
    /**
     * Convert the object into something JSON serializable.
     * 
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }
    
    /**
     * Convert the object to its JSON representation.
     * 
     * @param  int  $options
     * 
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }
}