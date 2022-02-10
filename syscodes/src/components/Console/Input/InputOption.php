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
use Syscodes\Components\Support\Str;
use Syscodes\Components\Contracts\Console\InputOption as InputOptionInterface;

/**
 * This class represents a command line option.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class InputOption implements InputOptionInterface
{
    /**
     * The default value.
     * 
     * @var mixed $default
     */
    protected $default;

    /**
     * The option description.
     * 
     * @var string $description
     */
    protected $description;

    /**
     * The option mode.
     * 
     * @var int $mode
     */
    protected $mode;

    /**
     * The option name.
     * 
     * @var string $name
     */
    protected $name;

    /**
     * The Shortcut of the option.
     * 
     * @var string $shortcut
     */
    protected $shortcut;

    /**
     * Constructor. Create a new InputOption instance.
     * 
     * @param  string  $name  The argument name
     * @param  string|array|null  $shortcut  The shortcut of the option
     * @param  int|null  $mode  The argument mode
     * @param  string|null  $description  The description text
     * @param  mixed  $default  The default value
     * 
     * @return void
     * 
     * @throws \InvalidArgumentException  If option mode is invalid or incompatible
     */
    public function __construct(
        string $name, 
        $shortcut = null,
        int $mode = null,
        string $description = null,
        $default = null
    ) {
        if (Str::startsWith($name, '--')) {
            $name = substr($name, 2);
        }

        if (empty($name)) {
            throw new InvalidArgumentException('An option name cannot be empty');
        }

        if (empty($shortcut)) {
            $shortcut = null;
        }

        if (null !== $shortcut) {
            if (is_array($shortcut)) {
                $shortcut = implode('|', $shortcut);
            }
    
            $shortcuts = preg_split('{(\|)-?}', ltrim($shortcut, '-'));
            $shortcuts = array_filter($shortcuts);
            $shortcut  = implode('|', $shortcuts);
            
            if (empty($shortcut)) {
                throw new InvalidArgumentException('An option shortcut cannot be empty');
            }
        }
        
        if (null === $mode) {
            $mode = InputOptionInterface::VALUE_NONE;
        } elseif ($mode >= (InputOptionInterface::VALUE_NEGATABLE << 1) || $mode < 1) {
            throw new InvalidArgumentException(sprintf('Option mode "%s" is not valid.', $mode));
        }

        $this->name        = $name;
        $this->shortcut    = $shortcut;
        $this->mode        = $mode;
        $this->description = $description;

        $this->setDefault($default);        
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
     * {@inheritdoc}
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
        if (InputOptionInterface::VALUE_NONE === (InputOptionInterface::VALUE_NONE & $this->mode) && null !== $default) {
            throw new LogicException('Cannot set a default value when using InputOptionInterface::VALUE_NONE mode');
        }

        if ($this->isArray()) {
            if (null === $default) {
                $default = [];
            } elseif ( ! is_array($default)) {
                throw new LogicException('Should get a default value for an array argument');
            }
        }

        $this->default = $this->isAcceptValue() || $this->isNegatable() ? $default : false;
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
    public function getShortcut()
    {
        return $this->shortcut;
    }

    /**
     * {@inheritdoc}
     */
    public function setShortcut($shortcut): void
    {
        if (is_array($shortcut)) {
            $shortcut = implode('|', $shortcut);
        }

        $shortcuts = preg_split('{(\|)-?}', ltrim($shortcut, '-'));
        $shortcuts = array_filter($shortcuts);
        $shortcuts = implode('|', $shortcuts);

        $this->shortcut = $shortcuts;
    }
    
    /**
     * {@inheritdoc}
     */
    public function isAcceptValue(): bool
    {
        return $this->isValueRequired() || $this->isValueOptional();
    }

    /**
     * {@inheritdoc}
     */
    public function isValueRequired(): bool
    {
        return InputOptionInterface::VALUE_REQUIRED === (InputOptionInterface::VALUE_REQUIRED & $this->mode);
    }
    
    /**
     * G{@inheritdoc}
     */
    public function isValueOptional(): bool
    {
        return InputOptionInterface::VALUE_OPTIONAL === (InputOptionInterface::VALUE_OPTIONAL & $this->mode);
    }
    
    /**
     * {@inheritdoc}
     */
    public function isArray(): bool
    {
        return InputOptionInterface::VALUE_IS_ARRAY === (InputOptionInterface::VALUE_IS_ARRAY & $this->mode);
    }

    /**
     * {@inheritdoc}
     */
    public function isNegatable(): bool
    {
        return InputOptionInterface::VALUE_NEGATABLE === (InputOptionInterface::VALUE_NEGATABLE & $this->mode);
    }
}