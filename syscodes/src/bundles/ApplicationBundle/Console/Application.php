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

namespace Syscodes\Bundles\ApplicationBundle\Console;

use Syscodes\Components\Version;
use Syscodes\Components\Console\Command;
use Syscodes\Components\Contracts\Events\Dispatcher;
use Syscodes\Components\Contracts\Container\Container;
use Syscodes\Components\Console\Application as BaseApplication;
use Syscodes\Components\Contracts\Console\Input\Input as InputInterface;
use Syscodes\Components\Contracts\Console\Output\Output as OutputInterface;

/**
 * Console application.
 */
class Application extends BaseApplication
{
	/**
	 * Application config data.
	 * 
	 * @var array $config
	 */
	protected $config = [
		'homepage'   => '',
		'publishAt'  => '02.05.2019',
		'updateAt'   => '13.09.2021',
		'logoText'   => '',
		'logoStyle'  => 'info',
	];

	/**
	 * The event dispatcher instance.
	 * 
	 * @var \Syscodes\Components\Contracts\Events\Dispatcher $events
	 */
	protected $events;

	/**
	 * The Lenevor application instance.
	 * 
	 * @var \Syscodes\Components\Contracts\Container|Container $lenevor
	 */
	protected $lenevor;

	/**
	 * Console constructor. Initialize the console of Lenevor.
	 *
	 * @param  \Syscodes\Components\Contracts\Container\Container  $lenevor
	 * 
	 * @param  string  $version
	 * 
	 * @return void
	 */
	public function __construct(Container $lenevor, Dispatcher $events, string $version)
	{
		parent::__construct(Version::NAME, $version);

		$this->events  = $events;
		$this->lenevor = $lenevor;
	}

	/**
	 * Runs the current command discovered on the CLI.
     * 
     * @param  \Syscodes\Components\Contracts\Console\Input\Input|null  $input  The input interface implemented
     * @param  \Syscodes\Components\Contracts\Console\Output\Output|null  $output  The output interface implemented
     * 
     * @return int
	 */
	public function run(?InputInterface $input = null, ?OutputInterface $output = null): int
	{
		$this->setLogo("                     __                                                    
                    / /   ___  ____  ___ _   ______  _____                 
                   / /   / _ \/ __ \/ _ \ | / / __ \/ ___/                 
                  / /___/  __/ / / /  __/ |/ / /_/ / /                     
                 /_____/\___/_/ /_/\___/|___/\____/_/                      
     ________    ____   ___                ___            __  _                
    / ____/ /   /  _/  /   |  ____  ____  / (_)________ _/ /_(_)___  ____      
   / /   / /    / /   / /| | / __ \/ __ \/ / / ___/ __ `/ __/ / __ \/ __ \     
  / /___/ /____/ /   / ___ |/ /_/ / /_/ / / / /__/ /_/ / /_/ / /_/ / / / /     
  \____/_____/___/  /_/  |_/ .___/ .___/_/_/\___/\__,_/\__/_/\____/_/ /_/
                          /_/   /_/
		", 'info');
		
		$exit = parent::run($input, $output);
		
		return $exit;
	}
	
	/**
	 * Add a command, resolving through the application.
	 * 
	 * @param  \Syscodes\Components\Console\Command|string  $command
	 * 
	 * @return \Syscodes\Components\Console\Command\Command|null
	 */
	public function resolve($command)
	{
		if ($command instanceof Command) {
			return $this->addCommand($command);
		}
		
		return $this->add($this->lenevor->make($command));
	}
	
	/**
	 * Resolve an array of commands through the application.
	 * 
	 * @param  mixed  $commands
	 * 
	 * @return static
	 */
	public function resolveCommands($commands): static
	{
		$commands = is_array($commands) ? $commands : func_get_args();
		
		foreach ($commands as $command) {
			$this->resolve($command);
		}
		
		return $this;
	}
	
	/**
	 * Add a command to the console.
	 * 
	 * @param  \Syscodes\Components\Console\Command\Command  $command
	 * 
	 * @return \Syscodes\Components\Console\Command\Command|null
	 */
	#[\Override]
	public function add(Command $command): ?Command
	{
		if ($command instanceof Command) {
			$command->setLenevor($this->lenevor);
		}
		
		return $this->addToParent($command);
	}
	
	/**
	 * Add the command to the parent instance.
	 * 
	 * @param  \Syscodes\Components\Console\Command\Command  $command
	 * 
	 * @return \Syscodes\Components\Console\Command\Command
	 */
	protected function addToParent(Command $command)
	{
		return parent::addCommand($command);
	}

	/**
	 * Returns the version of the console.
     *
     * @return string
	 */
	public function getConsoleVersion(): string
	{
		return parent::getConsoleVersion().
			sprintf(' (env: <comment>%s</>, debug: <comment>%s</>) [<note>%s</>]',
				env('APP_ENV'), env('APP_DEBUG') ? 'true' : 'false', PHP_OS
			);
	}

	/**
     * Gets the logo text for console app.
     * 
     * @return string|null
     */
    public function getLogoText(): string
    {
        return $this->config['logoText'] ?? null;
    }

    /**
     * Sets the logo text for console app.
     * 
     * @param  string  $logoText
     * @param  striong|null  $style
     * 
     * @return void
     */
    public function setLogo(string $logoText, ?string $style = null): void
    {
        $this->config['logoText'] = $logoText;

        if ($style) {
            $this->config['logoStyle'] = $style;
        }
    }

    /**
     * Gets the logo style for console app.
     * 
     * @return string|null 
     */
    public function getLogoStyle(): ?string
    {
        return $this->config['logoStyle'] ?? 'info';
    }

    /**
     * Sets the logo style for console app.
     * 
     * @param  string  $style
     * 
     * @return void
     */
    public function setLogoStyle(string $style): void
    {
        $this->config['logoStyle'] = $style;
    }

	/**
	 * Gets the Lenevor application instance.
	 * 
	 * @return \Syscodes\Components\Contracts\Core\Application
	 */
	public function getLenevor()
	{
		return $this->lenevor;
	}
}