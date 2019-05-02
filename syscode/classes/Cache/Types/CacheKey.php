<?php 

namespace Syscode\Cache\Types;

use Syscode\Contracts\Cache\Key;

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
 * @author      Javier Alexander Campo M. <jalexcam@gmail.com>
 * @link        https://lenevor.com 
 * @copyright   Copyright (c) 2018-2019 Lenevor Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.3.0
 */
class CacheKey implements Key
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
     * Constructor. Create a new cache key instance.
     * 
     * @param  string  $key
     * 
     * @return string 
     */
    public function __construct($key)
    {
        $this->keyName = $this->getFixKeyChars($key);
    }

    /**
     * Returns a key name that is suitable for the cache implementation being used.
     * 
     * @return string
     */
    public function getKeyName()
    {
        return $this->keyName;
    }

    /**
     * Returns a key name that is suitable for the cache implementation being used.
     * 
     * @return string
     */
    public function __toString()
    {
        return (string) $this->keyName;
    }

    /**
     * Fixes the string to remove unallowed characters.
     *
     * @param  string  $key
     * 
     * @return string
     */
    public function getFixKeyChars($key)
    {
        return preg_replace(static::$invalidCharRegex, '', substr(sha1($key), 0, 30));
    }
}