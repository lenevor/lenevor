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

namespace Syscodes\Components\Core;

use Exception;
use Syscodes\Components\Filesystem\Filesystem;
use Syscodes\Components\Support\Collection;
use Syscodes\Components\Support\Environment;

/**
 * Allows generate the package manifest.
 */
class PackageManifest
{
    /**
     * The filesystem instance.
     *
     * @var \Syscodes\Components\Filesystem\Filesystem
     */
    public $files;

    /**
     * The base path.
     *
     * @var string
     */
    public $basePath;

    /**
     * The vendor path.
     *
     * @var string
     */
    public $vendorPath;

    /**
     * The manifest path.
     *
     * @var string|null
     */
    public $manifestPath;

    /**
     * The loaded manifest array.
     *
     * @var array
     */
    public $manifest;

    /**
     * Constructor. Create a new package manifest instance.
     *
     * @param  \Syscodes\Components\Filesystem\Filesystem  $files
     * @param  string  $basePath
     * @param  string  $manifestPath
     * 
     * @return voiod
     */
    public function __construct(Filesystem $files, $basePath, $manifestPath)
    {
        $this->files = $files;
        $this->basePath = $basePath;
        $this->manifestPath = $manifestPath;
        $this->vendorPath = Environment::get('COMPOSER_VENDOR_DIR') ?: $basePath.'/vendor';
    }

    /**
     * Get all of the service provider class names for all packages.
     *
     * @return array
     */
    public function providers(): array
    {
        return $this->config('providers');
    }

    /**
     * Get all of the aliases for all packages.
     *
     * @return array
     */
    public function aliases(): array
    {
        return $this->config('aliases');
    }

    /**
     * Get all of the values for all packages for the given configuration name.
     *
     * @param  string  $key
     * 
     * @return array
     */
    public function config($key): array
    {
        return (new Collection($this->getManifest()))
            ->flatMap(fn ($configuration) => (array) ($configuration[$key] ?? []))
            ->filter()
            ->all();
    }

    /**
     * Get the current package manifest.
     *
     * @return array
     */
    protected function getManifest(): array
    {
        if ( ! is_null($this->manifest)) {
            return $this->manifest;
        }

        if ( ! is_file($this->manifestPath)) {
            $this->build();
        }

        return $this->manifest = is_file($this->manifestPath) ?
            $this->files->getRequire($this->manifestPath) : [];
    }

    /**
     * Build the manifest and write it to disk.
     *
     * @return void
     */
    public function build(): void
    {
        $packages = [];

        if ($this->files->exists($path = $this->vendorPath.'/composer/installed.json')) {
            $installed = json_decode($this->files->get($path), true);

            $packages = $installed['packages'] ?? $installed;
        }

        $ignoreAll = in_array('*', $ignore = $this->packagesToIgnore());

        $this->write((new Collection($packages))->mapKeys(function ($package) {
            return [$this->format($package['name']) => $package['extra']['lenevor'] ?? []];
        })->each(function ($configuration) use (&$ignore) {
            $ignore = array_merge($ignore, $configuration['dont-discover'] ?? []);
        })->reject(function ($configuration, $package) use ($ignore, $ignoreAll) {
            return $ignoreAll || in_array($package, $ignore);
        })->filter()->all());
    }

    /**
     * Format the given package name.
     *
     * @param  string  $package
     * 
     * @return string
     */
    protected function format($package): string
    {
        return str_replace($this->vendorPath.'/', '', $package);
    }

    /**
     * Get all of the package names that should be ignored.
     *
     * @return array
     */
    protected function packagesToIgnore(): array
    {
        if ( ! is_file($this->basePath.'/composer.json')) {
            return [];
        }

        return json_decode(file_get_contents(
            $this->basePath.'/composer.json'
        ), true)['extra']['lenevor']['dont-discover'] ?? [];
    }

    /**
     * Write the given manifest array to disk.
     *
     * @param  array  $manifest
     * 
     * @return void
     *
     * @throws \Exception
     */
    protected function write(array $manifest): void
    {
        if ( ! is_writable($dirname = dirname($this->manifestPath))) {
            throw new Exception("The {$dirname} directory must be present and writable.");
        }

        $this->files->replace(
            $this->manifestPath, '<?php return '.var_export($manifest, true).';'
        );
    }
}