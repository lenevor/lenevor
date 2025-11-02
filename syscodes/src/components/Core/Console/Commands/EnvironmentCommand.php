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
 * Show the environment type of the framework.
 */
#[AsCommandAttribute(name: 'env')]
class EnvironmentCommand extends Command
{
    /**
     * The console command name.
     * 
     * @var string|null $name
     */
    protected ?string $name = 'env';

    /**
     * The console command description.
     * 
     * @var string|null $description
     */
    protected string $description = 'Display the current framework environment';

    /**
     * Execute the console command.
     *
     * @return void
     * 
     * @throws \LogicException
     */
    public function handle()
    {
        $this->line(sprintf(
            '    <bg=blue;fg=white> INFO </> The application environment is [%s].',
            $this->lenevor['config']['app.env'],
        ));
    }
}