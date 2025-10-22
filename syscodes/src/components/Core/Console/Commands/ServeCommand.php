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
use Syscodes\Components\Console\Attribute\AsCommandAttribute;

/**
 * Executable PHP server for execute the framework system.
 */
#[AsCommandAttribute(name: 'serve')]
class ServeCommand extends Command
{
     /**
     * The console command description.
     * 
     * @var string $description
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
        chdir($this->lenevor['path.base']);

        // $host = $this->input->getOption('host');

        // $port = $this->input->getOption('port');

        $public = $this->lenevor['path.public'];

        $this->commandline('<bg=blue;fg=white> INFO </> Lenevor Framework development Server started on http://{$host}:{$port}');
    }
}