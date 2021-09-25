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

namespace Syscodes\Console\Concerns;

use Syscodes\Console\Util\Show;
use Syscodes\Console\Style\ColorTag;

/**
 * Trait ApplicationHelp.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
trait ApplicationHelp
{
    /**
     * Displays the version info.
     * 
     * @param  \Syscodes\Contracts\Console\Output  $output  The output interface implemented
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
                'sepChar'   => ' : ',
                'keyPadPos' => 'left',
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
        $logo       = '';
        $updateAt   = $this->getParam('updateAt', 'Unknown');
        $publishAt  = $this->getParam('publishAt', 'Unknown');
        $currentAt  = date('d.m.Y');
        $phpOS      = PHP_OS;
        $phpVersion = PHP_VERSION; 

        if ($logoTxt = $this->getLogoText()) {
            $logo = ColorTag::wrap($logoTxt, $this->getLogoStyle());
        }

        $info = [
            "$logo\n  {$this->getName()}, Version <brown>{$this->getVersion()}</brown>\n",
            'System Info'      => "PHP version <green>{$phpVersion}</green> on <green>{$phpOS}</green> system",
            'Application Info' => "Update at <green>{$updateAt}</green>, publish at <green>{$publishAt}</green> (current at {$currentAt})",
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
                return sprintf('%s <info>%s</info> (env: <comment>%s</comment>, debug: <comment>%s</comment>) [<magenta>%s</magenta>]', 
                    $this->getName(), 
                    $this->getVersion(),
                    env('APP_ENV'),
                    env('APP_DEBUG') ? 'true' : 'false',
			        PHP_OS
                );
            }

            return $this->getName();
        }

        return 'Lenevor CLI Application';
    }
}