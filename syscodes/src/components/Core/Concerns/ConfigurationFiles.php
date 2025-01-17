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

namespace Syscodes\Components\Core\Concerns;

use SplFileInfo;
use Syscodes\Components\Finder\Finder;
use Syscodes\Components\Contracts\Core\Application;

/**
 * Get the configuration files of system.
 */
trait ConfigurationFiles
{
    /**
     * Get all of the configuration files for the application.
     * 
     * @param  \Syscodes\Components\Contracts\Core\Application  $app
     * 
     * @return array
     */
    protected function getConfigurationFiles(Application $app): array
    {
        $files = [];
        
        $configPath = realpath($app->configPath());
        
        if ( ! $configPath) {
            return [];
        }
        
        foreach (Finder::create()->files()->name('*.php')->in($configPath) as $file) {
            $directory = $this->getNestedDirectory($file, $configPath);
            $files[$directory.basename($file->getRealPath(), '.php')] = $file->getRealPath();
        }
        
        ksort($files, SORT_NATURAL);
        
        return $files;
    }
    
    /**
     * Get the configuration file nesting path.
     * 
     * @param  SplFileInfo  $file
     * @param  string  $configPath
     * 
     * @return string
     */
    protected function getNestedDirectory(SplFileInfo $file, $configPath): string
    {
        $directory = $file->getPath();
        
        if ($nested = trim(str_replace($configPath, '', $directory), DIRECTORY_SEPARATOR)) {
            $nested = str_replace(DIRECTORY_SEPARATOR, '.', $nested).'.';
        }
        
        return $nested;
    }
}