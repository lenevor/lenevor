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

use Exception;
use LogicException;
use Syscodes\Components\Console\Command\Command as BaseCommand;
use Syscodes\Components\Contracts\Console\Input\Input as InputInterface;
use Syscodes\Components\Contracts\Console\Output\Output as OutputInterface;

/**
 * Is class allows functionality for running, listing, etc all commands of framework.
 */
class Command extends BaseCommand
{
    use Concerns\ConfirmProcess,
        Concerns\HasParameters,
        Concerns\InteractsIO;

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
     * @var \Syscodes\Components\Contracts\Core\Application $lenevor
     */
    protected $lenevor;

    /**
     * The name and signature of the console command.
     * 
     * @var string|null $signature
     */
    protected ?string $signature = null;

    /**
     * Constructor. Create a new Command instance.
     * 
     * @return void
     */
    public function __construct()
    {
        parent::__construct($this->name);

        if ( ! empty($this->help)) {
            $this->setDescription($this->description);
        }

        if ( ! empty($this->help)) {
            $this->setHelp($this->help);
        }

        $this->setHidden($this->isHidden());
        
        if (isset($this->aliases)) {
            $this->setAliases((array) $this->aliases);
        }

        if ( ! isset($this->signature)) {
            $this->specifyParameters();
        }
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
        return parent::run($this->input = $input, $this->output = $output);
    }

    /**
     * Executes the current command.
     * 
     * @param  \Syscodes\Components\Contracts\Console\Input\Input  $input
     * @param  \Syscodes\Components\Contracts\Console\Output\Output  $input
     * 
     * @return int
     * 
     * @throws \LogicException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $method = method_exists($this, 'handle') ? 'handle' : '__invoke';

        try {
            return (int) $this->lenevor->call([$this, $method]);
        } catch (Exception $e) {
            throw $e->getMessage();

            return 0;
        }
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