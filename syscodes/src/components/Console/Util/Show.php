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

namespace Syscodes\Components\Console\Util;

use Syscodes\Components\Console\Helper\MultiList;
use Syscodes\Components\Console\Helper\SingleList;
use Syscodes\Components\Contracts\Console\Output\Output as OutputInterface;

/**
 * Format and render to a list of items.
 */
class Show
{
    /**
     * Show the single List.
     * 
     * @param  mixed  $data  The list of data
     * @param  string  $title  The title of list
     * @param  array  $options  The options for list of data
     * @param  \Syscodes\Components\Contracts\Console\Output\Output  $output  The output interface implemented
     * 
     * @return void
     */
    public static function sList($data, string $title = '', array $options = [], OutputInterface $output): void
    {
        SingleList::show($data, $title, $options, $output);
    }

    /**
     * Show the multi List.
     * 
     * @param  array  $data  The list of data
     * @param  array  $options  The options for list of data
     * @param  \Syscodes\Components\Contracts\Console\Output\Output  $output  The output interface implemented
     * 
     * @return void
     */
    public static function mList($data, array $options = [], OutputInterface $output): void
    {
        MultiList::show($data, $options, $output);
    }
}