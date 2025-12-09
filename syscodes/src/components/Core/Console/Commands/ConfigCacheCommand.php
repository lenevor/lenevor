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

use Throwable;
use LogicException;
use Syscodes\Components\Console\Command;
use Syscodes\Components\Contracts\Console\Kernel as ConsoleKernelContract;
use Syscodes\Components\Filesystem\Filesystem;
use Syscodes\Components\Support\Arr;
use Symfony\Component\Console\Attribute\AsCommand;

/**
 * Allows create a cache file for faster configuration loading.
 */
#[AsCommand(name: 'config:cache')]
class ConfigCacheCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'config:cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a cache file for faster configuration loading';

    /**
     * The filesystem instance.
     *
     * @var \Syscodes\Components\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Create a new config cache command instance.
     *
     * @param  \Syscodes\Components\Filesystem\Filesystem  $files
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    /**
     * Execute the console command.
     *
     * @return void
     *
     * @throws \LogicException
     */
    public function handle()
    {
        $this->callSilent('config:clear');

        $config = $this->getFreshConfiguration();

        $configPath = $this->lenevor->getCachedConfigPath();

        $this->files->put(
            $configPath, '<?php return '.var_export($config, true).';'.PHP_EOL
        );

        try {
            require $configPath;
        } catch (Throwable $e) {
            $this->files->delete($configPath);

            foreach (Arr::dot($config) as $key => $value) {
                try {
                    eval(var_export($value, true).';');
                } catch (Throwable $e) {
                    throw new LogicException("Your configuration files could not be serialized because the value at \"{$key}\" is non-serializable.", 0, $e);
                }
            }

            throw new LogicException('Your configuration files are not serializable.', 0, $e);
        }

        $this->components->info('Configuration cached successfully.');
    }

    /**
     * Boot a fresh copy of the application configuration.
     *
     * @return array
     */
    protected function getFreshConfiguration(): array
    {
        $app = require $this->lenevor->bootstrapPath('app.php');

        $app->setStoragePath($this->lenevor->storagePath());

        $app->make(ConsoleKernelContract::class)->bootstrap();

        return $app['config']->all();
    }
}