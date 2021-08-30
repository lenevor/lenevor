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

namespace Syscodes\Console\Command;

use LogicException;
use ReflectionClass;
use ReflectionException;
use Psr\Log\LoggerInterface;

/**
 * Is class allows functionality for running, listing, etc all commands of framework.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class Command
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
     * The Logger instance.
     * 
     * @var \Psr\Log\Interface $logger
     */
    protected $logger;

    /**
     * Constructor. Create a new Command instance.
     * 
     * @param  \Psr\Log\Interface  $logger
     * 
     * @return void
     */
    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    /**
     * Runs a command given.
     * 
     * @param  string  $command
     * @param  array  $params
     * 
     * @return mixed
     */
    public function run(string $command, array $params)
    {
        return $this->exposeCommands();        
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
     * Calls all the commands.
     * 
     * @return void
     */
    protected function exposeCommands()
    {
        if ($this->code) {
            $statusCode = $this->code;
        } else {
            $statusCode = $this->execute();
        }
        
        if ( ! is_int($statusCode)) {
            throw new \TypeError(sprintf('Return value of "%s::execute()" must be of the type int, "%s" returned.', static::class, get_debug_type($statusCode)));
        }

        return is_numeric($statusCode) ? (int) $statusCode : 0;
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