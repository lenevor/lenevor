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

namespace Syscodes\Components\Pagination;

use ArrayAccess;
use Countable;
use JsonSerializable;
use IteratorAggregate;
use Syscodes\Components\Contracts\Pagination\Paginator as PaginatorContract;
use Syscodes\Components\Contracts\Support\Arrayable;
use Syscodes\Components\Contracts\Support\Jsonable;
use Syscodes\Components\Pagination\Links\UrlWindowGenerator;
use Syscodes\Components\Support\Collection;

/**
 * Allows get the links of pagination of database data register.
 */
class Paginator extends AbstractPaginator implements Arrayable, Jsonable, JsonSerializable, ArrayAccess, Countable, IteratorAggregate, PaginatorContract
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
     * @param  mixed  $items
     * @param  int  $total
     * @param  int  $perPage
     * @param  int|null  $currentPage
     * @param  array  $options (path, query, fragment, pageName)
     * 
     * @return void
     */
    public function __construct($items, $total, $perPage, $currentPage = null, array $options = [])
    {
        foreach ($options as $key => $value) {
            $this->{$key} = $value;
        }
        
        $this->total       = $total;
        $this->perPage     = (int) $perPage;        
        $this->lastPage    = max((int) ceil($total / $perPage), 1);        
        $this->currentPage = $this->setCurrentPage($currentPage, $this->pageName);
        $this->path        = $this->path !== '/' ? rtrim($this->path, '/') : $this->path;
        
        $this->setItems($items);
    }

     /**
     * Set the items for the paginator.
     *
     * @param  mixed  $items
     * 
     * @return void
     */
    protected function setItems($items): void
    {
        $this->items = $items instanceof Collection ? $items : Collection::make($items);

        $this->items = $this->items->all();
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
     * Render the paginator using the given view.
     * 
     * @param  string|null  $view
     * @param  array  $data
     * 
     * @return string
     */
    public function links($view = null, $data = [])
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
        return static::viewFactory()->make($view ?: static::$defaultView, array_merge($data, [
            'paginator' => $this,
            'elements'  => $this->elements(),
        ]));
    }
    
    /**
     * Get the array of elements to pass to the view.
     * 
     * @return array
     */
    protected function elements()
    {
        $win = UrlWindowGenerator::make($this);
        
        return array_filter([
            $win['first'],
            is_array($win['slider']) ? '...' : null,
            $win['slider'],
            is_array($win['last']) ? '...' : null,
            $win['last'],
        ]);
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
            'current_page' => $this->currentPage(),
            'data' => $this->items->toArray(),
            'first_page_url' => $this->url(1),
            'from' => $this->firstItem(),
            'last_page' => $this->lastPage(),
            'last_page_url' => $this->url($this->lastPage()),
            'links' => $this->linkCollection()->toArray(),
            'next_page_url' => $this->nextPageUrl(),
            'path' => $this->path(),
            'per_page' => $this->perPage(),
            'prev_page_url' => $this->previousPageUrl(),
            'to' => $this->lastItem(),
            'total' => $this->total(),
        ];
    }
    
    /**
     * Convert the object into something JSON serializable.
     * 
     * @return mixed
     */
    public function jsonSerialize(): mixed
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
    public function toJson($options = 0): string
    {
        return json_encode($this->jsonSerialize(), $options);
    }
}