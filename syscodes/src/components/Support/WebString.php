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

namespace Syscodes\Components\Support;

use Syscodes\Components\Contracts\Support\Webable;

/**
 * Uses a html string for show data.
 */
class WebString implements Webable
{
    /**
     * The HTML string.
     * 
     * @var string $html
     */
    protected $html;

    /**
     * Constructor. Create a new WebString class instance.
     * 
     * @param  string  $html
     * 
     * @return void
     */
    public function __construct($html)
    {
        $this->html = $html;
    }

    /**
     * Get the HTML string.
     * 
     * @return string
     */
    public function toHtml(): string
    {
        return $this->html;
    }

    /**
     * Magic method.
     * 
     * Get the HTML string.
     * 
     * @return string
     */
    public function __toString(): string
    {
        return $this->toHtml();
    }
}