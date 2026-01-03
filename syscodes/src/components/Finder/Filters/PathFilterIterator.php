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

namespace Syscodes\Components\Finder\Filters;

/**
 * Allows the filters files by path patterns.
 */
class PathFilterIterator extends MultiFilterIterator
{
    /**
     * Filters the iterator values.
     * 
     * @return bool
     */
    public function accept(): bool
    {
        $filename = $this->current()->getRelativePathname();
        
        if ('\\' === DIRECTORY_SEPARATOR) {
            $filename = str_replace('\\', '/', $filename);
        }
        
        return $this->isAccepted($filename);
    }

    /**
     * Converts string into regexp.
     * 
     * @param  string  $value
     * 
     * @return string
     */
    protected function toRegex(string $value): string
    {
        return $this->isRegex($value) ? $value : '/'.preg_quote($value, '/').'/';
    }
}