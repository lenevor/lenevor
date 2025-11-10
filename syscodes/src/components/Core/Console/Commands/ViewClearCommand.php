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

use RuntimeException;
use Syscodes\Components\Console\Command;
use Syscodes\Components\Filesystem\Filesystem;
use Syscodes\Components\Console\Attribute\AsCommandAttribute;

/**
 * Allows you to delete all previously created views in the 
 * views folder of the storage.
 */
#[AsCommandAttribute(name: 'view:clear')]
class ViewClearCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'view:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all transpiled view files';

    /**
     * The filesystem instance.
     *
     * @var \Syscodes\Components\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Constructor. Create a new config clear command instance.
     *
     * @param  \Syscodes\Components\Filesystem\Filesystem  $files
     * 
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    /**
     * Executes the current command.
     *
     * @return void
     *
     * @throws \RuntimeException
     */
    public function handle()
    {
        $path = $this->lenevor['config']['view.transpiled'];

        if ( ! $path) {
            throw new RuntimeException('View path not found.');
        }
        
        $this->lenevor['view.engine.resolver']
             ->resolve('plaze')
             ->eraseTranspiledOrNotExpired();

        foreach ($this->files->glob("{$path}/*") as $view) {
            $this->files->delete($view);
        }

        $this->line('    <bg=blue;fg=white> INFO </> Transpiled views cleared successfully.');
    }
}