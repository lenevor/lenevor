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

namespace Syscodes\Components\Core\Console\Commands;

use Locale;
use Symfony\Component\Console\Attribute\AsCommand;
use Syscodes\Components\Console\Application;
use Syscodes\Components\Console\Command;
/**
 * A console command to display information about of system.
 */
#[AsCommand(name: 'about')]
class AboutCommand extends Command
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $name = 'about';    

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display basic information about your application';

    /**
     * Gets input definition for command.
     * 
     * @return void
     */
    protected function define()
    {
        $this->setHelp(<<<'EOT'
             The <comment>%command-name%</> command displays information about the current Lenevor project.
            
             The <comment>PHP</> section displays important configuration that could affect your application. The values might
             be different between web and CLI.
             EOT
        );
    }

    /**
     * Executes the current command.
     * 
     * @return int
     */
    public function handle()
    {
        echo $this->buildInfo($this->getApplication());

        return 0;
    }

    /**
     * Returns the info of the console with logo.
     * 
     * @param  \Syscodes\Components\Console\Application  $application
     *
     * @return string
     */
    public function buildInfo(Application $application): string
    {
        $logo         = '';
        $phpVersion   = \PHP_VERSION;
        $phpVersion   = \PHP_VERSION;
        $architecture = \PHP_INT_SIZE * 16;
        $locale       = class_exists(Locale::class, false) && Locale::getDefault() ? Locale::getDefault() : 'n/a';

        $info = "$logo\n";
        $info .= "  {$application->getName()} Version ".$application->getVersion()."\n";
        $info .= "  Core\n";
        $info .= "  Environment: ". env('APP_ENV')."\n";
        $info .= "  Debug: ". (env('APP_DEBUG') ? "True\n" : "False\n");
        $info .= "  PHP Info\n";
        $info .= "  Version: "."{$phpVersion}\n";
        $info .= "  Architecture: "."{$architecture} bits\n";
        $info .= "  Intl Locale: "."{$locale}\n";

        return $info;
    }
}