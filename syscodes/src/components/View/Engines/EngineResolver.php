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

namespace Syscodes\Components\View\Engines;

use Closure;
use InvalidArgumentException;

/**
 * Loader an engine resolver.
 */
class EngineResolver
{
    /**
     * The array of engine resolvers.
     * 
     * @var array $resolvers
     */
    protected $resolvers = [];

    /**
     * The resolved engine instances.
     * 
     * @var array $resolved
     */
    protected $resolved = [];

    /**
     * Register a new engine resolver.
     * 
     * @param  string  $engine
     * @param  \Closure  $resolver
     * 
     * @return void
     */
    public function register($engine, Closure $resolver): void
    {
        unset($this->resolved[$engine]);
        
        $this->resolvers[$engine] = $resolver;
    }

    /**
     * Resolver an engine instance.
     * 
     * @param  string  $engine
     * 
     * @return \Syscodes\Components\Contracts\View\Engine
     * 
     * @throws \InvalidArgumentException
     */
    public function resolve($engine)
    {
        if (isset($this->resolved[$engine])) {
            return $this->resolved[$engine];
        }

        if (isset($this->resolvers[$engine])) {
            return $this->resolved[$engine] = call_user_func($this->resolvers[$engine]);
        }

        throw new InvalidArgumentException("Engine [{$engine}] not found");
    }
}