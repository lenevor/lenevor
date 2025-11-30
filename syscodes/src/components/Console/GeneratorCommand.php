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

namespace Syscodes\Components\Console;

use Syscodes\Components\Support\Str;
use Syscodes\Components\Console\Command;
use Syscodes\Components\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Allows generate commands. 
 */
abstract class GeneratorCommand extends Command
{
    /**
     * The filesystem instance.
     * 
     * @var \Syscodes\Components\Filesystem\Filesystem $files
     */
    protected $files;
    
    /**
     * The type of class being generated.
     * 
     * @var string $type
     */
    protected $type;

    /**
     * Constructor. Create a new controller creator command instance.
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
     * Get the template file for the generator.
     * 
     * @return string
     */
    abstract protected function getTemplate(): string;
    
    /**
     * Executes the current command.
     * 
     * @return void
     */
    public function handle()
    {
        $name = $this->parseName($this->getNameInput());
        
        $path = $this->getPath($name);
        
        if (( ! $this->hasOption('force') ||
              ! $this->option('force')) &&
              $this->alreadyExists($this->getNameInput())) {
            $this->components->error($this->type.' already exists!');
            
            return false;
        }
        
        $this->makeDirectory($path);
        
        $this->files->put($path, $this->sortImports($this->buildClass($name)));

        $info = $this->type;
        
        $this->components->info(sprintf('%s [%s] created successfully.', $info, $path));
    }
    
    /**
     * Determine if the class already exists.
     * 
     * @param  string  $rawName
     * 
     * @return bool
     */
    protected function alreadyExists($rawName): bool
    {
        return $this->files->exists($this->getPath($this->parseName($rawName)));
    }
    
    /**
     * Get the destination class path.
     * 
     * @param  string  $name
     * 
     * @return string
     */
    protected function getPath($name): string
    {
        $name = Str::replaceFirst($this->rootNamespace(), '', $name);
        
        return $this->lenevor['path'].DIRECTORY_SEPARATOR.str_replace('\\', DIRECTORY_SEPARATOR, $name).'.php';
    }
    
    /**
     * Parse the class name and format according to the root namespace.
     * 
     * @param  string  $name
     * 
     * @return string
     */
    protected function parseName($name): string
    {
        $name = ltrim($name, '\\/');

        $name = str_replace('/', '\\', $name);

        $rootNamespace = $this->rootNamespace();

        if (Str::startsWith($name, $rootNamespace)) {
            return $name;
        }
        
        return $this->parseName($this->getDefaultNamespace(trim($rootNamespace, '\\')).'\\'.$name);
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
        return $rootNamespace;
    }
    
    /**
     * Build the directory for the class if necessary.
     * 
     * @param  string  $path
     * 
     * @return string
     */
    protected function makeDirectory($path) 
    {
        if ( ! $this->files->isDirectory(dirname($path))) {
            $this->files->makeDirectory(dirname($path), 0777, true, true);
        }
    }
    
    /**
     * Build the class with the given name.
     * 
     * @param  string  $name
     * 
     * @return string
     */
    protected function buildClass($name): string
    {
        $template = $this->files->get($this->getTemplate());
        
        return $this->replaceNamespace($template, $name)->replaceClass($template, $name);
    }
    
    /**
     * Replace the namespace for the given template.
     * 
     * @param  string  $template
     * @param  string  $name
     * 
     * @return static
     */
    protected function replaceNamespace(&$template, $name): static
    {
       $searches = [
            ['DummyNamespace', 'DummyRootNamespace'],
            ['{{ namespace }}', '{{ rootNamespace }}'],
        ];

        foreach ($searches as $search) {
            $template = str_replace(
                $search,
                [$this->getNamespace($name), $this->rootNamespace()],
                $template
            );
        }

        return $this;
    }
    
    /**
     * Get the full namespace name for a given class.
     * 
     * @param  string  $name
     * 
     * @return string
     */
    protected function getNamespace($name): string
    {
        return trim(implode('\\', array_slice(explode('\\', $name), 0, -1)), '\\');
    }
    
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
        $class = str_replace($this->getNamespace($name).'\\', '', $name);

        return str_replace(['DummyClass', '{{ class }}', '{{class}}'], $class, $template);
    }
    
    /**
     * Alphabetically sorts the imports for the given stub.
     * 
     * @param  string  $template
     * 
     * @return string
     */
    protected function sortImports($template): string
    {
        if (preg_match('/(?P<imports>(?:^use [^;{]+;$\n?)+)/m', $template, $match)) {
            $imports = explode("\n", trim($match['imports']));
            
            sort($imports);
            
            return str_replace(trim($match['imports']), implode("\n", $imports), $template);
        }
        
        return $template;
    }
    
    /**
     * Get the desired class name from the input.
     * 
     * @return string
     */
    protected function getNameInput(): string
    {
        $name = trim($this->argument('name'));

        if (Str::endsWith($name, '.php')) {
            return Str::substr($name, 0, -4);
        }

        return $name;
    }
    
    /**
     * Get the root namespace for the class.
     * 
     * @return string
     */
    protected function rootNamespace(): string
    {
        return $this->lenevor->getNamespace();
    }
    
    /**
     * Get the console command arguments.
     * 
     * @return array
     */
    protected function getArguments(): array
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the '.strtolower($this->type)],
        ];
    }

    /**
     * Prompt for missing input arguments using the returned questions.
     *
     * @return array
     */
    protected function promptForMissingArgumentsUsing(): array
    {
        return ['name' => 'What should the '.strtolower($this->type).' be named?'];
    }
}