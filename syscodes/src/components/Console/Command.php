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

namespace Syscodes\Components\Console;

use LogicException;
use Syscodes\Components\Console\Concerns\InteractsIO;
use Syscodes\Components\Console\Command\Command as BaseCommand;
use Syscodes\Components\Contracts\Console\Input\Input as InputInterface;
use Syscodes\Components\Contracts\Console\Output\Output as OutputInterface;

/**
 * Is class allows functionality for running, listing, etc all commands of framework.
 */
class Command extends BaseCommand
{
    use InteractsIO;

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
     * The console command description.
     * 
     * @var string $description
     */
    protected $description;
    
    /**
     * The console command help text.
     * 
     * @var string $help
     */
    protected $help;

    /**
     * Indicates whether the command should be shown in the Prime command list.
     * 
     * @var bool $hidden
     */
    protected $hidden = false;
    
    /**
     * The Lenevor appplication instance.
     * 
     * @var \Syscodes\Components\Core\Contracts\Application $lenevor
     */
    protected $lenevor;

    /**
     * The console command name.
     * 
     * @var string $name
     */
    protected $name;

    /**
     * Constructor. Create a new Command instance.
     * 
     * @return void
     */
    public function __construct()
    {
        parent::__construct($this->name);

        $this->setDescription((string) $this->description);
        $this->setHelp((string) $this->help);
        $this->setHidden($this->isHidden());
    }

    /**
     * Runs a command given.
     * 
     * @param  \Syscodes\Components\Contracts\Console\Input\Input  $input
     * @param  \Syscodes\Components\Contracts\Console\Output\Output  $input
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
     * @param  \Syscodes\Components\Contracts\Console\Input\Input  $input
     * @param  \Syscodes\Components\Contracts\Console\Output\Output  $input
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
     * @return \Syscodes\Components\Contracts\Core\Application
     */
    public function getLenevor()
    {
        return $this->lenevor;
    }

    /**
     * Set the Lenevor application instance.
     * 
     * @param  \Syscodes\Components\Contracts\Core\Application  $lenevor
     * 
     * @return void
     */
    public function setLenevor($lenevor): void
    {
        $this->lenevor = $lenevor;
    }
}