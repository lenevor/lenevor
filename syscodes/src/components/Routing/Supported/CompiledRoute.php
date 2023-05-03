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
 * @copyright   Copyright (c) 2019 - 2023 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Routing\Supported;

use Serializable;
use BadMethodCallException;

final class CompiledRoute implements Serializable
{
    /**
     * Get the prefix of the path.
     * 
     * @var string $prefix
     */
    protected string $prefix;

    /**
     * Get the variables.
     * 
     * @var array $variables
     */
    protected array $variables;

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