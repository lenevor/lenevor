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
use Syscodes\Components\Support\InteractsWithTime;
use  Syscodes\Components\Console\Input\InputOption;
use Syscodes\Components\Console\Attribute\AsCommandAttribute;

use function Syscodes\Components\Support\php_binary;

/**
 * Executable PHP server for execute the framework system.
 */
#[AsCommandAttribute(name: 'serve')]
class ServeCommand extends Command
{
    use InteractsWithTime;

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
     * The current port offset.
     *
     * @var int
     */
    protected int $portOffset = 0;

    /**
     * Executes the current command.
     * 
     * @return int
     * 
     * @throws \Exception
     */
    public function handle()
    {
        chdir(public_path());

        $this->line("   <bg=blue;fg=white;options=bold> INFO </> Server running on [http://{$this->host()}:{$this->port()}].\n");

        $this->line('   <fg=yellow;options=bold>Press Ctrl+C to stop the server</>');

        $this->newLine();

        passthru($this->serverCommand(), $status);

        if ($status && $this->canTryAnotherPort()) {
            $this->portOffset += 1;

            return $this->handle();
        }

        return $status;
    }
    
    /**
     * Get the full server command.
     * 
     * @return string
     */
    protected function serverCommand(): string
    {
        $server = file_exists(base_path('server.php'))
            ? base_path('server.php')
            : __DIR__.'/../../Resources/server.php';

        return sprintf("%s -S %s:%s",
            php_binary(),
            $this->host(),
            $this->port(),
            $server
        );
    }
    
    /**
     * Get the host for the command.
     * 
     * @return string
     */
    protected function host(): string
    {
        [$host] = $this->getHostAndPort();
        
        return $host;
    }
    
    /**
     * Get the port for the command.
     * 
     * @return int
     */
    protected function port(): int
    {
        $port = $this->input->getOption('port');
        
        if (is_null($port)) {
            [, $port] = $this->getHostAndPort();
        }
        
        $port = $port ?: 8000;
        
        return $port + $this->portOffset;
    }
    
    /**
     * Get the host and port from the host option string.
     * 
     * @return array
     */
    protected function getHostAndPort(): array
    {
        if (preg_match('/(\[.*\]):?([0-9]+)?/', $this->input->getOption('host'), $matches) !== false) {
            return [
                $matches[1] ?? $this->input->getOption('host'),
                $matches[2] ?? null,
            ];
        }
        
        $hostParts = explode(':', $this->input->getOption('host'));
        
        return [
            $hostParts[0],
            $hostParts[1] ?? null,
        ];
    }

    /**
     * Check if command has reached its max amount of port tries.
     *
     * @return bool
     */
    protected function canTryAnotherPort(): bool
    {
        return is_null($this->input->getOption('port')) &&
               ($this->input->getOption('tries') > $this->portOffset);
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
            ['tries', null, InputOption::VALUE_OPTIONAL, 'The max number of ports to attempt to serve from', 10],
        ];
    }
}