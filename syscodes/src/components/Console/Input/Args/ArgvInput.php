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

namespace Syscodes\Components\Console\Input;

use Syscodes\Components\Console\Input\InputDefinition;

/**
 * This class represents an input  coming from the CLI arguments.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class ArgvInput extends Input
{
    /**
     * @var string[] $tokens
     */
    protected $tokens;

    /**
     * Constructor. Create a new ArgvInput instance.
     * 
     * @param  array|null  $argv
     * @param  \Syscodes\Components\Console\Input\InputDefinition|null  $definition
     * 
     * @return void
     */
    public function __construct(array $argv = null, InputDefinition $definition = null)
    {
        $argv = $argv ?? $_SERVER['argv'] ?? [];

        array_shift($argv);

        $this->tokens = $argv;

        parent::__construct($definition);
    }

    /**
     * Gets the tokens of the console arguments.
     * 
     * @return string[]
     */
    public function getTokens()
    {
        return $this->tokens;
    }

    /**
     * Sets the tokens of the console arguments.
     * 
     * @param  array  $tokens
     * 
     * @return void
     */
    public function setTokens(array $tokens): void
    {
        $this->tokens = $tokens;
    }

    /**
     * Checks whether the console arguments contain a given token.
     * 
     * @param  string  $token  The token to search
     * 
     * @return bool  Returns `true` if the arguments contain the token and `false` otherwise
     */
    public function hasTokens($token): bool
    {
        return in_array($token, $this->tokens);
    }


    /**
     * {@inheritdoc}
     */
    public function getFirstArgument()
    {
        $tokens = $this->getTokens();
        
        foreach ($tokens as $token) {
            if ($token && '-' === $token[0]) {
                continue;
            }
            
            return $token;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function hasParameterOption($values, bool $params = false): bool
    {
        $tokens = $this->getTokens();
        
        foreach ($tokens as $token) {
            // end of options (--) signal reached, stop now
            if ($params && '--' === $token) {
                return false;
            }

            foreach ((array) $values as $value) {
                if ($token === $value || 0 === strpos($token, $value.'=')) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameterOption($values, $default = false, bool $params = false)
    {
        $tokens = $this->getTokens();
        
        foreach ((array) $values as $value) {
            for (\reset($tokens); null !== \key($tokens); \next($tokens)) {
                $token = \current($tokens);
                
                if ($params && '--' === $token) {
                    // end of options (--) signal reached, stop now
                    return $default;
                }
                
                // Long/short option with value in the next argument
                if ($token === $value) {
                    $next = \next($tokens);
                    
                    return ($next && '--' !== $next) ? $next : null;
                }
                
                // Long option with =
                if (0 === \strpos($token, $value.'=')) {
                    return \substr($token, \strlen($value) + 1);
                }
                
                // Short option
                if (\strlen($token) > 2 && '-' === $token[0] && '-' !== $token[1] && 0 === \strpos($token, $value)) {
                    return \substr($token, 2);
                }
            }
        }
        
        return $default;
    }
}