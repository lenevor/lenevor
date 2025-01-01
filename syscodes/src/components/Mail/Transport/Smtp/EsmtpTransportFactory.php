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

namespace Syscodes\Components\Mail\Transport\Smtp;

use Syscodes\Components\Mail\Transport\DomainTransport;
use Syscodes\Components\Mail\Transport\AbstractTransportFactory;

/**
 * Sends Emails over SMTP with ESMTP factory support.
 */
class EsmtpTransportFactory extends AbstractTransportFactory
{
    /**
     * Create the filters for allows the connection with the server.
     * 
     * @param  DomainTransport  $domain
     * 
     * @return \Syscodes\Components\Mail\Transport\Smtp\EsmtpTransport
     */
    public function create(DomainTransport $domain)
    {
        $autoTls = '' === $domain->getOption('auto_tls') || filter_var($domain->getOption('auto_tls', true), \FILTER_VALIDATE_BOOL);
        $tls     = 'smtps' === $domain->getScheme() ? true : ($autoTls ? null : false);
        $port    = $domain->getPort(0);
        $host    = $domain->getHost();
        
        $transport = new EsmtpTransport($host, $port, $tls, $this->dispatcher, $this->logger);
        /** @var SocketStream $stream */
        $stream        = $transport->getStream();
        $streamOptions = $stream->getStreamOptions();
        
        if ('' !== $domain->getOption('verify_peer') && ! filter_var($domain->getOption('verify_peer', true), FILTER_VALIDATE_BOOL)) {
            $streamOptions['ssl']['verify_peer'] = false;
            $streamOptions['ssl']['verify_peer_name'] = false;
        }
        
        if (null !== $peerFingerprint = $domain->getOption('peer_fingerprint')) {
            $streamOptions['ssl']['peer_fingerprint'] = $peerFingerprint;
        }
        
        $stream->setStreamOptions($streamOptions);
        
        if ($user = $domain->getUser()) {
            $transport->setUsername($user);
        }
        
        if ($password = $domain->getPassword()) {
            $transport->setPassword($password);
        }
        
        if (null !== ($localDomain = $domain->getOption('local_domain'))) {
            $transport->setLocalDomain($localDomain);
        }
        
        if (null !== ($maxPerSecond = $domain->getOption('max_per_second'))) {
            $transport->setMaxToSeconds((float) $maxPerSecond);
        }
        
        return $transport;
    }
    
    /**
     * Get the supported schemes.
     * 
     * @return array
     */
    protected function getSupportedSchemes(): array
    {
        return ['smtp', 'smtps'];
    }
}