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

namespace Syscodes\Bundles\ApplicationBundle\Console\Commands;

use Syscodes\Components\Console\Util\Show;
use Syscodes\Components\Console\Application;
use Syscodes\Components\Console\Style\ColorTag;
use Syscodes\Components\Console\Command\Command;

/**
 * A console command to display information about of system.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class AboutCommand extends Command
{
    protected static $defaultName = 'about';
    protected static $defaultDescription = 'Display information about the current project';

    /**
     * {@inheritdoc}
     */
    protected function define()
    {
        $this
            ->setName('about')
            ->setDescription(static::$defaultDescription)
            ->setHelp(<<<'EOT'
            The <green>%command-name%</green> command displays information about the current Lenevor project.
            
            The <green>PHP</green> section displays important configuration that could affect your application. The values might
            be different between web and CLI.
            EOT
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output) 
    {
        Show::sList(
            $this->makeVersionInfo($this->getApplication()), 
            '', 
            [
                'leftChar'  => '',
                'sepChar'   => ' : ',
                'keyPadPos' => 'left',
            ],
            $output
        );

        return 0;
    }

    /**
     * Returns the version of the console with logo.
     *
     * @return array
     */
    public function makeVersionInfo(Application $application): array
    {
        $logo       = '';
        $updateAt   = $application->getParam('updateAt', 'Unknown');
        $publishAt  = $application->getParam('publishAt', 'Unknown');
        $currentAt  = date('d.m.Y');
        $phpOS      = PHP_OS;
        $phpVersion = PHP_VERSION; 

        if ($logoTxt = $application->getLogoText()) {
            $logo = ColorTag::wrap($logoTxt, $application->getLogoStyle());
        }

        $info = [
            "$logo\n  {$application->getName()}, Version <brown>{$application->getVersion()}</brown>\n",
            'System Info'      => "PHP version <green>{$phpVersion}</green> on <green>{$phpOS}</green> system",
            'Application Info' => "Update at <green>{$updateAt}</green>, publish at <green>{$publishAt}</green> (current at {$currentAt})",
        ];

        if ($hUrl = $application->getParam('homepage')) {
            $info['Homepage URL'] = "<undersline>$hUrl</undersline>";
        } 

        return $info;
    }
}