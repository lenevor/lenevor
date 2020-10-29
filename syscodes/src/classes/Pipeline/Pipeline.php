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
 * @since       0.7.3
 */

namespace Syscodes\Pipeline;

use Closure;
use Throwable;
use RuntimeException;
use Syscodes\Contracts\Container\Container;
use Syscodes\Contracts\Pipeline\Pipeline as PipelineContract;

/**
 * Allows sending an object through several classes to perform any type 
 * of task and finally return the resulting value.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
class Pipeline implements PipelineContract
{
    /**
     * The container instance.
     * 
     * @var \Syscodes\Contracts\Container\Container $container
     */
    protected $container;

    /**
     * The method to call on each pipe.
     * 
     * @var string $method
     */
    protected $method = 'handle';

    /**
     * The object being passed through the pipeline.
     * 
     * @var mixed $passable
     */
    protected $passable;

    /**
     * The array of class pipes.
     * 
     * @var array $pipes
     */
    protected $pipes = [];

    /**
     * Constructor. Create new Pipeline class instance.
     * 
     * @param  \Syscodes\Contracts\Container\Container|null  $container  (null by default)
     * 
     * @return void
     */
    public function __construct(Container $container = null)
    {
        $this->container = $container;
    }

    /**
     * Set the given object being sent on the pipeline.
     * 
     * @param  mixed  $sender
     * 
     * @return $this
     */
    public function send($sender)
    {
        $this->passable = $sender;

        return $this;
    }

    /**
     * Set the array of pipes.
     * 
     * @param  array|mixed  $pipes
     * 
     * @return $this
     */
    public function through($pipes)
    {
        $this->pipes = is_array($pipes) ? $pipes : func_get_args();

        return $this;
    }

    /**
     * Set the method to call on the stops.
     * 
     * @param  string  $method
     * 
     * @return $this
     */
    public function method($method)
    {
        $this->method = $method;

        return $this;
    }

    /**
     * Run the pipeline with a final destination callback.
     * 
     * @param  \Closure  $destination
     * 
     * @return mixed
     */
    public function then(Closure $destination)
    {
        $pipeline = array_reduce(
            array_reverse($this->getPipes()), $this->call(), $this->prepareDestination($destination)
        );
        
        return $pipeline($this->passable);
    }

    /**
     * Get the array of configured pipes.
     * 
     * @return array
     */
    protected function getPipes()
    {
        return $this->pipes;
    }

    /**
     * Get a Closure that represents a slice of the application.
     * 
     * @return \Closure
     */
    protected function call()
    {
        return function ($stack, $pipe) {
            return function ($passable) use ($stack, $pipe) {
                try 
                {
                    if (is_callable($pipe))
                    {
                        return $pipe($passable, $stack);
                    }
                    elseif ( ! is_object($pipe))
                    {
                        [$name, $parameters] = $this->parsePipeString($pipe);

                        $pipe = $this->getContainer()->make($name);

                        $parameters = array_merge([$passable, $stack], $parameters);
                    } 
                    else 
                    {
                        $parameters = [$passable, $stack];
                    }
                    
                    $pipeline = method_exists($pipe, $this->method)
                                ? $pipe->{$this->method}(...$parameters)
                                : $pipe(...$parameters);
                                
                    return $pipeline;
                }
                catch (Throwable $e)
                {
                    return $this->handleException($passable, $e);
                }
            };
        };
    }
    
    /**
     * Parse full pipe string to get name and parameters.
     * 
     * @param  string  $pipe
     * 
     * @return array
     */
    protected function parsePipeString($pipe)
    {
        [$name, $parameters] = array_pad(explode(':', $pipe, 2), 2, []);
        
        if (is_string($parameters))
        {
            $parameters = explode(',', $parameters);
        }
        
        return [$name, $parameters];
    }

    /**
     * Get the initial slice to begin the stack call.
     * 
     * @param  \Closure  $destination
     * 
     * @return \Closure
     */
    protected function prepareDestination(Closure $destination)
    {
        return function ($passable) use ($destination) {
            try 
            {
                return $destination($passable);
            }
            catch(Throwable $e)
            {
                return $this->handleException($passable, $e);
            }
        };
    }

    /**
     * Handle the given exception.
     * 
     * @param  mixed  $passable
     * @param  \Throwable  $e
     * 
     * @return mixed
     * 
     * @throws \Throwable
     */
    protected function handleException($passable, Throwable $e)
    {
        throw $e;
    }

    /**
     * Get the container instance.
     * 
     * @return \Syscodes\Contracts\Container\Container
     * 
     * @throws \RuntimeException
     */
    protected function getContainer()
    {
        if ( ! $this->container)
        {
            throw new RuntimeException('A container instance has not been passed to the Pipeline');
        }

        return $this->container;
    }

    /**
     * set the container instance.
     * 
     * @param  \Syscodes\Contracts\Container\Container  $container
     * 
     * @return $this
     */
    protected function setContainer(Container $container)
    {
        $this->container = $container;

        return $this;
    }
}

