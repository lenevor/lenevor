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

use Syscodes\Components\Support\Stringable;
use Syscodes\Components\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Create new commands of console.
 */
#[AsCommand(name: 'make:command')]
class ConsoleMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Prime command';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Console command';

    /**
     * Replace the class name for the given template.
     *
     * @param  string  $template
     * @param  string  $name
     * 
     * @return string
     */
    protected function replaceClass($template, $name): string
    {
        $template = parent::replaceClass($template, $name);
        
        $command = $this->option('command') ?: 'app:'.(new Stringable($name))->classBasename()->kebab()->value();

        return str_replace(['dummy:command', '{{ command }}'], $command, $template);
    }

    /**
     * Get the template file for the generator.
     *
     * @return string
     */
    protected function getTemplate(): string
    {
        $relativePath = '/templates/console.tpl';

        return file_exists($customPath = $this->lenevor->basePath(trim($relativePath, '/')))
            ? $customPath
            : __DIR__.$relativePath;
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * 
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\Console\Commands';
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments(): array
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the command'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions(): array
    {
        return [
            ['force', 'f', InputOption::VALUE_NONE, 'Create the class even if the console command already exists'],
            ['command', null, InputOption::VALUE_OPTIONAL, 'The terminal command that will be used to invoke the class'],
        ];
    }
}