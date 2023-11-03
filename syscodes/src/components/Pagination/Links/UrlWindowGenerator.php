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
}