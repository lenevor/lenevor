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

namespace Syscodes\Console;

use LogicException;
use ReflectionClass;
use ReflectionException;
use Psr\Log\LoggerInterface;
use Syscodes\Console\Command\BaseCommand;
use Syscodes\Contracts\Console\Input as InputInterface;
use Syscodes\Contracts\Console\Output as OutputInterface;

/**
 * Is class allows functionality for running, listing, etc all commands of framework.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class Command extends BaseCommand
{
    /**
     * Gets the code.
     * 
     * @var int $code
     */
    protected $code;

    /**
     * Gets the commands.
     * 
     * @var array $commands
     */
    protected $commands = [];

    
    /**
     * The Lenevor appplication instance.
     * 
     * @var \Syscodes\Core\Contracts\Application $lenevor
     */
    protected $lenevor;

    /**
     * Constructor. Create a new Command instance.
     * 
     * @param  string|null  $name  The command name
     * 
     * @return void
     */
    public function __construct(string $name = null)
    {
        parent::__construct($name);
    }

    /**
     * Runs a command given.
     * 
     * @param  \Syscodes\Contracts\Console\Input  $input
     * @param  \Syscodes\Contracts\Console\Output  $input
     * 
     * @return int|mixed
     */
    public function run(InputInterface $input, OutputInterface $output)
    {
        return parent::run($input, $output);
    }

    /**
     * Executes the current command.
     * 
     * @param  \Syscodes\Contracts\Console\Input  $input
     * @param  \Syscodes\Contracts\Console\Output  $input
     * 
     * @return int|mixed
     * 
     * @throws \LogicException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $method = method_exists($this, 'handle') ? 'handle' : '__invoke';

        return (int) $this->lenevor->call([$this, $method]);
    }

    /**
     * Get the Lenevor application instance.
     * 
     * @return \Syscodes\Contracts\Core\Application
     */
    public function getLenevor()
    {
        return $this->lenevor;
    }

    /**
     * Set the Lenevor application instance.
     * 
     * @param  \Syscodes\Contracts\Core\Application  $lenevor
     * 
     * @return void
     */
    public function setLenevor($lenevor)
    {
        $this->lenevor = $lenevor;
    }
}