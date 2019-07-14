<?php

namespace Syscode\Http;

use BadMethodCallException;
use InvalidArgumentException;
use Syscode\Http\Contributors\{
	Parameters,
	Status
};
use Syscode\Filesystem\Exceptions\UnexpectedTypeException;

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
 * @author      Javier Alexander Campo M. <jalexcam@gmail.com>
 * @link        https://lenevor.com 
 * @copyright   Copyright (c) 2019 Lenevor Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.1.0
 */
class Response extends Status 
{
	use ResponseTrait;

   /**
    * Redirects to another url. Sets the redirect header, sends the headers and exits.
    * Can redirect via a Location header or using a refresh header.
    *
    * @param  string  $url     The url
    * @param  string  $method  The redirect method to use 'Location' or 'Refresh'
    * @param  int     $code    The redirect status code
    *
    * @return void
    *
    * @uses   \Syscode\Http\Uri
    */
	public static function redirect($url = '', $method = 'auto', $code = 302)
	{
		$response = new static;

		$response->setStatusCode($code);

		if (strpos($url, '://') === false)
		{
			$url = $url !== '' ? Uri::create($url) : Uri::base();
		}

		switch($method)
		{
			case 'refresh':
				$response->setHeader('Refresh', '0;url='.$url);
				break;
			default:
				$response->setHeader('Location', $url);
				break; 
		}

		$response->send(true);

		exit;
	}

	/**
	 * Creates an instance of the same response class for rendering contents to the content, 
	 * status code and headers.
	 *
	 * @param  string  $content  The response content  
	 * @param  int     $status   The HTTP response status for this response
	 * @param  array   $headers  Array of HTTP headers for this response
	 *
	 * @return response
	 */
	public static function render($content = null, $status = 200, $headers = [])
	{
		return new static($content, $status, $headers);
	}

	/**
	 * Sets up the response with a content and a status code.
	 *
	 * @param  string  $content  The response content
	 * @param  int     $status   The response status
	 * @param  array   $headers
	 *
	 * @return string
	 */
	public function __construct($content = null, int $status = 200, array $headers = [])
	{
		$this->setContent($content);
		$this->setStatusCode($status);

		$this->headers    = new Headers($headers);
		$this->parameters = new Parameters($_SERVER);
	}

	/**
	 * Gets the current response content.
	 * 
	 * @return string
	 */
	public function getContent()
	{
		return $this->content;
	}

	/**
	 * Gets the response status code.
	 *
	 * The status code is a 3-digit code to specify server response results to the browser.
	 *
	 * @return int
	 *
	 * @throws \BadMethodCallException
	 */
	public function getStatusCode()
	{
		if (empty($this->status))
		{
			throw new BadMethodCallException('HTTP Response is missing a status code.');
		}

		return $this->status;
	}

	/**
	 * Sends the headers if they haven't already been sent. Returns whether they were sent or not.
	 *
	 * @return bool
	 *
	 * @uses   \Syscode\Http\Http
	 */
	public function sendHeaders()
	{
		// Have the headers already been sent?
		if (headers_sent())
		{
			return $this;
		}

		// Valid headers
		$this->prepare();
			
		// Headers
		foreach ($this->headers->all() as $name => $value) 
		{
			// Parse non-replace headers
			if (is_int($name) && is_array($value))
			{
				isset($value[0]) && $name = $value[0];
				isset($value[1]) && $name = $value[1];
			}

			// Create the header
			is_string($name) && $value = "{$name}: {$value}";

			header($value, true, $this->status);
		}

		// Status
		if ( ! empty($_SERVER['FCGI_SERVER_VERSION']))
		{
			// Send the protocol/status line first, FCGI servers need different status header
			header(sprintf('Status: %s %s', $this->status, $this->statusText));
		}
		else
		{
			$this->protocol = (string) $this->parameters->get('SERVER_PROTOCOL') ?: 'HTTP/1.1';
			header(sprintf('%s %s %s', $this->protocol, $this->status, $this->statusText), true, $this->status);
		}
	}

	/**
	 * Sends content for the current web response.
	 * 
	 * @return $this
	 */
	public function sendContent()
	{
		echo $this->content;

		return $this;
	}

	/**
	 * Sends the response to the output buffer. Optionally, headers will be sent. 
	 *
	 * @param  bool  $sendHeader  Whether or not to send the defined HTTP headers
	 *
	 * @return void
	 */
	public function send($sendHeader = false)
	{
		if ($sendHeader)
		{
			$this->sendHeaders();
		}

		if ($this->content != null) 
		{
			$this->sendContent();
		}
	}

	/**
	 * Sends the content of the message to the browser.
	 *
	 * @param  string  $content  The response content
	 *
	 * @return $this
	 */
	public function setContent($content)
	{
		if ($content !== null && ! is_string($content) && ! is_numeric($content) && ! is_callable([$content, '__toString'])) {
            throw new UnexpectedValueException(sprintf('The Response content must be a string or object implementing __toString(), "%s" given.', gettype($content)));
		}
		
		$this->content = (string) $content;

		return $this;
	}

	/**
	 * Prepares the Response before it is sent to the client.
	 * 
	 * @return void
	 */
	public function prepare()
	{
		$headers = $this->headers;

		if ($this->isInformational() || $this->isEmpty()) 
		{
            $this->setContent(null);
			$headers->remove('Content-Type');
            $headers->remove('Content-Length');
		}
	}

	/**
	* Sets the response status code.
	*
	* @param  int          $code  The status code
	* @param  string|null  $text  The status text
	*
	* @return $this
	*
	* @throws \InvalidArgumentException
	*/
	public function setStatusCode(int $code = 200, $text = null)
	{
		$this->status = $code; 

		// Valid range?
		if ($this->isInvalid())
		{
			throw new InvalidArgumentException(sprintf("[%s] is not a valid HTTP return status code", $code));
		}

		if ($text === null)
		{
			$this->statusText = isset($this->statusCodes[$code]) ? $this->statusCodes[$code] : 'Unknown status';

			return $this;
		}

		if ($text === false)
		{
			$this->statusText = '';

			return $this;
		}

		$this->statusText = $text;

		return $this;
	}

	/**
	 * Is response invalid?
	 * 
	 * @final
	 * 
	 * @return void
	 */
	public function isInvalid(): bool
	{
		return $this->status < 100 || $this->status >= 600;
	}

	/**
     * Is response informative?
     *
     * @final
	 * 
	 * @return void
     */
    public function isInformational()
    {
        return $this->status >= 100 && $this->status < 200;
    }

	/**
     * Is the response empty?
     *
     * @final
	 * 
	 * @return void
     */
    public function isEmpty()
    {
        return in_array($this->status, [204, 304]);
    }

	/**
	 * Returns the Response as an HTTP string.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return sprintf('%s %s %s', $this->protocol, $this->status, $this->statusText)."\r\n".
            $this->headers."\r\n".
            $this->getContent();
	}

	/**
	 * Clone the current Response instance.
	 */
	public function __clone()
	{
		$this->headers = clone $this->headers;
	}
}