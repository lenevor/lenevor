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
 * @copyright   Copyright (c) 2019 - 2026 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Core\Console\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;
use Syscodes\Components\Console\GeneratorCommand;
use Syscodes\Components\Support\Str;

use function Syscodes\Components\Filesystem\join_paths;

/**
 * Creates a file of configuration in the application.
 */
#[AsCommand(name: 'make:config', aliases: ['config:make'])]
class ConfigMakeCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'make:config';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new configuration file';

    /**
     * The type of file being generated.
     *
     * @var string
     */
    protected $type = 'Config';

    /**
     * The console command name aliases.
     *
     * @var array<int, string>
     */
    protected $aliases = ['config:make'];

    /**
     * Get the destination file path.
     *
     * @param  string  $name
     * 
     * @return string
     */
    protected function getPath($name): string
    {
        return config_path(Str::finish($this->argument('name'), '.php'));
    }

    /**
     * Get the template file for the generator.
     * 
     * @return string
     */
    protected function getTemplate(): string
    {
        $relativePath = join_paths('templates', 'config.tpl');

        return file_exists($customPath = $this->lenevor->basePath($relativePath))
            ? $customPath
            : join_paths(__DIR__, $relativePath);
    }

    /**
     * Get the console command arguments.
     * 
     * @return array
     */
    protected function getOptions(): array
    {
        return [
            ['force', 'f', InputOption::VALUE_NONE, 'Create the configuration file even if it already exists'],
        ];
    }

    /**
     * Prompt for missing input arguments using the returned questions.
     *
     * @return array
     */
    protected function promptForMissingArgumentsUsing(): array
    {
        return [
            'name' => 'What should the configuration file be named?',
        ];
    }
}