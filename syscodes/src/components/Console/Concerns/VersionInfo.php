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
 * @copyright   Copyright (c) 2019 - 2021 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Console\Concerns;

use Locale;
use Syscodes\Components\Console\Util\Show;
use Syscodes\Components\Console\Style\ColorTag;

/**
 * Trait VersionInfo.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
trait VersionInfo
{
    /**
     * Displays the version info.
     * 
     * @param  \Syscodes\Components\Contracts\Console\Output  $output  The output interface implemented
     * 
     * @return void
     */
    public function displayVersionInfo($output): void
    {
        Show::sList(
            $this->makeVersionInfo(), 
            '', 
            [
                'leftChar'  => '',
                'sepChar'   => '    ',
                'keyPadPos' => 'right',
            ],
            $output
        );
    }

    /**
     * Returns the version of the console with logo.
     *
     * @return array
     */
    public function makeVersionInfo(): array
    {
        $logo         = '';
        $updateAt     = $this->getParam('updateAt', 'Unknown');
        $publishAt    = $this->getParam('publishAt', 'Unknown');
        $currentAt    = date('d.m.Y');
        $phpOS        = \PHP_OS;
        $phpVersion   = \PHP_VERSION; 
        $architecture = \PHP_INT_SIZE * 8;
        $locale       = class_exists(Locale::class, false) && Locale::getDefault() ? Locale::getDefault() : 'n/a';

        if ($logoTxt = $this->getLogoText()) {
            $logo = ColorTag::wrap($logoTxt, $this->getLogoStyle());
        }

        $info = [
            "$logo\n",
            "  <hiGreen>{$this->getName()}</hiGreen>\n",
            "  Version"      => "{$this->getVersion()}",
            "  Publish at"   => "{$publishAt}",
            "  Update at"    => "{$updateAt}\n",
            "  <hiGreen>Core</hiGreen>\n",
            "  Environment"  => env('APP_ENV'),
            "  Debug"        => (env('APP_DEBUG') ? "True" : "False")."\n",
            "  <hiGreen>PHP Info</hiGreen>\n",
            "  Version "     => "{$phpVersion}",
            "  Architecture" => "{$architecture} bits",
            "  Intl Locale"  => "{$locale}",
            
        ];

        if ($hUrl = $this->getParam('homepage')) {
            $info['Homepage URL'] = "<undersline>$hUrl</undersline>";
        } 

        return $info;
    }

    /**
     * Returns the version of the console.
     *
     * @return string
     */
    public function getConsoleVersion(): string
    {
        if ('UNKNOWN' !== $this->getName()) {
            if ('UNKNOWN' !== $this->getVersion()) {
                return sprintf('%s <info>%s</>', 
                    $this->getName(), 
                    $this->getVersion()
                );
            }

            return $this->getName();
        }

        return 'Lenevor CLI Application';
    }
}