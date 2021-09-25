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

namespace Syscodes\Console\Helper;

use Syscodes\Contracts\Console\Output as OutputInterface;

/**
 * Format and render to a multi list of items.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
final class MultiList
{
    /**
     * Displays the multi list of data with options.
     * 
     * @param  array  $data  The list of data
     * @param  array  $options  The options for list of data
     * @param  \Syscodes\Contracts\Console\Output  $output  The output interface implemented
     * 
     * @return int|string
     */
    public static function show(array $data, array $options = [], OutputInterface $output)
    {
        $stringList = [];

        $options['returned'] = true;

        foreach ($data as $title => $list) {
            if ( ! $list) {
                continue;
            }

            $stringList[] = SingleList::show($list, (string) $title, $options, $output);
        }

        return $output->writeln(implode(PHP_EOL, $stringList));
    }
}