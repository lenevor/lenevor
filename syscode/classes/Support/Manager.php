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
 * @copyright   Copyright (c) 2019 Lenevor Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.4.0
 */

namespace Syscode\Support;

use Closure;
use InvalidArgumentException;

/**
 * This class manage the creation of driver based components.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
abstract class Manager
{
    /**
     * The application instance.
     * 
     * @var \Syscode\Contracts\Core\Application $app
     */
    protected $app;

    /**
     * The registered custom driver creators.
     * 
     * @var array $cumtomCreators
     */
    protected $cumtomCreators = [];

    /**
     * The array of created drivers.
     * 
     * @var array $drivers
     */
    protected $drivers = [];

    /**
     * Constructor. The Manager class instance.
     * 
     * @param  \Syscode\Contracts\Core\Application  $app
     * 
     * @return void
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Get the default driver name.
     * 
     * @return string
     */
    abstract public function getDefaultDriver();

    /**
     * Get a driver instance.
     * 
     * @param  string|null  $driver
     * 
     * @return mixed
     * 
     * @throws \InvalidArgumentException
     */
    public function driver($driver = null)
    {
        $driver = $driver ?: $this->getDefaultDriver();

        if (is_null($driver))
        {
            throw new InvalidArgumentException(sprintf('Unable to resolve NULL driver for [%s].', static::class));
        }

        if ( ! isset($this->drivers[$driver]))
        {
            $this->drivers[$driver] = $this->createDriver($driver);
        }

        return $this->drivers[$driver];
    }

    /**
     * 
     */
    protected function createDriver($driver)
    {
        
    }
}