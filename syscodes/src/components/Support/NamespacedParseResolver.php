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

namespace Syscodes\Components\Support;

/**
 * This class represents the parse key using for generates 
 * into namespace, groups and items.
 */
class NamespacedParseResolver
{
    /**
     * A cache of the parsed items.
     * 
     * @var array $parsed
     */
    protected $parsed = [];

    /**
     * Parse the key string which should include the
     * filename as the first segment into namespace, groups
     * and item.
     * 
     * @param  string  $key
     * 
     * @return array
     */
    public function parseLine($key): array
    {
        // The stored cache key is referenced back to the 
        // parsing processing key again.
        if (isset($this->parsed[$key])) {
            return $this->parsed[$key];
        }
        
        // Here only referenced a group from an array is items.
        if (false == Str::contains($key, '::')) {
            $segments = explode('.', $key);
            
            $parsed = $this->parseSegments($segments);
        }

        return $this->parsed[$key] = $parsed;
    }
    
    /**
     * Parse an array of segments.
     * 
     * @param  array  $segments
     * 
     * @return array
     */
    protected function parseSegments(array $segments): array
    {
        // The first segment in a basic array where will always be
        // the group, does the traversal and takes that segment.
        // If there is only one total element in the array, then 
        // the entire group is simply popped out of the array and 
        // not a single item.
        $group = $segments[0];
        
        $item = count($segments) === 1
                    ? null
                    : implode('.', array_slice($segments, 1));
                    
        return [null, $group, $item];
    }

    /**
     * Set the parsed value of a key.
     * 
     * @param  string  $key
     * @param  array  $parsed
     * 
     * @return void
     */
    public function setParsedLine(string $key, array $parsed): void
    {
        $this->parsed[$key] = $parsed;
    }

    /**
     * Flush the cache of parsed key.
     *
     * @return void
     */
    public function flushParsedLines(): void
    {
        $this->parsed = [];
    }
}