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

namespace Syscodes\Components\Database\Migrations;

use Closure;
use InvalidArgumentException;
use Syscodes\Components\Filesystem\Filesystem;
use Syscodes\Components\Support\Str;

/**
 * Allow the creation of a migration for console.
 */
class MigrationCreator
{
    /**
     * The custom app templates directory.
     *
     * @var string
     */
    protected $customTemplatePath;

    /**
     * The filesystem instance.
     *
     * @var \Syscodes\Components\Filesystem\Filesystem
     */
    protected $files;

    /**
     * The registered post create hooks.
     *
     * @var string[]
     */
    protected $postCreate = [];

    /**
     * Constructor. Create a new migration creator instance.
     *
     * @param  \Syscodes\Components\Filesystem\Filesystem  $files
     * @param  string  $customTemplatePath
     * 
     * @return void
     */
    public function __construct(Filesystem $files, $customTemplatePath)
    {
        $this->files = $files;
        $this->customTemplatePath = $customTemplatePath;
    }

    /**
     * Create a new migration at the given path.
     *
     * @param  string  $name
     * @param  string  $path
     * @param  string|null  $table
     * @param  bool  $create
     * 
     * @return string
     *
     * @throws \Exception
     */
    public function create($name, $path, $table = null, $create = false)
    {
        $this->ensureMigrationDoesntAlreadyExist($name, $path);

        $stub = $this->getTemplate($table, $create);

        $path = $this->getPath($name, $path);

        $this->files->directoryExists(dirname($path));

        $this->files->put(
            $path, $this->populateTemplate($stub, $table)
        );

        $this->firePostCreateHooks($table, $path);

        return $path;
    }

    /**
     * Ensure that a migration with the given name doesn't already exist.
     *
     * @param  string  $name
     * @param  string|null  $migrationPath
     * 
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    protected function ensureMigrationDoesntAlreadyExist($name, $migrationPath = null)
    {
        if ( ! empty($migrationPath)) {
            $migrationFiles = $this->files->glob($migrationPath.'/*.php');

            foreach ($migrationFiles as $migrationFile) {
                $this->files->getRequireOnce($migrationFile);
            }
        }

        if (class_exists($className = $this->getClassName($name))) {
            throw new InvalidArgumentException("A {$className} class already exists.");
        }
    }

    /**
     * Get the migration template file.
     *
     * @param  string|null  $table
     * @param  bool  $create
     * 
     * @return string
     */
    protected function getTemplate($table, $create): string
    {
        if (is_null($table)) {
            $template = $this->files->exists($customPath = $this->customTemplatePath.'/migration.tpl')
                ? $customPath
                : $this->templatePath().'/migration.tpl';
        } elseif ($create) {
            $template = $this->files->exists($customPath = $this->customTemplatePath.'/migration.create.tpl')
                ? $customPath
                : $this->templatePath().'/migration.create.tpl';
        } else {
            $template = $this->files->exists($customPath = $this->customTemplatePath.'/migration.update.tpl')
                ? $customPath
                : $this->templatePath().'/migration.update.tpl';
        }

        return $this->files->get($template);
    }

    /**
     * Populate the place-holders in the migration template.
     *
     * @param  string  $template
     * @param  string|null  $table
     * 
     * @return string
     */
    protected function populateTemplate($template, $table)
    {
        if ( ! is_null($table)) {
            $template = str_replace(
                ['DummyTable', '{{ table }}', '{{table}}'],
                $table, $template
            );
        }

        return $template;
    }

    /**
     * Get the class name of a migration name.
     *
     * @param  string  $name
     * 
     * @return string
     */
    protected function getClassName($name)
    {
        return Str::studlyCaps($name);
    }

    /**
     * Get the full path to the migration.
     *
     * @param  string  $name
     * @param  string  $path
     * 
     * @return string
     */
    protected function getPath($name, $path): string
    {
        return $path.'/'.$this->getDatePrefix().'_'.$name.'.php';
    }

    /**
     * Fire the registered post create hooks.
     *
     * @param  string|null  $table
     * @param  string  $path
     * 
     * @return void
     */
    protected function firePostCreateHooks($table, $path): void
    {
        foreach ($this->postCreate as $callback) {
            $callback($table, $path);
        }
    }

    /**
     * Register a post migration create hook.
     *
     * @param  \Closure  $callback
     * 
     * @return void
     */
    public function afterCreate(Closure $callback): void
    {
        $this->postCreate[] = $callback;
    }

    /**
     * Get the date prefix for the migration.
     *
     * @return string
     */
    protected function getDatePrefix(): string
    {
        return date('Y_m_d_His');
    }

    /**
     * Get the path to the templates.
     *
     * @return string
     */
    public function templatePath(): string
    {
        return __DIR__.'/templates';
    }

    /**
     * Get the filesystem instance.
     *
     * @return \Syscodes\Components\Filesystem\Filesystem
     */
    public function getFilesystem()
    {
        return $this->files;
    }
}