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
 * @copyright   Copyright (c) 2019 - 2022 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Dotenv\Store\Contributors;

use InvalidArgumentException;

/**
 * Allows the read files and converting string in array.
 */
final class Reader
{
    /**
     * Read the file(s), and return their raw content.
     * 
     * @param  string[]  $filePaths
     * @param  bool  $modeEnabled  (true by default)
     * 
     * @return array 
     */
    public static function read(array $filePaths, bool $modeEnabled = true)
    {
        $output = '';

        foreach ($filePaths as $filePath) {
            $output = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES); 
            
            if ($modeEnabled) {
                break;
            }
        }

        return $output;
    }
}