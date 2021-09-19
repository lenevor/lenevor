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
 * @copyright   Copyright (c) 2019 - 2021 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Console\Util;

use Syscodes\Contracts\Console\Output as OutputInterface;

/**
 * Format and render to a list of items.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class Show
{
    /**
     * Show the single List.
     * 
     * @param  mixed  $data  The list of data
     * @param  string  $title  The title of list
     * @param  array  $options  The options for list of data
     * @param  \Syscodes\Contracts\Console\Output  $output  The output interface implemented
     * 
     * @return int|string
     */
    public static function sList($data, string $title = '', array $options = [], OutputInterface $output)
    {
        return SingleList::show($data, $title, $options, $output);
    }
}