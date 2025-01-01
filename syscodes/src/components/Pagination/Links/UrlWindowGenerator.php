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
        if ( ! $this->hasPages()) {
            return [
                'first' => null,
                'slider' => null,
                'last' => null
            ];
        }

        $onEachSide = $this->paginator->onEachSide;
        
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
            'first'  => $this->paginator->getUrlRange(1, $this->lastPage()),
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
            'first'  => $this->paginator->getUrlRange(1, $win + 2),
            'slider' => null,
            'last'   => $this->paginator->getUrlRange($this->lastPage() - 1, $this->lastPage()),
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
        $last = $this->paginator->getUrlRange(
            $this->lastPage() - ($win + 2),
            $this->lastPage()
        );
        
        return [
            'first'  => $this->paginator->getUrlRange(1, 2),
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
        $slider = $this->paginator->getUrlRange(
            $this->currentPage() - $onEachSide,
            $this->currentPage() + $onEachSide
        );
        
        return [
            'first'  => $this->paginator->getUrlRange(1, 2),
            'slider' => $slider,
            'last'   => $this->paginator->getUrlRange($this->lastPage() - 1, $this->lastPage()),
        ];
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