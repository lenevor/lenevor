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
 
namespace Syscodes\Components\Http;

use JsonSerializable;
use UnexpectedValueException;
use Syscodes\Components\Http\Concerns\HttpResponse;
use Syscodes\Components\Contracts\Support\Renderable;
use Syscodes\Components\Http\Concerns\HttpStatusCode;
use Syscodes\Components\Http\Response\ResponseHeaders;

/**
 * Response represents an HTTP response.
 */
class Response
{
	use HttpResponse,
        HttpStatusCode;

	/**
	 * Sets up the response with a content and a status code.
	 *
	 * @param  mixed  $content  The response content 
	 * @param  int  $status  The response status  
	 * @param  array  $headers  Array of HTTP headers for this response
	 *
	 * @return string
	 */
	public function __construct($content = '', int $status = 200, array $headers = [])
	{
		$this->headers = new ResponseHeaders($headers);
		
		$this->setContent($content);
		$this->setStatusCode($status);
		$this->setProtocolVersion('1.0');
	}

	/**
	 * Creates an instance of the same response class for rendering contents to the content, 
	 * status code and headers.
	 *
	 * @param  mixed  $content  The response content  
	 * @param  int  $status  The HTTP response status for this response  
	 * @param  array  $headers  Array of HTTP headers for this response
	 *
	 * @return static
	 */
	public static function render($content = '', $status = 200, $headers = [])
	{
		return new static($content, $status, $headers);
	}

	/**
	 * Gets the current response content.
	 * 
	 * @return string
	 */
	public function getContent(): string
	{
		return $this->content;
	}

	/**
	 * Sends the headers if they haven't already been sent. 
	 * Returns whether they were sent or not.
	 *
	 * @return self
	 */
	public function sendHeaders(): self
	{
		// Have the headers already been sent?
		if (headers_sent()) {
			return $this;
		}

		// Headers
		foreach ($this->headers->allPreserveCaseWithoutCookies() as $name => $values) {
			$replace = 0 === strcasecmp($name, 'Content-Type');

			foreach ($values as $value) {
				header($name.': '. $value, $replace, $this->statusCode);
			}
		}
		
		// Cookies
		foreach ($this->headers->getCookies() as $cookie) {
		 	header('Set-Cookie: '.$cookie, false, $this->statusCode);
		}
		
		// Status
		if ( ! empty($_SERVER['FCGI_SERVER_VERSION'])) {
			// Send the protocol/status line first, FCGI servers need different status header
			header(sprintf('Status: %s %s', $this->statusCode, $this->statusText));
		} else {
			header(sprintf('HTTP/%s %s %s', $this->version, $this->statusCode, $this->statusText), true, $this->statusCode);
		}

		return $this;
	}

	/**
	 * Sends content for the current web response.
	 * 
	 * @return self
	 */
	public function sendContent(): self
	{
		echo $this->content;

		return $this;
	}

	/**
	 * Sends the response to the output buffer. Optionally, headers will be sent. 
	 *
	 * @param  bool  $sendHeader  Whether or not to send the defined HTTP headers
	 *
	 * @return  self
	 */
	public function send($sendHeader = false): self
	{
		if ($sendHeader) {
			$this->sendHeaders();
		}

		if (null !== $this->content) {
			$this->sendContent();
		}

		return $this;
	}

	/**
	 * Sends the content of the message to the browser.
	 *
	 * @param  mixed  $content  The response content
	 *
	 * @return self
	 */
	public function setContent($content): self
	{
		if (null !== $content && ! is_string($content) && ! is_numeric($content) &&
			! is_bool($content) && ! is_object($content) && ! is_callable([$content, '__toString'])) {
			throw new UnexpectedValueException(
				sprintf('The Response content must be a string or object implementing __toString(), "%s" given', gettype($content)
			));
		}

		if ($content instanceof JsonSerializable || is_array($content)) {
			$this->header('Content-Type', 'application/json');

			$content = json_encode($content);
		} elseif ($content instanceof Renderable) {
			$content = $content->render();
		}
		
		$this->content = $content ?? '';

		return $this;
	}

	/**
	 * Prepares the Response before it is sent to the client.
	 * 
	 * @param  \Syscodes\Components\Http\Request  $request
	 * 
	 * @return self
	 */
	public function prepare($request): self
	{
		$headers = $this->headers;

		if ($this->isInformational() || $this->isEmpty()) {
			$this->setContent(null);
			$headers->remove('Content-Type');
			$headers->remove('Content-Length');
		}
		
		// Fix protocol
		if ('HTTP/1.0' != $request->server->get('SERVER_PROTOCOL')) {
			$this->setProtocolVersion('1.1');
		}

		return $this;
	}
	
	/**
	 * Magic method.
	 * 
	 * Returns the Response as an HTTP string.
	 * 
	 * @return string
	 */
	public function __toString(): string
	{
		return sprintf('HTTP/%s %s %s', $this->version, $this->statusCode, $this->statusText)."\r\n".
			$this->headers."\r\n".
			$this->getContent();
	}
	
	/**
	 * Magic method.
	 * 
	 * Clone the current Response instance.
	 * 
	 * @return void
	 */
	public function __clone()
	{
		$this->headers = clone $this->headers;
	}
}