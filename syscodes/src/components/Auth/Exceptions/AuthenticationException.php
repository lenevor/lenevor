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

namespace Syscodes\Components\Auth\Exceptions;

use Exception;

/**
 *  AutheticationException.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class AuthenticationException extends Exception
{
    /**
     * All of the guards that were checked.
     * 
     * @var array
     */
    protected $guards;
    
    /**
     * The path the user should be redirected to.
     * 
     * @var string|null
     */
    protected $redirectTo;
    
    /**
     * Constructor. Create a new authentication exception.
     * 
     * @param  string  $message
     * @param  array  $guards
     * @param  string|null  $redirectTo
     * 
     * @return void
     */
    public function __construct(string $message = 'Unauthenticated', array $guards = [], $redirectTo = null)
    {
        parent::__construct($message);
        
        $this->guards     = $guards;
        $this->redirectTo = $redirectTo;
    }
    
    /**
     * Get the guards that were checked.
     * 
     * @return array
     */
    public function guards(): array
    {
        return $this->guards;
    }
    
    /**
     * Get the path the user should be redirected to.
     * 
     * @return string|null
     */
    public function redirectTo()
    {
        return $this->redirectTo;
    }
}