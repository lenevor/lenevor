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
 * @copyright   Copyright (c) 2019 - 2025 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Bundles\ApplicationBundle\Routing;

/**
 * A Route describes a route and its parameters.
 */
class Route
{
    /**
     * The compiled route.
     * 
     * @var \Syscodes\Bundles\ApplicationBundle\Routing\CompiledRoute|null $compiled
     */
    protected ?CompiledRoute $compiled = null;

    /**
     * The default values pattern.
     * 
     * @var array $defaults
     */
    protected array $defaults = [];

    /**
     * The host pattern.
     * 
     * @var string $host
     */
    protected string $host = '';

    /**
     * The path pattern.
     * 
     * @var string $path
     */
    protected string $path = '/';

    /**
     * Contains the arguments pattern.
     * 
     * @var array $requirements
     */
    protected array $requirements = [];

    /**
     * Constructor. Create a new Route class instance.
     * 
     * @param  string  $path
     * @param  array  $defaults
     * @param  array  $requirements
     * @param  string|null $host
     */
    public function __construct(
        string $path,
        array $defaults = [],
        array $requirements = [],
        ?string $host = ''
    ) {
        $this->setPath($path);
        $this->setHost($host);
        $this->setDefault($defaults);
        $this->setRequirement($requirements);
    }

    /**
     * Get the path associated with the route.
     * 
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Set the path associated with the route.
     * 
     * @param  string  $pattern
     * 
     * @return static
     */
    public function setPath(string $pattern): static
    {
        $this->path = '/'.ltrim(trim($pattern), '/');
        
        $this->compiled = null;

        return $this;
    }

    /**
     * Gets the host from a route.
     * 
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * Sets the host from a route.
     * 
     * @param  string|null  $pattern
     * 
     * @return static
     */
    public function setHost(?string $pattern): static
    {
        $this->host = (string) $pattern;

        $this->compiled = null;

        return $this;
    }

    /**
     * Get a default value for the route.
     * 
     * @param  string  $key
     * 
     * @return string|null
     */
    public function getDefault(string $key): ?string
    {
        return $this->defaults[$key] ?? null;
    }

    /**
     * set a defaults value for the route.
     * 
     * @param  array  $defaults
     * 
     * @return static
     */
    public function setDefault(array $defaults): static
    {
        foreach ($defaults as $key => $value) {
            $this->defaults[$key] = $value;
        }

        $this->compiled = null;

        return $this;
    }

    /**
     * Determine if the given defaults value exists.
     * 
     * @param  string  $name
     * 
     * @return bool
     */
    public function hasDefault(string $name): bool
    {
        return array_key_exists($name, $this->defaults);
    }

    /**
     * Get the patterns of the current route.
     * 
     * @param  string  $key
     * 
     * @return string|null
     */
    public function getRequirement(string $key): ?string
    {
        return $this->requirements[$key] ?? null;
    }

    /**
     * Set the patterns of the current route.
     * 
     * @param  array  $requirements
     * 
     * @return static
     */
    public function setRequirement(array $requirements): static
    {
        foreach ($requirements as $key => $value) {
            $this->requirements[$key] = $value;
        }

        $this->compiled = null;

        return $this;
    }
    
    /**
     * Compiles the route.
     *
     * @return \Syscodes\Bundles\ApplicationBundle\Routing\RouteCompiler 
     */
    public function compile(): CompiledRoute
    {
        if (null !== $this->compiled) {
            return $this->compiled;
        }
        
        return $this->compiled = RouteCompiler::compile($this);
    }
}