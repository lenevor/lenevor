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

namespace Syscodes\Console\Input;

use LogicException;
use InvalidArgumentException;

/**
 * This class valides the arguments and options to set in command line.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class InputDefinition
{
    /**
     * The argument implement.
     * 
     * @var array $arguments
     */
    protected $arguments;

    /**
     * An array argument.
     * 
     * @var bool $hasArrayArgument
     */
    protected $hasArrayArgument;

    /**
     * An array optional argument.
     * 
     * @var bool $hasOptionalArgument
     */
    protected $hasOptionalArgument;

    /**
     * An array negations.
     * 
     * @var array $negations
     */
    protected $negations;

    /**
     * An array InputOption object.
     * 
     * @var array $options
     */
    protected $options;

    /**
     * Gets the number of InputArguments.
     * 
     * @var int $requiredCount
     */
    protected $requiredCount;

    /**
     * Gets the InputOption to shortcut.
     * 
     * @var array $shortcuts
     */
    protected $shortcuts;

    /**
     * Constructor. Create a new InputDefinition instance.
     * 
     * @param  array  $arguments  The arguments for command
     * @param  array $options  The options for command
     * 
     * @return void
     * 
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function __construct(array $arguments = [], array $options = [])
    {
        $this->setArguments($arguments);
        $this->setOptions($options);
    }
    
    /*
    |----------------------------------------------------------------
    | Some Methods For The Arguments
    |---------------------------------------------------------------- 
    */

    

    /*
    |----------------------------------------------------------------
    | Some Methods For The Options
    |---------------------------------------------------------------- 
    */

}