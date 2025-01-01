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

namespace Syscodes\Components\Http\Concerns;

use Syscodes\Components\Database\Erostrine\Model;

/**
 * Trait InteractWithFlashData.
 */
trait InteractsWithFlashData
{
    /**
     * Retrieve an old input item.
     * 
     * @param  string|null  $key
     * @param  \Syscodes\Components\Database\Eloquent\Model|string|array|null  $default
     * 
     * @return string|array|null
     */
    public function old($key = null, $default = null)
    {
        $default = $default instanceof Model ? $default->getAttribute($key) : $default;
        
        return $this->hasSession() ? $this->session()->getOldInput($key, $default) : $default;
    }
    
    /**
     * Flash the input for the current request to the session.
     * 
     * @return void
     */
    public function flash(): void
    {
        $this->session()->flashInput($this->input());
    }
    
    /**
     * Flash only some of the input to the session.
     * 
     * @param  array|mixed  $keys
     * 
     * @return void
     */
    public function flashOnly($keys): void
    {
        $this->session()->flashInput(
            $this->only(is_array($keys) ? $keys : func_get_args())
        );
    }
    
    /**
     * Flash only some of the input to the session.
     * 
     * @param  array|mixed  $keys
     * 
     * @return void
     */
    public function flashExcept($keys): void
    {
        $this->session()->flashInput(
            $this->except(is_array($keys) ? $keys : func_get_args())
        );
    }
    
    /**
     * Flush all of the old input from the session.
     * 
     * @return void
     */
    public function flush(): void
    {
        $this->session()->flashInput([]);
    }
}