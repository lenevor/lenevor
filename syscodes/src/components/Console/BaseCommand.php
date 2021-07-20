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

use ReflectionException;
use Psr\Log\LoggerInterface;
use Syscodes\Console\Command\Command;

/**
 * Class BaseCommand.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class BaseCommand extends Command
{
    /**
     * Gets the Command's Arguments description.
     * 
     * @var array $arguments
     */
    protected $arguments = [];

    /**
     * The console command description.
     * 
     * @var string|null $description
     */
    protected $description;

    /**
     * The group of commands is lumped under, when listing commands.
     * 
     * @var string $group
     */
    protected $group;

    /**
     * The Lenevor appplication instance.
     * 
     * @var \Syscodes\Core\Contracts\Application $lenevor
     */
    protected $lenevor;

    /**
     * The logger to user for a command.
     * 
     * @var \Psr\Log\LoggerInterface $logger
     */
    protected $logger;

    /**
     * The console command name.
     * 
     * @var string $name
     */
    protected $name;

    /**
     * The Command's options description.
     * 
     * @var array $options
     */
    protected $options = [];

    /**
     * The signature of the console command.
     * 
     * @var string $signature
     */
    protected $signature;

    /**
     * Constructor. Create a new base command instance.
     * 
     * @param  \Psr\Log\LoggerInterface  $logger
     * 
     * @return void
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;       
    }

    /**
     * Resolve the console command instance.
     * 
     * @param  \Syscodes\Console\Command|string  $command
     * 
     * @return \Syscodes\Console\Command
     */
    public function resolveCommand($command)
    {
        $command = $this->getLenevor()->make($command);

        if ($command instanceof self) {
            $command->setLenevor($this->getLenevor());
        }

        return $command;
    }

    /**
     * Executes the current command.
     * 
     * @return int
     * 
     * @throws \LogicException
     */
    protected function execute()
    {
        $method = method_exists($this, 'handle') ? 'handle' : '__invoke';

        return (int) $this->lenevor->call([$this, $method]);
    }

    /**
     * Is be used by a command to run other commands.
     * 
     * @param  string  $command
     * @param  array  $params
     * 
     * @return mixed
     * 
     * @throws \ReflectionException
     */
    public function call(string $command, array $params)
    {
        return $this->run($command, $params);
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