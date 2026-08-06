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

namespace Syscodes\Components\Support\Facades;

/**
 * Initialize the View class facade.
 * 
 * @method static \Syscodes\Components\Validation\Validator make(array $data, array $rules, array $messages = [], array $attributes = [])
 * @method static array validate(array $data, array $rules, array $messages = [], array $attributes = [])
 * @method static void extend(string $rule, \Closure|string $extension, string|null $message = null)
 * @method static void extendImplicit(string $rule, \Closure|string $extension, string|null $message = null)
 * @method static void extendDependent(string $rule, \Closure|string $extension, string|null $message = null)
 * @method static void replacer(string $rule, \Closure|string $replacer)
 * @method static void includeUnvalidatedArrayKeys()
 * @method static void excludeUnvalidatedArrayKeys()
 * @method static void resolver(\Closure $resolver)
 * @method static \Syscodes\Components\Contracts\Translation\Translator getTranslator()
 * @method static \Syscodes\Components\Validation\PresenceVerifierInterface getPresenceVerifier()
 * @method static void setPresenceVerifier(\Syscodes\Components\Validation\PresenceVerifierInterface $presenceVerifier)
 * @method static \Syscodes\Components\Contracts\Container\Container|null getContainer()
 * @method static \Syscodes\Components\Validation\Factory setContainer(\Syscodes\Components\Contracts\Container\Container $container)
 *  
 * @see \Syscodes\Components\Validation\Factory
 */
class Validator extends Facade
{
    /**
     * Get the registered name of the component.
     * 
     * @return string
     * 
     * @throws \RuntimeException
     */
    protected static function getFacadeAccessor(): string
    {
        return 'validator';
    }
}
