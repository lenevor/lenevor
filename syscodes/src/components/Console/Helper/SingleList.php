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

namespace Syscodes\Components\Console\Helper;

use Syscodes\Components\Support\Str;
use Syscodes\Components\Console\Util\FormatUtil;
use Syscodes\Components\Contracts\Console\Output\Output as OutputInterface;

/**
 * Format and render to a single list of items.
 */
final class SingleList
{
    /**
     * Displays the list of data with options.
     * 
     * @param  mixed  $data  The list of data
     * @param  string  $title  The title of list
     * @param  array  $options  The options for list of data
     * @param  \Syscodes\Components\Contracts\Console\Output  $output  The output interface implemented
     * 
     * @return int|string
     */
    public static function show($data, string $title = 'Information', array $options = [], OutputInterface $output)
    {
        $string = '';
        
        $options = array_merge([
            'leftChar'     => '  ', 
            //'sepChar'      => '  ',
            'keyMinWidth'  => 8,
            'keyStyle'     => 'white',
            'titleStyle'   => 'green',
            'ucFirst'      => false,
            'returned'     => false,
            'ucTitleWords' => true,
        ], $options);
        
        // title
        if ($title) {
            $title   = $options['ucTitleWords'] ? Str::title(trim($title)) : $title;
            $string .= FormatUtil::wrap($title, $options['titleStyle']).\PHP_EOL;
        }
        
        // Handle item list
        $string .= FormatUtil::spliceKeyValue((array) $data, $options);
        
        // Return formatted string
        if ($options['returned']) {
            return $string;
        }
        
        return $output->writeln($string);
    }
}