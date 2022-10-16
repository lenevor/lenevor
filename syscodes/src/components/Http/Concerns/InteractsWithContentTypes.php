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

namespace Syscodes\Components\Http\Concerns;

use Syscodes\Components\Support\Str;

/**
 * Trait InteractsWithContentTypes.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
trait InteractsWithContentTypes
{
    /**
     * Determine if the request is sending JSON.
     * 
     * @return bool
     */
    public function isJson(): bool
    {
        return Str::contains($this->header('CONTENT_TYPE') ?? '', ['/json', '+json']);
    }

    /**
     * Determine if the current request probably expects a JSON response.
     *
     * @return bool
     */
    public function expectsJson(): bool
    {
        return ($this->ajax() && ! $this->pjax()) || $this->wantsJson();
    }
    
    /**
     * Determine if the current request is asking for JSON.
     * 
     * @return bool
     */
    public function wantsJson(): bool
    {
        $acceptable = $this->getAcceptableContentTypes();
        
        return isset($acceptable[0]) && Str::contains(strtolower($acceptable[0]), ['/json', '+json']);
    }
}