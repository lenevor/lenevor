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

use LogicException;
use InvalidArgumentException;
use Syscodes\Components\Contracts\Console\Input\InputArgument as InputArgumentInterface;

/**
 * This class represents a command line argument.
 */
class InputArgument implements InputArgumentInterface
{
    /**
     * The default value.
     * 
     * @var mixed $default
     */
    protected $default;

    /**
     * The argument mode.
     * 
     * @var int $mode
     */
    protected int $mode;

    /**
     * Constructor. Create a new InputArgument instance.
     * 
     * @param  string  $name  The argument name
     * @param  int|null  $mode  The argument mode
     * @param  string|null  $description  The description text
     * @param  mixed  $default  The default value
     * 
     * @return void
     * 
     * @throws \InvalidArgumentException  When argument mode is not valid
     * @throws \LogicException
     */
    public function __construct(
        protected string $name, 
        ?int $mode = null,
        protected string $description = '',
        mixed $default = null
    ) {
        if (null === $mode) {
            $mode = InputArgumentInterface::OPTIONAL;
        } elseif ($mode > 7 || $mode < 1) {
            throw new InvalidArgumentException(
                sprintf('Argument mode "%s" is not valid', $mode)
            );
        }

        $this->mode = $mode;

        $this->setDefault($default);
    }

    /**
     * Gets the argument name.
     * 
     * @return string  The argument name
     */
    public function getName(): string
    {
        return $this->name;
    }
    
    /**
     * Sets the argument name.
     * 
     * @param  string  $name  The argument name
     * 
     * @return void
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * Gets the argument mode.
     * 
     * @return int
     */
    public function getMode(): int
    {
        return $this->mode;
    }

    /**
     * Sets the argument mode.
     * 
     * @param  int  $mode  The argument mode
     * 
     * @return void
     */
    public function setMode(int $mode): void
    {
        $this->mode = $mode;
    }
    
    /**
     * Gets the description text.
     * 
     * @return string  The description text
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Sets the description text.
     * 
     * @param  string  $description  The description text
     * 
     * @return void
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }
    
    /**
     * Gets the default value.
     * 
     * @return mixed
     */
    public function getDefault(): mixed
    {
        return $this->default;
    }
    
    /**
     * Sets the default value.
     * 
     * @param  mixed  $default
     * 
     * @return mixed
     * 
     * @throws \LogicException
     */
    public function setDefault(mixed $default = null): void
    {
        if (InputArgumentInterface::REQUIRED === $this->mode && null !== $default) {
            throw new LogicException('Cannot set a default value except for InputArgumentInterface::OPTIONAL mode');
        }

        if ($this->isArray()) {
            if (null === $default) {
                $default = [];
            } elseif ( ! is_array($default)) {
                throw new LogicException('Should get a default value for an array argument');
            }
        }

        $this->default = $default;
    }
    
    /**
     * Gets true if the argument is required.
     * 
     * @return bool  True if parameter mode is self::REQUIRED, false otherwise
     */
    public function isRequired(): bool
    {
        return InputArgumentInterface::REQUIRED === (InputArgumentInterface::REQUIRED & $this->mode);
    }
    
    /**
     * Gets true if the argument can take multiple values.
     * 
     * @return bool  True if mode is self::IS_ARRAY, false otherwise
     */
    public function isArray(): bool
    {
        return InputArgumentInterface::IS_ARRAY === (InputArgumentInterface::IS_ARRAY & $this->mode);
    }
}