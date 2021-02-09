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
 * @copyright   Copyright (c) 2019 - 2021 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

/*
 * You can empty out this file, if you are certain that you match all requirements.
 */

// You can remove this if you are confident that your PHP version is sufficient

$version = '7.3.12';

if (version_compare(PHP_VERSION, $version) < 0) 
{
    if (PHP_SAPI == 'cli') {
        $string  = "\033[1;36m";
        $string .= "$version\033[0m";
        trigger_error("Your PHP version must be equal or higher than {$string} to use Lenevor Framework.".PHP_EOL, E_USER_ERROR);
    }

    exit("Your PHP version must be equal or higher than <b>{$version}</b> to use Lenevor Framework.");
}

// You can remove this if you are confident you have mbstring installed
if ( ! extension_loaded('mbstring')) 
{
    if (PHP_SAPI == 'cli') {
        $string  = "\033[1;36m";
        $string .= "mbstring\033[0m";
        trigger_error("You must enable the {$string} extension to use Lenevor Framework.".PHP_EOL, E_USER_ERROR);
    }

    exit('You must enable the <b>mbstring</b> extension to use Lenevor Framework.');
}