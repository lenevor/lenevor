<?php

/**
 * Lenevor PHP Framework
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

namespace Syscodes\Components\Database\Concerns;

use Syscodes\Components\Support\Collection;
use Syscodes\Components\Support\Str;

/**
 * Allows wrap the given JSON path. 
 */
trait CompilesJsonPaths
{
    /**
     * Split the given JSON selector into the field and the optional 
     * path and wrap them separately.
     *
     * @param  string  $column
     * 
     * @return array
     */
    protected function wrapJsonFieldAndPath($column): array
    {
        $parts = explode('->', $column, 2);

        $field = $this->wrap($parts[0]);

        $path = count($parts) > 1 ? ', '.$this->wrapJsonPath($parts[1], '->') : '';

        return [$field, $path];
    }

    /**
     * Wrap the given JSON path.
     *
     * @param  string  $value
     * @param  string  $delimiter
     * 
     * @return string
     */
    protected function wrapJsonPath($value, $delimiter = '->'): string
    {
        $value = preg_replace("/([\\\\]+)?\\'/", "''", $value);

        $jsonPath = (new Collection(explode($delimiter, $value)))
            ->map(fn ($segment) => $this->wrapJsonPathSegment($segment))
            ->join('.');

        return "'$".(str_starts_with($jsonPath, '[') ? '' : '.').$jsonPath."'";
    }

    /**
     * Wrap the given JSON path segment.
     *
     * @param  string  $segment
     * 
     * @return string
     */
    protected function wrapJsonPathSegment($segment): string
    {
        if (preg_match('/(\[[^\]]+\])+$/', $segment, $parts)) {
            $key = Str::beforeLast($segment, $parts[0]);

            if ( ! empty($key)) {
                return '"'.$key.'"'.$parts[0];
            }

            return $parts[0];
        }

        return '"'.$segment.'"';
    }
}