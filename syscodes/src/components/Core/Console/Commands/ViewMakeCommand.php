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
use Syscodes\Components\Core\Inspiration;

/**
 * Allows create view file 
 */
#[AsCommand(name: 'make:view')]
class ViewMakeCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'make:view';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new view';

    /**
     * The type of file being generated.
     *
     * @var string
     */
    protected $type = 'View';

    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * 
     * @return string
     *
     * @throws \Syscodes\Components\Contracts\Filesystem\FileNotFoundException
     */
    protected function buildClass($name): string
    {
        $contents = parent::buildClass($name);

        return str_replace(
            '{{ quote }}',
            Inspiration::quotes()->random(),
            $contents,
        );
    }

    /**
     * Get the destination view path.
     *
     * @param  string  $name
     * 
     * @return string
     */
    protected function getPath($name): string
    {
        return $this->viewPath(
            $this->getNameInput().'.'.$this->option('extension'),
        );
    }

    /**
     * Get the desired view name from the input.
     *
     * @return string
     */
    protected function getNameInput(): string
    {
        $name = trim($this->argument('name'));

        $name = str_replace(['\\', '.'], '/', $name);

        return $name;
    }

    /**
     * Get the template file for the generator.
     *
     * @return string
     */
    protected function getTemplate(): string
    {
        return $this->resolveTemplatePath(
            '/templates/view.tpl',
        );
    }

    /**
     * Resolve the fully-qualified path to the template.
     *
     * @param  string  $template
     * 
     * @return string
     */
    protected function resolveTemplatePath($template): string
    {
        return file_exists($customPath = $this->lenevor->basePath(trim($template, '/')))
            ? $customPath
            : __DIR__.$template;
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getOptions(): array
    {
        return [
            ['extension', null, InputOption::VALUE_OPTIONAL, 'The extension of the generated view', 'plaze.php'],
            ['force', 'f', InputOption::VALUE_NONE, 'Create the view even if the view already exists'],
        ];
    }
}