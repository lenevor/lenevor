<?php

/**
 * Lenevor PHP Framework
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

namespace Syscodes\Components\Database\Console\Seeds;

use Symfony\Component\Console\Attribute\AsCommand;
use Syscodes\Components\Console\GeneratorCommand;
use Syscodes\Components\Support\Str;

/**
 * Allows create seeder class in the application.
 */
#[AsCommand(name: 'make:seeder')]
class SeederMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:seeder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new seeder class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Seeder';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        parent::handle();
    }

    /**
     * Get the template file for the generator.
     *
     * @return string
     */
    protected function getTemplate(): string
    {
        return $this->resolveTemplatePath('/templates/seeder.tpl');
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
        return is_file($customPath = $this->lenevor->basePath(trim($template, '/')))
            ? $customPath
            : __DIR__.$template;
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
        $name = str_replace('\\', '/', Str::replaceFirst($this->rootNamespace(), '', $name));

        if (is_dir($this->lenevor->databasePath().'/seeds')) {
            return $this->lenevor->databasePath().'/seeds/'.$name.'.php';
        }

        return $this->lenevor->databasePath().'/seeders/'.$name.'.php';
    }

    /**
     * Get the root namespace for the class.
     *
     * @return string
     */
    protected function rootNamespace(): string
    {
        return 'Database\Seeders\\';
    }
}