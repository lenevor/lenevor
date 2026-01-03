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

namespace Syscodes\Components\Finder;

/**
 * Returns a regexp.
 */
class Glob
{
    /**
     * Returns a regexp which is the equivalent of the glob pattern.
     */
    public static function toRegex(string $glob, bool $strictLeadingDot = true, bool $strictWildcardSlash = true, string $delimiter = '#'): string
    {
        $firstByte = true;
        $escaping = false;
        $inCurlies = 0;
        $regex = '';
        $sizeGlob = \strlen($glob);
        for ($i = 0; $i < $sizeGlob; ++$i) {
            $car = $glob[$i];
            if ($firstByte && $strictLeadingDot && '.' !== $car) {
                $regex .= '(?=[^\.])';
            }

            $firstByte = '/' === $car;

            if ($firstByte && $strictWildcardSlash && isset($glob[$i + 2]) && '**' === $glob[$i + 1].$glob[$i + 2] && (!isset($glob[$i + 3]) || '/' === $glob[$i + 3])) {
                $car = '[^/]++/';
                if (!isset($glob[$i + 3])) {
                    $car .= '?';
                }

                if ($strictLeadingDot) {
                    $car = '(?=[^\.])'.$car;
                }

                $car = '/(?:'.$car.')*';
                $i += 2 + isset($glob[$i + 3]);

                if ('/' === $delimiter) {
                    $car = str_replace('/', '\\/', $car);
                }
            }

            if ($delimiter === $car || '.' === $car || '(' === $car || ')' === $car || '|' === $car || '+' === $car || '^' === $car || '$' === $car) {
                $regex .= "\\$car";
            } elseif ('*' === $car) {
                $regex .= $escaping ? '\\*' : ($strictWildcardSlash ? '[^/]*' : '.*');
            } elseif ('?' === $car) {
                $regex .= $escaping ? '\\?' : ($strictWildcardSlash ? '[^/]' : '.');
            } elseif ('{' === $car) {
                $regex .= $escaping ? '\\{' : '(';
                if (!$escaping) {
                    ++$inCurlies;
                }
            } elseif ('}' === $car && $inCurlies) {
                $regex .= $escaping ? '}' : ')';
                if (!$escaping) {
                    --$inCurlies;
                }
            } elseif (',' === $car && $inCurlies) {
                $regex .= $escaping ? ',' : '|';
            } elseif ('\\' === $car) {
                if ($escaping) {
                    $regex .= '\\\\';
                    $escaping = false;
                } else {
                    $escaping = true;
                }

                continue;
            } else {
                $regex .= $car;
            }
            $escaping = false;
        }

        return $delimiter.'^'.$regex.'$'.$delimiter;
    }
}