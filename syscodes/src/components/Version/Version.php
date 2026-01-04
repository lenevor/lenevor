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

namespace Syscodes\Components;

/**
 * Loads the version of system.
 */
final class Version 
{
    /**
     * The framework name.
     */
    public const NAME = 'Lenevor Framework';

    /** 
     * Lenevor's version.
     */
    public const RELEASE = '0.8.16';

    /**
     * Release status.
     */
    public const STATUS = 'alpha';

    /**
     * The codename in key.
     */
    public const CODENAME = 'Polaris';

    /**
     * Data version.
     */
    public const RELEASEDATE = 'Created 02-May-2019';

    /**
     * Copyright information.
     */
    public const COPYRIGHT = 'All rights reserved';
    
    /**
     * Product copyrighting.
     */
    public const COPY = '©';

    /**
     * Year actual.
     */
    public const YEAR = '2026';

    /**
     * Gets a string version of "PHP normalized" for the Lenevor Framework.
     *
     * @return string  Short version
     */
    public static function shortVersion(): string
    {
        return self::COPY.' '.self::YEAR.' '.self::NAME; 
    }

    /**
     * Gets a string version Lenevor under real All information Release.
     * 
     * @return string  Complete version
     */
    public static function longVersion(): string
    {
        return self::COPY.' '.self::YEAR.' '.self::COPYRIGHT.' - '.self::NAME.' ' .self::RELEASE. ' '. 
               self::STATUS.' [ '.self::CODENAME.' ] '.self::RELEASEDATE;
    }
}