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
 * @copyright   Copyright (c) 2019 - 2023 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Http\Response;

use InvalidArgumentException;
use Syscodes\Components\Http\Response;

/**
 * Redirects to another URL. Sets the redirect header, sends the headers and exits.
 * Can redirect via a Location header or using a Refresh header.
 */
class RedirectResponseHeader extends Response
{
    /**
     * The target URL.
     * 
     * @var string $targetUrl
     */
    protected $targetUrl;

    /**
     * Constructor. Creates a redirect response so that it conforms to the rules 
     * defined for a redirect status code.
     * 
     * @param  string  $url  The URL to redirect to
     * @param  int  $status  The redirect status code  
     * @param  array  $headers  The header array
     * 
     * @return void
     * 
     * @throws \InvalidArgumentException
     */
    public function __construct(?string $url, int $status = 302, array $headers = [])
    {
        if (null === $url) {
            @trigger_error(sprintf('Passing a null url when instantiating a "%s"', __CLASS__), E_USER_DEPRECATED);
            
            $url = '';
        }

        parent::__construct('', $status, $headers);

        $this->setTargetUrl($url);

        if ( ! $this->isRedirect()) {
            throw new InvalidArgumentException(sprintf('The HTTP status code is not a redirect ("%s" given).', $status));
        }

        // Loaded the headers and status code
        $this->send(true);

        // Terminate the current script 
        exit;
    }

    /**
     * Creates an instance of the same redirect class for rendering URL's to the url, method rules defined
     * status code and headers.
     * 
     * @param  mixed  $url  The URL to redirect to
     * @param  int  $status  The HTTP response status for this response  
     * @param  array  $headers  Array of HTTP headers for this response
     * 
     * @return static
     */
    public static function render($url = '', $status = 302, $headers = [])
    {
        return new static($url, $status, $headers);
    }

    /**
     * Returns the target URL.
     * 
     * @return string
     */
    public function getTargetUrl(): string
    {
        return $this->targetUrl;
    }

    /**
    * Redirects to another url. Sets the redirect header, sends the headers and exits.
    * Can redirect via a Location header.
    *
    * @param  string  $url  The url
    *
    * @return self
    */
    public function setTargetUrl($url): self
    {
        if ('' === ($url ?? '')) {
            throw new InvalidArgumentException('Cannot redirect to an empty URL');
        }
        
        $this->targetUrl = $url;
        
        $this->setContent(sprintf('<!DOCTYPE html>
    <html>
        <head>
            <meta charset="UTF-8" />
            <meta http-equiv="refresh" content="0;url=%1$s" />
            <title>Redirecting to %1$s</title>
        </head>
        <body>
            Redirecting to <a href="%1$s">%1$s</a>.
        </body>
    </html>', htmlspecialchars($url, ENT_QUOTES, 'UTF-8')));
    
        $this->headers->set("Location", $url);
        
        return $this;
    }
}