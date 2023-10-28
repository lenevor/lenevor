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

use ArrayAccess;
use Traversable;
use ArrayIterator;
use IteratorAggregate;
use Syscodes\Components\Support\Arr;

/**
 * Allows get the links of pagination of database data register.
 */
abstract class AbstractPaginator implements ArrayAccess, IteratorAggregate
{
    /**
     * The default pagination view.
     * 
     * @var string $defaultView
     */
    public static $defaultView = 'Resources/Views/Default';
    
    /**
     * The default "simple" pagination view.
     * 
     * @var string $defaultSimpleView
     */
    public static $defaultSimpleView = 'Resources/Views/Simple';
    
    /**
     * The view factory resolver callback.
     * 
     * @var \Closure $viewFactoryResolver
     */
    protected static $viewFactoryResolver;
    
    /**
     * The current page being "viewed".
     * 
     * @var int $currentPage
     */
    protected $currentPage;

    /**
     * The current page resolver callback.
     * 
     * @var \Closure $currentPageResolver
     */
    protected static $currentPageResolver;
    
    /**
     * The current path resolver callback.
     * 
     * @var \Closure $currentPathResolver
     */
    protected static $currentPathResolver;
    
    /**
     * The URL fragment to add to all URLs.
     * 
     * @var string|null $fragment
     */
    protected $fragment;
    
    /**
     * All of the items being paginated.
     * 
     * @var array $items
     */
    protected $items = [];
    
    /**
     * The query string variable used to store the page.
     * 
     * @var string $pageName
     */
    protected $pageName = 'page';
    
    /**
     * The number of items to be shown per page.
     * 
     * @var int $perPage
     */
    protected $perPage;
    
    /**
     * The base path to assign to all URLs.
     * 
     * @var string $path
     */
    protected $path = '/';
    
    /**
     * The query parameters to add to all URLs.
     * 
     * @var array $query
     */
    protected $query = [];
    
    /**
     * Get an iterator for the items.
     * 
     * @return \ArrayIterator
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }
    
    /**
     * Determine if the list of items is empty or not.
     * 
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->items);
    }
    
    /**
     * Get the number of items for the current page.
     * 
     * @return int
     */
    public function count(): int
    {
        return count($this->items);
    }
    
    /**
     * Get the paginator's underlying collection.
     * 
     * @return array
     */
    public function getItems(): array
    {
        return $this->items;
    }
    
    /**
     * Set the paginator's underlying collection.
     * 
     * @param  array  $items
     * 
     * @return static
     */
    public function setItems(array $items): static
    {
        $this->items = $items;
        
        return $this;
    }

    /*
	|-----------------------------------------------------------------
	| ArrayAccess Methods
	|-----------------------------------------------------------------
	*/

	/**
	 * Whether or not an offset exists.
	 * 
	 * @param  mixed  $offset
	 * 
	 * @return bool
	 */
	public function offsetExists(mixed $offset): bool
	{
		return Arr::has($this->items, $offset);
	}

	/**
	 * Returns the value at specified offset.
	 * 
	 * @param  mixed  $offset
	 * 
	 * @return mixed
	 */
	public function offsetGet(mixed $offset): mixed
	{
		return Arr::get($this->items, $offset);
	}

	/**
	 * Assigns a value to the specified offset
	 * 
	 * @param  mixed  $offset
	 * @param  mixed  $value
	 * 
	 * @return void
	 */
	public function offsetSet(mixed $offset, mixed $value): void
	{
	    Arr::set($this->items, $offset, $value);
	}

	/**
	 * Unsets an offset.
	 * 
	 * @param  mixed  $offset
	 * 
	 * @return void
	 */
	public function offsetUnset(mixed $offset): void
	{
		Arr::erase($this->items, $offset);
	}
}