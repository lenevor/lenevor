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

namespace Syscodes\Components\Cache\Concerns;

/**
 * This class generate a key random.
 */
trait CacheKey
{
    /**
     * This characters your invalid.
     * 
     * @var string $invalidCharRegex 
     */
    protected static $invalidCharRegex = '/[^a-z\-_0-9.]/i';

    /**
     * The cache keyName
     * 
     * @var string $keyName
     */
    protected $keyName;

    /**
     * Returns a key name that is suitable for the cache implementation being used.
     * 
     * @return  string
     */
    public function getKeyName(): string
    {
        return $this->keyName;
    }

    /**
     * Fixes the string to remove unallowed characters.
     *
     * @param  string  $key
     * 
     * @return void
     */
    public function getFixKeyChars(string $key): void
    {
        $parts = preg_replace(static::$invalidCharRegex, '', array_slice(str_split($hash = sha1($key), 2), 0, 2));

        $this->keyName = implode(DIRECTORY_SEPARATOR, $parts).DIRECTORY_SEPARATOR.$hash;
    }

    /**
     * Magic method. 
     * 
     * Returns a key name that is suitable for the cache 
     * implementation being used.
     * 
     * @return string
     */
    public function __toString(): string
    {
        return (string) $this->keyName;
    }
}