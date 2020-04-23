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
 * @since       0.1.1
 */

/*
 * You can empty out this file, if you are certain that you match all requirements.
 */

// You can remove this if you are confident that your PHP version is sufficient
if (version_compare(PHP_VERSION, '7.3.12') < 0) 
{
    trigger_error('Your PHP version must be equal or higher than 7.3.12 to use Lenevor.'.PHP_EOL, E_USER_ERROR);
}

// You can remove this if you are confident you have mbstring installed
if ( ! extension_loaded('mbstring')) 
{
    trigger_error('You must enable the mbstring extension to use Lenevor.'.PHP_EOL, E_USER_ERROR);
}