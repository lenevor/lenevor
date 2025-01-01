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

use Closure;
use ArrayAccess;
use Traversable;
use ArrayIterator;
use IteratorAggregate;
use Syscodes\Components\Support\Arr;
use Syscodes\Components\Support\Str;
use Syscodes\Components\Support\Collection;
use Syscodes\Components\Contracts\Support\Webable;
use Syscodes\Components\Support\Traits\ForwardsCalls;

/**
 * Allows get the links of pagination of database data register.
 */
abstract class AbstractPaginator implements ArrayAccess, IteratorAggregate, Webable
{
    use ForwardsCalls;

    /**
     * The default pagination view.
     * 
     * @var string $defaultView
     */
    public static $defaultView = 'pagination::default';
    
    /**
     * The default "simple" pagination view.
     * 
     * @var string $defaultSimpleView
     */
    public static $defaultSimpleView = 'pagination::simple';
    
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
     * The query string resolver callback.
     * 
     * @var \Closure $queryStringResolver
     */
    protected static $queryStringResolver;
    
    /**
     * The URL fragment to add to all URLs.
     * 
     * @var string|null $fragment
     */
    protected $fragment;
    
    /**
     * All of the items being paginated.
     * 
     * @var array|object $items
     */
    protected $items = [];
    
    /**
     * The number of links to display on each side of current page link.
     * 
     * @var int $onEachSide
     */
    public $onEachSide = 3;
    
    /**
     * The query string variable used to store the page.
     * 
     * @var string $pageName
     */
    protected $pageName = 'page';

    /**
     * The base path to assign to all URLs.
     * 
     * @var string $path
     */
    protected $path = '/';
    
    /**
     * The number of items to be shown per page.
     * 
     * @var int $perPage
     */
    protected $perPage;
    
    /**
     * The query parameters to add to all URLs.
     * 
     * @var array $query
     */
    protected $query = [];
    
    /**
     * Determine if the given value is a valid page number.
     * 
     * @param  int  $pageNumber
     * 
     * @return bool
     */
    protected function isValidPageNumber(int $pageNumber): bool
    {
        return ($pageNumber >= 1) && (filter_var($pageNumber, FILTER_VALIDATE_INT) !== false);
    }
    
    /**
     * Get the URL for the previous page.
     * 
     * @return string|null
     */
    public function previousPageUrl()
    {
        if ($this->currentPage() > 1) {
            return $this->url($this->currentPage() - 1);
        }
    }
    
    /**
     * Create a range of pagination URLs.
     * 
     * @param  int  $start
     * @param  int  $end
     * 
     * @return array
     */
    public function getUrlRange($start, $end): array
    {
        return collect(range($start, $end))->mapKeys(function ($page) {
            return [$page => $this->url($page)];
        })->all();
    }
    
    /**
     * Get the URL for a given page number.
     * 
     * @param  int  $page
     * 
     * @return string
     */
    public function url($page): string
    {
        if ($page <= 0) {
            $page = 1;
        }
        
        // If we have any extra query string key / value pairs that need to be added
        // onto the URL, we will put them in query string form and then attach it
        // to the URL. This allows for extra information like sortings storage.
        $parameters = [$this->pageName => $page];

        if (count($this->query) > 0) {
            $parameters = array_merge($this->query, $parameters);
        }
        
        return $this->getPath()
                        .(Str::contains($this->getPath(), '?') ? '&' : '?')
                        .Arr::query($parameters)
                        .$this->buildFragment();
    }
    
    /**
     * Get / set the URL fragment to be appended to URLs.
     * 
     * @param  string|null  $fragment
     * 
     * @return static|string|null
     */
    public function fragment($fragment = null): static|string|null
    {
        if (is_null($fragment)) {
            return $this->fragment;
        }
        
        $this->fragment = $fragment;
        
        return $this;
    }
    
    /**
     * Add a set of query string values to the paginator.
     * 
     * @param  array|string  $keys
     * @param  string|null  $value
     * 
     * @return static
     */
    public function appends($keys, $value = null): static
    {
        if ( ! is_array($keys)) {
            return $this->addQuery($keys, $value);
        }
        
        foreach ($keys as $key => $value) {
            $this->addQuery($key, $value);
        }
        
        return $this;
    }
    
    /**
     * Add a query string value to the paginator.
     * 
     * @param  string  $key
     * @param  string  $value
     * 
     * @return static
     */
    protected function addQuery($key, $value): static
    {
        if ($key !== $this->pageName) {
            $this->query[$key] = $value;
        }
        
        return $this;
    }
    
    /**
     * Get the set of query string values to the paginator.
     * 
     * @return array
     */
    public function getQuery(): array
    {
        return $this->query;
    }
    
    /**
     * Build the full fragment portion of a URL.
     * 
     * @return string
     */
    protected function buildFragment(): string
    {
        return $this->fragment ? '#'.$this->fragment : '';
    }
    
    /**
     * Get the slice of items being paginated.
     * 
     * @return array
     */
    public function items(): array
    {
        return $this->items->all();
    }
    
    /**
     * Get the number of the first item in the slice.
     * 
     * @return int
     */
    public function firstItem()
    {
        if (count($this->items) > 0) {
            return (($this->currentPage - 1) * $this->perPage) + 1;
        }
    }
    
    /**
     * Get the number of the last item in the slice.
     * 
     * @return int
     */
    public function lastItem()
    {
        if (count($this->items) > 0) {
            return $this->firstItem() + $this->count() - 1;
        }
    }
    
    /**
     * Get the number of items shown per page.
     * 
     * @return int
     */
    public function perPage(): int
    {
        return $this->perPage;
    }
    
    /**
     * Determine if there are enough items to split into multiple pages.
     * 
     * @return bool
     */
    public function hasPages(): bool
    {
        return ($this->currentPage() != 1) || $this->hasMorePages();
    }
    
    /**
     * Determine if the paginator is on the first page.
     * 
     * @return bool
     */
    public function onFirstPage(): bool
    {
        return $this->currentPage() <= 1;
    }
    
    /**
     * Get the current page.
     * 
     * @return int
     */
    public function currentPage(): int
    {
        return $this->currentPage;
    }
    
    /**
     * Get the query string variable used to store the page.
     * 
     * @return string
     */
    public function getPageName(): string
    {
        return $this->pageName;
    }
    
    /**
     * Set the query string variable used to store the page.
     * 
     * @param  string  $name
     * 
     * @return static
     */
    public function setPageName(string $name): static
    {
        $this->pageName = $name;
        
        return $this;
    }
    
    /**
     * Set the number of links to display on each side of current page link.
     * 
     * @param  int  $count
     * 
     * @return static
     */
    public function onEachSide(int $count): static
    {
        $this->onEachSide = $count;
        
        return $this;
    }
    
    /**
     * Get the base path to assign to all URLs.
     * 
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }
    
    /**
     * Set the base path to assign to all URLs.
     * 
     * @param  string  $path
     * 
     * @return static
     */
    public function setPath(string $path): static
    {
        $this->path = $path;
        
        return $this;
    }
    
    /**
     * Resolve the current request path or return the default value.
     * 
     * @param  string  $default
     * 
     * @return string
     */
    public static function resolveCurrentPath($default = '/')
    {
        if (isset(static::$currentPathResolver)) {
            return call_user_func(static::$currentPathResolver);
        }
        
        return $default;
    }
    
    /**
     * Set the current request path resolver callback.
     * 
     * @param  \Closure  $resolver
     * 
     * @return void
     */
    public static function currentPathResolver(Closure $resolver): void
    {
        static::$currentPathResolver = $resolver;
    }
    
    /**
     * Resolve the current page or return the default value.
     * 
     * @param  string  $pageName
     * @param  int  $default
     * 
     * @return int
     */
    public static function resolveCurrentPage($pageName = 'page', $default = 1)
    {
        if (isset(static::$currentPageResolver)) {
            return call_user_func(static::$currentPageResolver, $pageName);
        }
        
        return $default;
    }
    
    /**
     * Set the current page resolver callback.
     * 
     * @param  \Closure  $resolver
     * 
     * @return void
     */
    public static function currentPageResolver(Closure $resolver): void
    {
        static::$currentPageResolver = $resolver;
    }
    
    /**
     * Get an instance of the view factory from the resolver.
     * 
     * @return \Syscodes\Components\Contracts\View\Factory
     */
    public static function viewFactory()
    {
        return call_user_func(static::$viewFactoryResolver);
    }
    
    /**
     * Set the view factory resolver callback.
     * 
     * @param  \Closure  $resolver
     * 
     * @return void
     */
    public static function viewFactoryResolver(Closure $resolver): void
    {
        static::$viewFactoryResolver = $resolver;
    }

     /**
     * Resolve the query string or return the default value.
     *
     * @param  string|array|null  $default
     * @return string
     */
    public static function resolveQueryString($default = null)
    {
        if (isset(static::$queryStringResolver)) {
            return (static::$queryStringResolver)();
        }

        return $default;
    }

    /**
     * Set with query string resolver callback.
     *
     * @param  \Closure  $resolver
     * @return void
     */
    public static function queryStringResolver(Closure $resolver)
    {
        static::$queryStringResolver = $resolver;
    }
    
    /**
     * Set the default pagination view.
     * 
     * @param  string  $view
     * 
     * @return void
     */
    public static function defaultView($view): void
    {
        static::$defaultView = $view;
    }
    
    /**
     * Set the default "simple" pagination view.
     * 
     * @param  string  $view
     * 
     * @return void
     */
    public static function defaultSimpleView($view): void
    {
        static::$defaultSimpleView = $view;
    }
    
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
        return $this->items->isEmpty();
    }
    
    /**
     * Determine if the list of items is not empty.
     * 
     * @return bool
     */
    public function isNotEmpty(): bool
    {
        return $this->items->isNotEmpty();
    }
    
    /**
     * Get the number of items for the current page.
     * 
     * @return int
     */
    public function count(): int
    {
        return $this->items->count();
    }
    
    /**
     * Get the paginator's underlying collection.
     * 
     * @return \Syscodes\Components\Support\Collection
     */
    public function getCollection()
    {
        return $this->items;
    }
    
    /**
     * Set the paginator's underlying collection.
     * 
     * @param  \Syscodes\Components\Support\Collection  $collection
     * 
     * @return static
     */
    public function setCollection(Collection $collection): static
    {
        $this->items = $collection;
        
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
		return $this->items->has($offset);
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
		return $this->items->get($offset);
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
	    $this->items->put($offset, $value);
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
		$this->items->erase($offset);
	}
    
    /**
     * Render the contents of the paginator to HTML.
     * 
     * @return string
     */
    public function toHtml(): string
    {
        return (string) $this->render();
    }
    
    /**
     * Magic method.
     * 
     * Make dynamic calls into the collection.
     * 
     * @param  string  $method
     * @param  array  $parameters
     * 
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->forwardCallTo($this->getCollection(), $method, $parameters);
    }
    
    /**
     * Magic method.
     * 
     * Render the contents of the paginator when casting to string.
     * 
     * @return string
     */
    public function __toString(): string
    {
        return (string) $this->render();
    }
}