<?php

namespace Syscodes\Components\Core\Console\Commands;

use Syscodes\Components\Console\GeneratorCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;

/**
 * Create a trait for console.
 */
#[AsCommand(name: 'make:trait')]
class TraitMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:trait';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new trait';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Trait';

    /**
     * Get the template file for the generator.
     *
     * @return string
     */
    protected function getTemplate(): string
    {
        return $this->resolvetemplatePath('/templates/trait.tpl');
    }

    /**
     * Resolve the fully-qualified path to the template.
     *
     * @param  string  $template
     * 
     * @return string
     */
    protected function resolvetemplatePath($template): string
    {
        return file_exists($customPath = $this->lenevor->basePath(trim($template, '/')))
            ? $customPath
            : __DIR__.$template;
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
        return match (true) {
            is_dir(app_path('Concerns')) => $rootNamespace.'\\Concerns',
            is_dir(app_path('Traits')) => $rootNamespace.'\\Traits',
            default => $rootNamespace,
        };
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getOptions(): array
    {
        return [
            ['force', 'f', InputOption::VALUE_NONE, 'Create the trait even if the trait already exists'],
        ];
    }
}