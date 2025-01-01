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

namespace Syscodes\Components\Finder;

use SplFileInfo as BaseSplFileInfo;

/**
 * Allows have the interface to information for an individual file. 
 */
class SplFileInfo extends BaseSplFileInfo
{
    /**
     * Gets the relative path.
     * 
     * @var string $relativePath
     */
    protected $relativePath;

    /**
     * Gets the relative path name.
     * 
     * @var string $relativePathname
     */
    protected $relativePathname;

    /**
     * Constructor. Create a new SplFileInfo class instance.
     * 
     * @param  string  $file  The file name
     * @param  string  $relativePath  The relative path
     * @param  string  $relativePathname  The relative path name
     * 
     * @return void
     */
    public function __construct($file, $relativePath, $relativePathname)
    {
        parent::__construct($file);

        $this->relativePath     = $relativePath;
        $this->relativePathname = $relativePathname;
    }

    /**
     * Get teh relative path.
     * 
     * @return string
     */
    public function getRelativePath(): string
    {
        return $this->relativePath;
    }

    /**
     * Get teh relative path.
     * 
     * @return string
     */
    public function getRelativePathname(): string
    {
        return $this->relativePathname;
    }
}