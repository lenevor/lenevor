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

namespace Syscodes\Components\Database\Concerns;

use Syscodes\Components\Contracts\Container\Container;
use Syscodes\Components\Support\Flowing;

/**
 * Trait CapsuleManager.
 */
trait CapsuleManager
{
    /**
     * The current globally used instance.
     * 
     * @var object $instance
     */
    protected static $instance;
    
    /**
     * The container instance.
     * 
     * @var \Syscodes\Components\Contracts\Container\Container $container
     */
    protected $container;

    /**
     * Get the container instance.
     * 
     * @param  \Syscodes\Components\Contracts\Container\Container  $container
     * 
     * @return void
     */
    protected function getContainerManager(Container $container)
    {
        $this->container = $container;
        
        if ( ! $this->container->bound('config')) {
            $this->container->instance('config', new Flowing);
        }
    }
    
    /**
     * Set this capsule instance available global.
     * 
     * @return void
     */
    public function setCapsuleGlobal(): void
    {
        static::$instance = $this;
    }
    
    /**
     * Get the container instance.
     * 
     * @return  \Syscodes\Components\Contracts\Container\Container
     */
    public function getContainer()
    {
        return $this->container;
    }
    
    /**
     * Set the container instance.
     * 
     * @param  \Syscodes\Components\Contracts\Container\Container  $container
     * 
     * @return void
     */
    public function setContainer(Container $container): void
    {
        $this->container = $container;
    }    
}