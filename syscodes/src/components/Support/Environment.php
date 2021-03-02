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

namespace Syscodes\Support;

use Syscodes\Dotenv\Repository\RepositoryCreator;
use Syscodes\Dotenv\Repository\Adapters\PutenvAdapter;

/**
 * Gets the adapter environment and value of an environment variable.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class Environment
{
    /**
     * Activate use of putenv, by default is true.
     * 
     * @var bool $enabledPutenv
     */
    protected static $enabledPutenv = true;

    /**
     * The environment repository instance.
     * 
     * @var Syscodes\Dotenv\Repository\RepositoryCreator|null $repository
     */
    protected static $repository;

    /**
     * Get the environment repository instance.
     * 
     * @return  Syscodes\Dotenv\Repository\RepositoryCreator
     */
    public static function getRepositoryCreator()
    {
        if (null === static::$repository) {
            $repository = RepositoryCreator::createDefaultAdapters();

            if (static::$enabledPutenv) {
                $repository = $repository->addAdapter(PutenvAdapter::class);
            }

            static::$repository = $repository->make();
        }

        return static::$repository;
    }

    /**
     * Gets the value of an environment variable.
     * 
     * @param  string  $key
     * @param  mixed|null  $default 
     * 
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        $value = Environment::getRepositoryCreator()->get($key);

        if ($value === null) {
            $value = $_ENV[$key] ?? $_SERVER[$key] ?? false;
        }

        if ($value === false) {
            return value($default);
        }

        // Handle any boolean values
        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return null;
        }
        
        return $value;
    }
}