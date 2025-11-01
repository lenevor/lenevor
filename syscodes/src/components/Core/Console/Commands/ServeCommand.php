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

namespace Syscodes\Components\Core\Console\Commands;

use Syscodes\Components\Console\Command;
use Syscodes\Components\Support\Environment;
use  Syscodes\Components\Console\Input\InputOption;
use Syscodes\Components\Console\Attribute\AsCommandAttribute;

/**
 * Executable PHP server for execute the framework system.
 */
#[AsCommandAttribute(name: 'serve')]
class ServeCommand extends Command
{
    /**
     * The console command name.
     * 
     * @var string|null $name
     */
    protected ?string $name = 'serve';

     /**
     * The console command description.
     * 
     * @var string|null $description
     */
    protected string $description = 'Serve the application on the PHP development server';

    /**
     * Executes the current command.
     * 
     * @return int
     * 
     * @throws \LogicException
     */
    public function handle()
    {
        chdir(public_path());

        $host = $this->input->getOption('host');

        $port = $this->input->getOption('port');

        $public = $this->lenevor['path.public'];

        $this->line("<bg=blue;fg=white> INFO </> Server running on [http://{$this->host()}:{$this->port()}].");
        
        $this->newLine();

        $this->line('<fg=yellow;options=bold>Press Ctrl+C to stop the server</>');

        $this->newLine();

        passthru('"'.PHP_BINARY.'"'." -S {$host}:{$port} -t \"{$public}\"", $status);

        return $status;
    }
    
    /**
     * Get the host for the command.
     * 
     * @return string
     */
    protected function host(): string
    {
        return $this->input->getOption('host');
    }
    
    /**
     * Get the port for the command.
     * 
     * @return int
     */
    protected function port(): int
    {
        $port = $this->input->getOption('port') ?: 8000;

        return $port;
    }


    /**
     * Get the console command options.
     * 
     * @return array
     */
    protected function getOptions(): array
    {
        return [
            ['host', null, InputOption::VALUE_OPTIONAL, 'The host address to serve the application on', Environment::get('SERVER_HOST', '127.0.0.1')],
            ['port', null, InputOption::VALUE_OPTIONAL, 'The port to serve the application on', Environment::get('SERVER_PORT', '8000')],
        ];
    }
}