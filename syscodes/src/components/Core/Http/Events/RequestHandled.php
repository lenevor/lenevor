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

namespace Syscodes\Components\Core\Http\Events;

/**
 * The request handled to when the user given order 
 */
class RequestHandled
{
    /**
     * The request instance.
     * 
     * @var \Syscodes\Components\Http\Request
     */
    public $request;
    
    /**
     * The response instance.
     * 
     * @var \Syscodes\Components\Http\Response
     */
    public $response;
    
    /**
     * Constructor. Create a new request handled instance class.
     * 
     * @param  \Syscodes\Components\Http\Request  $request
     * @param  \Syscodes\Components\Http\Response  $response
     *
     * @return void 
     */
    public function __construct($request, $response)
    {
        $this->request = $request;
        $this->response = $response;
    }
}