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
 * @copyright   Copyright (c) 2019 - 2024 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Mail\Transport;

use Psr\Log\LoggerInterface;
use Syscodes\Components\Contracts\Events\Dispatcher;

/**
 * Get the transport factory for send of messages.
 */
abstract class AbstractTransportFactory
{
    /**
     * Get the dispatcher event instance.
     * 
     * @var Dispatcher $dispatcher
     */
    protected ?Dispatcher $dispatcher;
    
    /**
     * Get the logger instance.
     * 
     * @var LoggerInterface $logger
     */
    protected ?LoggerInterface $logger;
    
    /**
     * Constructor. Create a new AbstractTransportFactory class instance.
     * 
     * @param  Dispatcher|null  $dispatcher
     * @param  LoggerInterface|null  $logger
     * 
     * @return void
     */
    public function __construct(Dispatcher $dispatcher = null, LoggerInterface $logger = null)
    {
        $this->dispatcher = $dispatcher;
        $this->logger     = $logger;
    }
    
    /**
     * Get the supported schemes.
     * 
     * @return array
     */
    abstract protected function getSupportedSchemes(): array;
}