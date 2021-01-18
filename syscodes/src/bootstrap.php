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
 * @since       0.2.1
 */

// Define the absolute paths for configured directories
if ( ! defined('APP_PATH')) define('APP_PATH', realpath($paths['path.app']).DIRECTORY_SEPARATOR);
if ( ! defined('BST_PATH')) define('BST_PATH', realpath($paths['path.bootstrap']).DIRECTORY_SEPARATOR);
if ( ! defined('CON_PATH')) define('CON_PATH', realpath($paths['path.config']).DIRECTORY_SEPARATOR);
if ( ! defined('RES_PATH')) define('RES_PATH', realpath($paths['path.resources']).DIRECTORY_SEPARATOR);
if ( ! defined('SYS_PATH')) define('SYS_PATH', realpath($paths['path.sys']).DIRECTORY_SEPARATOR);

// Call the file constants
require CON_PATH.'constants.php';
// Activate the framework class autoloader
require SYS_PATH.'src/components/Autoloader/Autoloader.php';
// Call the class configuration Autoloader
require SYS_PATH.'src/components/Config/AutoloadConfig.php';

// Aliases of the class autoloader 
class_alias('Syscodes\\Autoloader', 'Autoloader');

// Define the core classes to the autoloader
(new Autoloader)
    ->initialize(new Syscodes\Config\AutoloadConfig())
    ->register();