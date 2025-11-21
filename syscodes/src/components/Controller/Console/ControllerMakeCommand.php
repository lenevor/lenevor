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

namespace Syscodes\Components\controller\console;

use Syscodes\Components\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Attribute\AsCommand;

/**
 * Create different type of controllers depend according to the need.
 */
#[AsCommand(name: 'make:controller')]
class ControllerMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:controller';
    
    /**
     * The console command description.
     * 
     * @var string
     */
    protected $description = 'Create a new controller class';
    
    /**
     * The type of class being generated.
     * 
     * @var string $type
     */
    protected $type = 'Controller';

    /**
     * Get the template file for the generator.
     * 
     * @return string
     */
    protected function getTemplate(): string
    {
        $template = null;
        
        if ($type = $this->option('type')) {
            $template = "/templates/controller.{$type}.tpl";
        } elseif ($this->option('parent')) {
            $template = $this->option('singleton')
                ? '/templates/controller.nested.singleton.tpl'
                : '/templates/controller.nested.tpl';
            } elseif ($this->option('invokable')) {
                $template = '/templates/controller.invokable.tpl';
        } elseif ($this->option('singleton')) {
            $template = '/templates/controller.singleton.tpl';
        } elseif ($this->option('resource')) {
            $template = '/templates/controller.tpl';
        }
        
        if ($this->option('api') && is_null($template)) {
            $template = '/templates/controller.api.tpl';
        } elseif ($this->option('api') && ! is_null($template) && ! $this->option('invokable')) {
            $template = str_replace('.template', '.api.tpl', $template);
        }
        
        $template ??= '/templates/controller.plain.tpl';
        
        return $this->resolveTemplatePath($template);
    }

    /**
     * Resolve the fully-qualified path to the template.
     *
     * @param  string  $template
     * 
     * @return string
     */
    protected function resolveTemplatePath($template)
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
        return $rootNamespace.'\Http\Controllers';
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
        $rootNamespace = $this->rootNamespace();
        $controllerNamespace = $this->getNamespace($name);
        
        $replace = [];
        
        if ($this->option('creatable')) {
            $replace['abort(404);'] = '//';
        }
        
        $baseControllerExists = file_exists($this->getPath("{$rootNamespace}Http\Controller"));
        
        if ($baseControllerExists) {
            $replace["use {$controllerNamespace}\Controller;\n"] = '';
        } else {
            $replace[' extends Controller'] = '';
            $replace["use {$rootNamespace}Http\Controllers\Controller;\n"] = '';
        }

        return str_replace(
            array_keys($replace), array_values($replace), parent::buildClass($name)
        );
    }

    /**
     * Get the console command options.
     * 
     * @return array
     */
    protected function getOptions(): array
    {
        return [
            ['api', null, InputOption::VALUE_NONE, 'Exclude the create and edit methods from the controller'],
            ['type', null, InputOption::VALUE_REQUIRED, 'Manually specify the controller stub file to use'],
            ['force', null, InputOption::VALUE_NONE, 'Create the class even if the controller already exists'],
            ['invokable', 'i', InputOption::VALUE_NONE, 'Generate a single method, invokable controller class'],
            ['resource', 'r', InputOption::VALUE_NONE, 'Generate a resource controller class'],
            ['requests', 'R', InputOption::VALUE_NONE, 'Generate FormRequest classes for store and update'],
            ['singleton', 's', InputOption::VALUE_NONE, 'Generate a singleton resource controller class'],
            ['creatable', null, InputOption::VALUE_NONE, 'Indicate that a singleton resource should be creatable'],        
        ];
    }
}