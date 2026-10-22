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

namespace Syscodes\Components\Support\Traits;

use Closure;
use ReflectionFunction;
use ReflectionIntersectionType;
use ReflectionUnionType;
use RuntimeException;
use Syscodes\Components\Support\Collection;
use Syscodes\Components\Support\Reflector;

/**
 * Trait ReflectsClosures.
 */
trait ReflectsClosures
{
    /**
     * Get the class name of the first parameter of the given Closure.
     *
     * @param  \Closure  $closure
     * @return string
     *
     * @throws \ReflectionException
     * @throws \RuntimeException
     */
    protected function firstClosureParameterType(Closure $closure)
    {
        $types = array_values($this->closureParameterTypes($closure));

        if ( ! $types) {
            throw new RuntimeException('The given Closure has no parameters.');
        }

        if ($types[0] === null) {
            throw new RuntimeException('The first parameter of the given Closure is missing a type hint.');
        }

        return $types[0];
    }

    /**
     * Get the class names of the first parameter of the given Closure, including union types.
     *
     * @param  \Closure  $closure
     * @return array
     *
     * @throws \ReflectionException
     * @throws \RuntimeException
     */
    protected function firstClosureParameterTypes(Closure $closure)
    {
        $reflection = new ReflectionFunction($closure);

        $types = (new Collection($reflection->getParameters()))
            ->mapWithKeys(function ($parameter) {
                if ($parameter->isVariadic()) {
                    return [$parameter->getName() => null];
                }

                return [$parameter->getName() => Reflector::getParameterClassNames($parameter)];
            })
            ->filter()
            ->values()
            ->all();

        if (empty($types)) {
            throw new RuntimeException('The given Closure has no parameters.');
        }

        if (isset($types[0]) && empty($types[0])) {
            throw new RuntimeException('The first parameter of the given Closure is missing a type hint.');
        }

        return $types[0];
    }

    /**
     * Get the class names / types of the parameters of the given Closure.
     *
     * @param  \Closure  $closure
     * @return array
     *
     * @throws \ReflectionException
     */
    protected function closureParameterTypes(Closure $closure)
    {
        $reflection = new ReflectionFunction($closure);

        return (new Collection($reflection->getParameters()))
            ->mapWithKeys(function ($parameter) {
                if ($parameter->isVariadic()) {
                    return [$parameter->getName() => null];
                }

                return [$parameter->getName() => Reflector::getParameterClassName($parameter)];
            })
            ->all();
    }

    /**
     * Get the class names / types of the return type of the given Closure.
     *
     * @param  \Closure  $closure
     * @return list<class-string>
     *
     * @throws \ReflectionException
     */
    protected function closureReturnTypes(Closure $closure)
    {
        $reflection = new ReflectionFunction($closure);

        if ($reflection->getReturnType() === null ||
            $reflection->getReturnType() instanceof ReflectionIntersectionType) {
            return [];
        }

        $types = $reflection->getReturnType() instanceof ReflectionUnionType
            ? $reflection->getReturnType()->getTypes()
            : [$reflection->getReturnType()];

        return (new Collection($types))
            ->reject(fn ($type) => $type->isBuiltin())
            ->reject(fn ($type) => in_array($type->getName(), ['static', 'self']))
            ->map(fn ($type) => $type->getName())
            ->values()
            ->all();
    }
}
