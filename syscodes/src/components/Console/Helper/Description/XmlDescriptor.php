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

namespace Syscodes\Components\Console\Helper\Description;

use Syscodes\Components\Console\Application;
use Syscodes\Components\Console\Command\Command;
use Syscodes\Components\Console\Input\InputOption;
use Syscodes\Components\Console\Input\InputArgument;
use Syscodes\Components\Console\Input\InputDefinition;
use Syscodes\Components\Contracts\Console\Output as OutputInterface;
use Syscodes\Components\Console\Helper\Description\ApplicationDescription;

/**
 * Xml descriptor.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class XmlDescriptor extends Descriptor
{
    /**
     * The output interface implementation.
     * 
     * @var \Syscodes\Components\Contracts\Console\Output $output
     */
    protected $output;

    /**
     * {@inheritdoc}
     */
    protected function describeArgument(InputArgument $argument, array $options = [])
    {
        $this->writeText('hola mundo!!!');
    }

     /**
     * {@inheritdoc}
     */
    protected function describeOption(InputOption $option, array $options = [])
    {
        $this->writeText('hola mundo!!!');
    }

    /**
     * {@inheritdoc}
     */
    protected function describeDefinition(InputDefinition $definition, array $options = [])
    {
        $this->writeText('hola mundo!!!');
    }
    
    /**
     * {@inheritdoc}
     */
    protected function describeCommand(Command $command, array $options = [])
    {
        $this->writeText(' Hola command!!! ');
    }

    /**
     * {@inheritdoc}
     */
    protected function describeApplication(Application $application, array $options = [])
    {
        $this->writeText(' Hola application!!! ');
    } 

    /**
     * {@inheritdoc}
     */
    private function writeText(string $content)
    {
        $this->write($this->output->note($content));
    }
}