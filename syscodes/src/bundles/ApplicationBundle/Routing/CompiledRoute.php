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

use BadMethodCallException;
use Syscodes\Components\Contracts\Support\Serializable;

/**
 * Allows the compiled route for gets the pattern variables.
 */
final class CompiledRoute implements Serializable
{
    /**
     * Get the host in a regex expression.
     * 
     * @var string $hostRegex
     */
    protected ?string $hostRegex;

    /**
     * Get the host in the tokens.
     * 
     * @var array $hostTokens
     */
    protected array $hostTokens;

    /**
     * Get the host in the variables.
     * 
     * @var array $hostVariables
     */
    protected array $hostVariables;

    /**
     * Get the path of the variables.
     * 
     * @var array $pathVariables
     */
    protected array $pathVariables;

    /**
     * Get the prefix of the path.
     * 
     * @var string $prefix
     */
    protected string $prefix;

    /**
     * Get the regex of a route.
     * 
     * @var string $regex
     */
    protected string $regex;

    /**
     * Get the tokens for a route.
     * 
     * @var array $tokens
     */
    protected array $tokens;

    /**
     * Get the variables.
     * 
     * @var array $variables
     */
    protected array $variables;

    /**
     * Constructor. Create a new CompiledRoute class instance.
     * 
     * @param string  $regex  The regular expression to use to match this route
     * @param array  $tokens  An array of tokens to use to generate URL for this route
     * @param string  $prefix  The prefix of the compiled route
     * @param array  $pathVariables  An array of path variables
     * @param string|null  $hostRegex  Host regex
     * @param array  $hostTokens  Host tokens
     * @param array  $hostVariables  An array of host variables
     * @param array  $variables An array of variables (variables defined in the path and in the host patterns)
     * 
     * @return void
     */
    public function __construct(
        string $regex,
        array $tokens,
        string $prefix,
        array $pathVariables,
        string $hostRegex = null,
        array $hostTokens = [],
        array $hostVariables = [],
        array $variables = []
    ) {
        $this->prefix = $prefix;
        $this->regex = $regex;
        $this->tokens = $tokens;
        $this->pathVariables = $pathVariables;
        $this->hostRegex = $hostRegex;
        $this->hostTokens = $hostTokens;
        $this->hostVariables = $hostVariables;
        $this->variables = $variables;
    }

    /**
     * Returns the prefix.
     * 
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * Returns the regex.
     * 
     * @return string
     */
    public function getRegex(): string
    {
        return $this->regex;
    }

    /**
     * Returns the tokens.
     * 
     * @return array
     */
    public function getTokens(): array
    {
        return $this->tokens;
    }

    /**
     * Returns the path variables.
     * 
     * @return array
     */
    public function getPathVariables(): array
    {
        return $this->pathVariables;
    }

    /**
     * Returns the host regex.
     * 
     * @return string|null
     */
    public function getHostRegex(): ?string
    {
        return $this->hostRegex;
    }

    /**
     * Returns the host tokens.
     * 
     * @return array
     */
    public function getHostTokens(): array
    {
        return $this->hostTokens;
    }

    /**
     * Returns the host variables.
     * 
     * @return array
     */
    public function getHostVariables(): array
    {
        return $this->hostVariables;
    }

    /**
     * Returns the variables.
     * 
     * @return array
     */
    public function getVariables(): array
    {
        return $this->variables;
    }

    /**
     * String representation of object.
     * 
     * @return string
     */
    final public function serialize(): string
    {
        throw new BadMethodCallException('Cannot serialize '.__CLASS__);
    }

    /**
     * Get the value of the variables onto a array.
     * 
     * @param  array  $data
     * 
     * @return void
     */
    public function __unserialize(array $data): void
    {
        $this->variables = $data['vars'];
        $this->prefix = $data['path_prefix'];
        $this->regex = $data['path_regex'];
        $this->tokens = $data['path_tokens'];
        $this->pathVariables = $data['path_vars'];
        $this->hostRegex = $data['host_regex'];
        $this->hostTokens = $data['host_tokens'];
        $this->hostVariables = $data['host_vars'];
    }
    
    /**
     * Constructs the object.
     * 
     * @param  string  $serialized
     * 
     * @return void
     */
    final public function unserialize(string $serialized): void
    {
        $this->__unserialize(unserialize($serialized, ['allowed_classes' => false]));
    }
}