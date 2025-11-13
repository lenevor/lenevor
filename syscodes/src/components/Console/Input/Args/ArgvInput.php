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

namespace Syscodes\Components\Console\Input;

use RuntimeException;
use Syscodes\Components\Console\Input\InputDefinition;

/**
 * This class represents an input  coming from the CLI arguments.
 */
class ArgvInput extends Input
{
    /**
     * @var string[] $tokens
     */
    protected $tokens;

    /**
     * @var string[] $parsed
     */
    protected $parsed;

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
     * Processes command line arguments.
     * 
     * @return void
     */
    protected function parse(): void
    {
        $parseOptions = true;
        $this->parsed = $this->getTokens();
        
        while (null !== $token = array_shift($this->parsed)) {
            if ($parseOptions && '--' == $token) {
                $parseOptions = false;
            } elseif ($parseOptions && 0 === strpos($token, '--')) {
                $this->parseLongOption($token);
            } elseif ($parseOptions && '-' === $token[0] && '-' !== $token) {
                $this->parseShortOption($token);
            }
        }
    }
    
    /**
     * Parses a short option.
     * 
     * @param  string  $token  The current token.
     * 
     * @return void
     */
    private function parseShortOption($token)
    {
        $name = substr($token, 1);
        
        if (strlen($name) > 1) {
            if ($this->definition->hasShortcut($name[0]) && $this->definition->getOptionForShortcut($name[0])->isAcceptValue()) {
                // an option with a value (with no space)
                $this->addShortOption($name[0], substr($name, 1));
            } else {
                $this->parseShortOptionSet($name);
            }
        } else {
            $this->addShortOption($name, null);
        }
    }
    
    /**
     * Adds a short option value.
     * 
     * @param  string  $shortcut  The short option key
     * @param  mixed  $value  The value for the option
     * 
     * @return void
     * 
     * @throws \RuntimeException  When option given doesn't exist
     */
    private function addShortOption($shortcut, $value)
    {
        if ( ! $this->definition->hasShortcut($shortcut)) {
            throw new RuntimeException(sprintf('The "-%s" option does not exist', $shortcut));
        }
        
        $this->addLongOption($this->definition->getOptionForShortcut($shortcut)->getName(), $value);
    }
    
    /**
     * Parses a short option set.
     * 
     * @param  string  $name  The current token
     * 
     * @return void
     * 
     * @throws \RuntimeException When option given doesn't exist
     */
    private function parseShortOptionSet($name)
    {
        $len = strlen($name);
        
        for ($i = 0; $i < $len; $i++) {
            if (!$this->definition->hasShortcut($name[$i])) {
                throw new RuntimeException(sprintf('The "-%s" option does not exist', $name[$i]));
            }
            
            $option = $this->definition->getOptionForShortcut($name[$i]);
            
            if ($option->isAcceptValue()) {
                $this->addLongOption($option->getName(), $i === $len - 1 ? null : substr($name, $i + 1));
                
                break;
            } else {
                $this->addLongOption($option->getName(), null);
            }
        }
    }
    
    /**
     * Parses a long option.
     * 
     * @param  string  $token  The current token
     *
     * @return void 
     */
    private function parseLongOption($token)
    {
        $name = substr($token, 2);
        
        if (false !== $pos = strpos($name, '=')) {
            if (0 === strlen($value = substr($name, $pos + 1))) {
                array_unshift($this->parsed, $value);
            }
            
            $this->addLongOption(substr($name, 0, $pos), $value);
        } else {
            $this->addLongOption($name, null);
        }
    }
    
    /**
     * Adds a long option value.
     * 
     * @param  string  $name  The long option key
     * @param  mixed  $value  The value for the option
     * 
     * @return void
     * 
     * @throws \RuntimeException  When option given doesn't exist
     */
    private function addLongOption($name, $value)
    {
        $this->options[$name] = $value;
    }

    /**
     * Gets the tokens of the console arguments.
     * 
     * @return string[]
     */
    public function getTokens(): array
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
     * Gets the first argument from unprocessed parameters (not parsed).
     * 
     * @return string|null
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
     * Gets true if the unprocessed parameters (not parsed) contain a value.
     * 
     * @param  string|array  $values  The values to look for in the unprocessed parameters
     * @param  bool  $params  Just check the actual parameters, skip the ones with end of options signal (--) 
     * 
     * @return bool
     */
    public function hasParameterOption(string|array $values, bool $params = false): bool
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
     * Gets the value of a unprocessed option (not parsed).
     * 
     * @param  string|array  $values  The values to look for in the unprocessed parameters
     * @param  mixed  $default  The default value
     * @param  bool  $params  Just check the actual parameters, skip the ones with end of options signal (--)
     * 
     * @return mixed
     */
    public function getParameterOption(string|array $values, $default = false, bool $params = false): mixed
    {
        $tokens = $this->getTokens();
        
        foreach ((array) $values as $value) {
            for (reset($tokens); null !== key($tokens); next($tokens)) {
                $token = current($tokens);
                
                if ($params && '--' === $token) {
                    // end of options (--) signal reached, stop now
                    return $default;
                }
                
                // Long/short option with value in the next argument
                if ($token === $value) {
                    $next = next($tokens);
                    
                    return ($next && '--' !== $next) ? $next : null;
                }
                
                // Long option with =
                if (0 === strpos($token, $value.'=')) {
                    return substr($token, strlen($value) + 1);
                }
                
                // Short option
                if (strlen($token) > 2 && '-' === $token[0] && '-' !== $token[1] && 0 === strpos($token, $value)) {
                    return substr($token, 2);
                }
            }
        }
        
        return $default;
    }
    
    /**
     * Magic method.
     * 
     * Returns a stringified representation of the args passed to the command.
     * 
     * @return string
     */
    public function __toString(): string
    {
        $self = $this;
        
        $tokens = array_map(function ($token) use ($self) {
            if (preg_match('{^(-[^=]+=)(.+)}', $token, $match)) {
                return $match[1].$self->escapeToken($match[2]);
            }
            
            if ($token && $token[0] !== '-') {
                return $self->escapeToken($token);
            }
            
            return $token;
        }, $this->tokens);
        
        return implode(' ', $tokens);
    }
}