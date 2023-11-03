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
use Syscodes\Components\Contracts\Pagination\SimplePaginator as SimplePaginatorContract;

/**
 * Allows get the links of a simple pagination of database data register.
 */
class SimplePaginator extends AbstractPaginator implements Arrayable, Jsonable, JsonSerializable, ArrayAccess, Countable, IteratorAggregate, SimplePaginatorContract
{
    /**
     * Determine if there are more items in the data source.
     * 
     * @return bool
     */
    protected $hasMore;

    /**
     * Constructor. Create a new Paginator instance.
     * 
     * @param  array  $items
     * @param  int  $perPage
     * @param  int|null  $currentPage
     * @param  array  $options (path, query, fragment, pageName)
     * 
     * @return void
     */
    public function __construct(array $items, int $perPage, int $currentPage = null, array $options = [])
    {
        foreach ($options as $key => $value) {
            $this->{$key} = $value;
        }
        
        $this->perPage = $perPage;
        
        $this->currentPage = $this->setCurrentPage($currentPage);
        
        $this->hasMore = count($items) > ($this->perPage);
        
        $this->items = array_slice($items, 0, $this->perPage);
        
        if ($this->path !== '/') {
            $this->path = rtrim($this->path, '/');
        }
    }
    
    /**
     * Get the current page for the request.
     * 
     * @param  int  $currentPage
     * 
     * @return int
     */
    protected function setCurrentPage(int $currentPage): int
    {
        $currentPage = $currentPage ?: static::resolveCurrentPage();
        
        return $this->isValidPageNumber($currentPage) ? (int) $currentPage : 1;
    }
    
    /**
     * Get the URL for the next page.
     * 
     * @return string|null
     */
    public function nextPageUrl()
    {
        if ($this->hasMorePages()) {
            return $this->url($this->currentPage() + 1);
        }
    }
    
    /**
     * Render the paginator using the given view.
     * 
     * @param  string|null  $view
     * @param  array  $data
     * 
     * @return string
     */
    public function links($view = null, $data = []): string
    {
        return $this->render($view, $data);
    }
    
    /**
     * Render the paginator using the given view.
     * 
     * @param  string|null  $view
     * @param  array  $data
     * 
     * @return string
     */
    public function render($view = null, $data = [])
    {
        return static::viewFactory()->make($view ?: static::$defaultSimpleView, array_merge($data, [
            'paginator' => $this,
        ]));
    }
    
    /**
     * Manually indicate that the paginator does have more pages.
     * 
     * @param  bool  $value
     * 
     * @return static
     */
    public function hasMorePagesWhen(bool $value = true): static
    {
        $this->hasMore = $value;
        
        return $this;
    }
    
    /**
     * Determine if there are more items in the data source.
     * 
     * @return bool
     */
    public function hasMorePages(): bool
    {
        return $this->hasMore;
    }
    
    /**
     * Get the instance as an array.
     * 
     * @return array
     */
    public function toArray(): array
    {
        return [
            'per_page'      => $this->perPage(),
            'current_page'  => $this->currentPage(),
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