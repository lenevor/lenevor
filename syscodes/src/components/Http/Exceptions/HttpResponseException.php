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
 * @author      Javier Alexander Campo M. <jalexcam@gmail.com>
 * @link        https://lenevor.com 
 * @copyright   Copyright (c) 2019-2020 Lenevor Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.1.0
 */

namespace Syscodes\Http\Exceptions;

use RuntimeException;
use Syscodes\Http\Response;

/**
 *This class is responsible for calling the Response class to be loaded 
 * in an exception depending on the error message provided by the user 
 * or the system. 
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
class HttpResponseException extends RuntimeException
{
    /**
     * Gets the response instance.
     * 
     * @var \Syscodes\Http\Response $response
     */
    protected $response;

    /**
     * Constructor. The HttpResponseException class instance.
     * 
     * @param  \Syscodes\Http\Response  $response
     * 
     * @return void
     */
    public function __construct(Response $response)
    {
        $this->response = $response;
    }

    /**
     * Get the response instance.
     * 
     * @return \Syscodes\Http\Response;
     */
    public function getResponse()
    {
        return $this->response;
    }
}