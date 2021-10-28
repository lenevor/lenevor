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

namespace Syscodes\Components\Console\Helper;

use InvalidArgumentException;
use Syscodes\Components\Console\Helper\Description\XmlDescriptor;
use Syscodes\Components\Console\Helper\Description\TextDescriptor;
use Syscodes\Components\Contracts\Console\Output as OutputInterface;
use Syscodes\Components\Contracts\Console\Descriptor as DescriptorInterface;

/**
 * This class adds helper method to describe objects in various formats.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class DescriptorHelper
{
    /**
     * The descriptor instance.
     * 
     * @var \Syscodes\Components\Contracts\Console\Descriptor[] $descriptor
     */
    protected $descriptor = [];

    /**
     * Constructor. Create a new DescriptorHelper instance.
     * 
     * @return void
     */
    public function __construct()
    {
        $this
            ->register('txt', new TextDescriptor())
            ->register('xml', new XmlDescriptor())
        ;
    }
    
    /**
     * Describes an object if supported.
     * 
     * Available options are:
     * * format: string, the output format name
     * * raw_text: boolean, sets output type as raw
     * 
     * @param  \Syscodes\Components\Contracts\Console\Output  $output
     * @param  object  $object
     * @param  array  $options
     * 
     * @return void
     * 
     * @throws \InvalidArgumentException  when the given format is not supported
     */
    public function describe(OutputInterface $output, object $object, array $options = [])
    {
        $options = array_merge([
            'raw_text' => false,
            'format' => 'txt',
        ], $options);
        
        if ( ! isset($this->descriptor[$options['format']])) {
            throw new InvalidArgumentException(sprintf('Unsupported format "%s".', $options['format']));
        }
        
        $descriptor = $this->descriptor[$options['format']];
        $descriptor->describe($output, $object, $options);
    }
    
    /**
     * Registers a descriptor.
     * 
     * @param  string  $format
     * @param  \Syscodes\Components\Contracts\Console\Descriptor  $descriptor
     * 
     * @return self
     */
    public function register(string $format, DescriptorInterface $descriptor): self
    {
        $this->descriptor[$format] = $descriptor;
        
        return $this;
    }
}