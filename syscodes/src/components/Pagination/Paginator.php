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
            'elements'  => $this->elements(),
        ]));
    }
    
    /**
     * Get the array of elements to pass to the view.
     * 
     * @return array
     */
    protected function elements(): array
    {
        $win = $this->getUrlWindow();
        
        return array_filter([
            $win['first'],
            is_array($win['slider']) ? '...' : null,
            $win['slider'],
            is_array($win['last']) ? '...' : null,
            $win['last'],
        ]);
    }
    
    /**
     * Get the window of URLs to be shown.
     * 
     * @param  int  $onEachSide
     * 
     * @return array
     */
    public function getUrlWindow(int $onEachSide = 3): array 
    {
        if ( ! $this->hasPages()) {
            return ['first' => null, 'slider' => null, 'last' => null];
        }
        
        $win = $onEachSide * 2;
        
        if ($this->lastPage() < ($win + 6)) {
            return $this->getSmallSlider();
        }
        
        if ($this->currentPage() <= $win) {
            return $this->getSliderTooCloseToBeginning($win);
        } else if ($this->currentPage() > ($this->lastPage() - $win)) {
            return $this->getSliderTooCloseToEnding($win);
        }
        
        return $this->getFullSlider($onEachSide);
    }
    
    /**
     * Get the slider of URLs there are not enough pages to slide.
     * 
     * @return array
     */
    protected function getSmallSlider(): array
    {
        return [
            'first'  => $this->getUrlRange(1, $this->lastPage()),
            'slider' => null,
            'last'   => null,
        ];
    }
    
    /**
     * Get the slider of URLs when too close to beginning of window.
     * 
     * @param  int  $win
     * 
     * @return array
     */
    protected function getSliderTooCloseToBeginning($win): array
    {
        return [
            'first'  => $this->getUrlRange(1, $win + 2),
            'slider' => null,
            'last'   => $this->getUrlRange($this->lastPage() - 1, $this->lastPage()),
        ];
    }
    
    /**
     * Get the slider of URLs when too close to ending of window.
     * 
     * @param  int  $win
     * 
     * @return array
     */
    protected function getSliderTooCloseToEnding($win): array
    {
        $last = $this->getUrlRange(
            $this->lastPage() - ($win + 2),
            $this->lastPage()
        );
        
        return [
            'first'  => $this->getUrlRange(1, 2),
            'slider' => null,
            'last'   => $last,
        ];
    }
    
    /**
     * Get the slider of URLs when a full slider can be made.
     * 
     * @param  int  $onEachSide
     * 
     * @return array
     */
    protected function getFullSlider($onEachSide): array
    {
        $slider = $this->getUrlRange(
            $this->currentPage() - $onEachSide,
            $this->currentPage() + $onEachSide
        );
        
        return [
            'first'  => $this->getUrlRange(1, 2),
            'slider' => $slider,
            'last'   => $this->getUrlRange($this->lastPage() - 1, $this->lastPage()),
        ];
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