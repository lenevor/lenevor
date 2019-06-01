<?php

namespace Syscode\Http;

use BadMethodCallException;
use InvalidArgumentException;
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
	public static function render($content = null, $status = 200, array $headers = [])
	{
		$response = new static($content, $status, $headers);

		return $response;
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
		foreach ($headers as $key => $value)
		{
			$this->setHeader($key, $value);
		}

		$this->setContent($content);
		$this->setStatusCode($status);
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
	 * Get a HTTP response header.
	 *
	 * @param  string|null  $name  The header name, or null for all headers
	 *
	 * @return mixed
	 */
	public function getHeader($name = null)
	{
		if (func_num_args())
		{
			return isset($this->headers[$name]) ? $this->headers[$name] : null;
		}
		else
		{
			return $this->headers;
		}
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
			
		// Headers
		foreach ($this->headers as $name => $value) 
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
			$protocol = (string) Http::server('SERVER_PROTOCOL') ?: 'HTTP/1.1';
			header(sprintf('%s %s %s', $protocol, $this->status, $this->statusText), true, $this->status);
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
	 * Returns all the headers.
	 * 
	 * @return array
	 */
	public function all()
	{
		return $this->headers;
	}

	/**
	 * Adds a header to the queue.
	 * 
	 * @param  string       $name     The header name
	 * @param  string       $value    The header value
	 * @param  string|bool  $replace  If you want to replace the value exists by the header, it is not overwritten / overwritten when it is false
	 *
	 * @return $this
	 */
	public function setHeader($name, $value, $replace = true)
	{
		if ($replace)
		{
			$this->headers[$name] = $value;
		}
		else
		{
			$this->headers[] = [$name, $value];
		}

		return $this;
	}

	/**
	 * Adds multiple header to the queue.
	 *
	 * @param  array        $header   The header name
	 * @param  string|bool  $replace  If you want to replace the value exists by the header, it is not overwritten / overwritten when it is false
	 *
	 * @return mixed
	 */
	public function setHeaders($header, $replace = true)
	{
		foreach ($header as $key => $value) 
		{
			$this->setHeader($key, $value, $replace);
		}

		return $this;
	}

	/**
	* Sets the response status code.
	*
	* @param  int  $code  The status code
	*
	* @return $this
	*
	* @throws \InvalidArgumentException
	*/
	public function setStatusCode($code = 200, $text = null)
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
	 * Returns the Response as an HTTP string.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return 
			sprintf('%s %s %s', $protocol, $this->status, $this->statusText)."\r\n".
			$this->getHeader()."\r\n".
			$this->getContent();
	}

}