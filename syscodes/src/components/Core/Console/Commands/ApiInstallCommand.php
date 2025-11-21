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
use Syscodes\Components\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Attribute\AsCommand;

/**
 * Executable PHP install for execute of the api file.
 */
#[AsCommand(name: 'install:api')]
class ApiInstallCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'install:api';
    
    /**
     * The console command description.
     * 
     * @var string
     */
    protected $description = 'Create an API routes file and install';

    /**
     * Executes the current command.
     * 
     * @return void
     */
    public function handle()
    {
        if (file_exists($apiRoutesPath = $this->lenevor->basePath('routes/api.php')) && ! $this->option('force')) {
            $this->line('   <bg=blue;fg=white;options=bold> INFO </> API routes file already exists.');
        } else {
            $this->line('   <bg=blue;fg=white;options=bold> INFO </> Published API routes file.');

            copy(__DIR__.'/templates/api-routes.tpl', $apiRoutesPath);

            $this->getApiRoutesFile();
        }
    }

    /**
     * Uncomment the API routes file in the application bootstrap file.
     *
     * @return void
     */
    protected function getApiRoutesFile()
    {
        $appBootstrapPath = $this->lenevor->bootstrapPath('app.php');

        $content = file_get_contents($appBootstrapPath);

        if (str_contains($content, '// api: ')) {
            (new Filesystem)->replaceInFile(
                '// api: ',
                'api: ',
                $appBootstrapPath,
            );
        } elseif (str_contains($content, 'web: __DIR__.\'/../routes/web.php\',')) {
            (new Filesystem)->replaceInFile(
                'web: __DIR__.\'/../routes/web.php\',',
                'web: __DIR__.\'/../routes/web.php\','.PHP_EOL.'        api: __DIR__.\'/../routes/api.php\',',
                $appBootstrapPath,
            );
        } else {
            $this->warning("Unable to automatically add API route definition to [{$appBootstrapPath}]. API route file should be registered manually.");

            return;
        }
    }

    /**
     * Get the console command options.
     * 
     * @return array
     */
    protected function getOptions(): array
    {
        return [
            ['--composer=global', null, InputOption::VALUE_OPTIONAL, 'Absolute path to the Composer binary which should be used to install packages.'],
            ['--force', null, InputOption::VALUE_NONE, 'Overwrite any existing API routes file.'],            
        ];
    }
}