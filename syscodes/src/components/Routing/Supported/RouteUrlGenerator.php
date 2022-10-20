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

namespace Syscodes\Components\Routing\Supported;

/**
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class RouteUrlGenerator
{
    /**
     * The named parameter defaults.
     * 
     * @var array
     */
    public $defaultParameters = [];
    
    /**
     * Characters that should not be URL encoded.
     * 
     * @var array $dontEncode
     */
    protected $dontEncode = [
        '%2F' => '/',
        '%40' => '@',
        '%3A' => ':',
        '%3B' => ';',
        '%2C' => ',',
        '%3D' => '=',
        '%2B' => '+',
        '%21' => '!',
        '%2A' => '*',
        '%7C' => '|',
    ];

    /**
     * The request instance.
     * 
     * @var \Syscodes\Components\Http\Request
     */
    protected $request;

    /**
     * The URL generator instance.
     * 
     * @var \Syscodes\Components\Routing\UrlGenerator
     */
    protected $url;

    /**
     * Constructor. Create a new RouteUrlGenerator class instance.
     * 
     * @param  \Syscodes\Components\Routing\Supported\UrlGenerator  $url
     * @param  \Syscodes\Components\Http\Request
     * 
     * @return void
     */
    public function __construct($url, $request)
    {
        $this->url     = $url;
        $this->request = $request;        
    }
}
