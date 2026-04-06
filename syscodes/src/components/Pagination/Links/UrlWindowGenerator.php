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

namespace Syscodes\Components\Pagination\Links;

use Syscodes\Components\Contracts\Pagination\Paginator;

/**
 * Allows the generation of urls in a pagination.
 */
class UrlWindowGenerator
{
    /**
     * The paginator implementation.
     * 
     * @var \Syscodes\Components\Contracts\Pagination\Paginator
     */
    protected $paginator;

    /**
     * Constructor. Create a new UrlWindowGenerator instance.
     * 
     * @param  \Syscodes\Components\Contracts\Pagination\Paginator  $paginator
     * 
     * @return void
     */
    public function __construct(Paginator $paginator)
    {
        $this->paginator = $paginator;
    }
    
    /**
     * Create a new URL window instance.
     * 
     * @param  \Syscodes\Components\Contracts\Pagination\Paginator  $paginator
     * 
     * @return array
     */
    public static function make(Paginator $paginator): array
    {
        return (new static($paginator))->get();
    }
    
    /**
     * Get the window of URLs to be shown.
     * 
     * @return array
     */
    public function get(): array 
    {
        $onEachSide = $this->paginator->onEachSide;
        
        $window = $onEachSide * 2;
        
        if ($this->lastPage() < ($window + 8)) {
            return $this->getSmallSlider();
        }
        
        return $this->getUrlSlider($onEachSide);
    }
    
    /**
     * Get the slider of URLs there are not enough pages to slide.
     * 
     * @return array
     */
    protected function getSmallSlider(): array
    {
        return [
            'first'  => $this->paginator->getUrlRange(1, $this->lastPage()),
            'slider' => null,
            'last'   => null,
        ];
    }
    
    /**
     * Get the slider of URLs when a full slider can be made.
     * 
     * @param  int  $onEachSide
     * 
     * @return array
     */
    protected function getUrlSlider($onEachSide): array
    {
        $window = $onEachSide + 4;

        if ( ! $this->hasPages()) {
            return [
                'first' => null,
                'slider' => null,
                'last' => null
            ];
        }

        // If the current page is very close to the beginning of the page range, we will
        // just render the beginning of the page range, followed by the last 2 of the
        // links in this list.
        if ($this->currentPage() <= $window) {
            return $this->getSliderTooCloseToBeginning($window, $onEachSide);
        } 
        // If the current page is close to the ending of the page range we will just get
        // this first couple pages, followed by a larger window of these ending pages
        // since we're too close to the end of the list to create a full on slider.
        else if ($this->currentPage() > ($this->lastPage() - $window)) {
            return $this->getSliderTooCloseToEnding($window, $onEachSide);
        }
        
        // If we have enough room on both sides of the current page to build a slider we
        // will surround it with both the beginning and ending caps.
        return $this->getFullSlider($onEachSide);
    }

    /**
     * Get the slider of URLs when too close to beginning of window.
     * 
     * @param  int  $window
     * @param  int  $onEachSide
     * 
     * @return array
     */
    protected function getSliderTooCloseToBeginning($window, $onEachSide): array
    {
        return [
            'first'  => $this->paginator->getUrlRange(1, $window + $onEachSide),
            'slider' => null,
            'last'   => $this->getFinish(),
        ];
    }
    
    /**
     * Get the slider of URLs when too close to ending of window.
     * 
     * @param  int  $window
     * @param  int  $onEachSide
     * 
     * @return array
     */
    protected function getSliderTooCloseToEnding($window, $onEachSide): array
    {
        $last = $this->paginator->getUrlRange(
            $this->lastPage() - ($window + ($onEachSide - 1)),
            $this->lastPage()
        );
        
        return [
            'first'  => $this->getStart(),
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
        return [
            'first' => $this->getStart(),
            'slider' => $this->getPageUrlRange($onEachSide),
            'last' => $this->getFinish(),
        ];
    }

    /**
     * Get the page range for the current page window.
     *
     * @param  int  $onEachSide
     * 
     * @return array
     */
    public function getPageUrlRange($onEachSide): array
    {
        return $this->paginator->getUrlRange(
            $this->currentPage() - $onEachSide,
            $this->currentPage() + $onEachSide
        );
    }

    /**
     * Get the starting URLs of a pagination slider.
     *
     * @return array
     */
    public function getStart(): array
    {
        return $this->paginator->getUrlRange(1, 2);
    }

    /**
     * Get the ending URLs of a pagination slider.
     *
     * @return array
     */
    public function getFinish(): array
    {
        return $this->paginator->getUrlRange(
            $this->lastPage() - 1,
            $this->lastPage()
        );
    }
    
    /**
     * Determine if the underlying paginator being presented has pages to show.
     * 
     * @return bool
     */
    public function hasPages(): bool
    {
        return $this->paginator->lastPage() > 1;
    }
    
    /**
     * Get the current page from the paginator.
     * 
     * @return int
     */
    protected function currentPage(): int
    {
        return $this->paginator->currentPage();
    }
    
    /**
     * Get the last page from the paginator.
     * 
     * @return int
     */
    protected function lastPage(): int
    {
        return $this->paginator->lastPage();
    }
}