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
 * @copyright   Copyright (c) 2019 - 2022 Alexander Campo <jalexcam@gmail.com>
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
     * The argument description.
     * 
     * @var string $description
     */
    protected $description;

    /**
     * The argument mode.
     * 
     * @var int $mode
     */
    protected $mode;

    /**
     * The argument name.
     * 
     * @var string $name
     */
    protected $name;

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
        string $name, 
        int $mode = null,
        string $description = null,
        $default = null
    ) {
        if (null === $mode) {
            $mode = InputArgumentInterface::OPTIONAL;
        } elseif ($mode > 7 || $mode < 1) {
            throw new InvalidArgumentException(
                sprintf('Argument mode "%s" is not valid', $mode)
            );
        }

        $this->name        = $name;
        $this->mode        = $mode;
        $this->description = $description;

        $this->setDefault($default);
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->name;
    }
    
    /**
     * {@inheritdoc}
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getMode(): int
    {
        return $this->mode;
    }

    /**
     * {@inheritdoc}
     */
    public function setMode(int $mode): void
    {
        $this->mode = $mode;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * {@inheritdoc}
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
    public function getDefault()
    {
        return $this->default;
    }
    
    /**
     * {@inheritdoc}
     */
    public function setDefault($default = null): void
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
     * {@inheritdoc}
     */
    public function isRequired(): bool
    {
        return InputArgumentInterface::REQUIRED === (InputArgumentInterface::REQUIRED & $this->mode);
    }
    
    /**
     * {@inheritdoc}
     */
    public function isArray(): bool
    {
        return InputArgumentInterface::IS_ARRAY === (InputArgumentInterface::IS_ARRAY & $this->mode);
    }
}