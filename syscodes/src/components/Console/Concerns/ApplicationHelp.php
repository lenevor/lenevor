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
     * @return int
     */
    public function displayVersionInfo($output) 
    {
        $output->writeln($this->makeVersionInfo());
    }

    /**
     * Returns the version of the console with logo.
     *
     * @return string
     */
    public function makeVersionInfo(): string
    {
        $updateAt  = $this->getParam('updateAt', 'Unknown');
        $publishAt = $this->getParam('publishAt', 'Unknown');

        if ($logoTxt = $this->getLogoText()) {
            $logo = ColorTag::wrap($logoTxt, $this->getLogoStyle());
        }

        $info = "$logo\n<hiBlue>{$this->getName()}</hiBlue>, Version <comment>{$this->getVersion()}</comment>".
                "\n\n<hiMagenta>Application Info</hiMagenta> : Update at <hiGreen>$updateAt</hiGreen>, publish at <hiGreen>$publishAt</hiGreen>";

        if ($hUrl = $this->getParam('homepage')) {
            $info .= "\n\t<hiMagenta>Homepage</hiMagenta> : <undersline>$hUrl</undersline>\n";
        } elseif ('' !== $this->getParam('homePage')) {
            $info .= "\n";
        }

        return $info;
    }

    /**
     * Returns the version of the console.
     *
     * @return string
     */
    public function getConsoleVersion()
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

        return 'Lenevor CLI Console';
    }
}