<?php

namespace Syscodes\Components\Core\Console\Commands;

use Syscodes\Components\Console\GeneratorCommand;
use Syscodes\Components\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;

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