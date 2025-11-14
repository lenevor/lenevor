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
 
namespace Syscodes\Components\Http;

use ArrayObject;
use JsonSerializable;
use InvalidArgumentException;
use Syscodes\Components\Contracts\Support\Jsonable;
use Syscodes\Components\Contracts\Support\Arrayable;
use Syscodes\Components\Contracts\Support\Renderable;

class_exists(ResponseHeaders::class);

/**
 * Response represents an HTTP response.
 */
class Response
{
	use Concerns\ResponseContent,
	    Concerns\ResponseStatusCode;

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
	public static function render($content = '', $status = 200, $headers = []): static
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
		return transform($this->content, fn ($content) => $content, '');
	}

	/**
	 * Sends the headers if they haven't already been sent. 
	 * Returns whether they were sent or not.
	 *
	 * @return static
	 */
	public function sendHeaders(): static
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
	 * @return static
	 */
	public function sendContent(): static
	{
		echo $this->content;

		return $this;
	}

	/**
	 * Sends the response to the output buffer. Optionally, headers will be sent. 
	 *
	 * @param  bool  $sendHeader  Whether or not to send the defined HTTP headers
	 *
	 * @return  static
	 */
	public function send($sendHeader = false): static
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
	 * @return static
	 */
	public function setContent($content): static
	{
		if ($this->shouldBeJson($content)) {
            $this->header('Content-Type', 'application/json');

            $content = $this->convertToJson($content);

            if ($content === false) {
                throw new InvalidArgumentException(json_last_error_msg());
            }
        } elseif ($content instanceof Renderable) {
			$content = $content->render();
		}
		
		$this->content = $content ?? '';

		return $this;
	}
	
	/**
	 * Determine if the given content should be turned into JSON.
	 * 
	 * @param  mixed  $content
	 * 
	 * @return bool
	 */
	protected function shouldBeJson($content): bool
	{
		return $content instanceof Arrayable ||
		       $content instanceof Jsonable ||
			   $content instanceof ArrayObject ||
			   $content instanceof JsonSerializable ||
			   is_array($content);
	}
	
	/**
	 * Convert the given content into JSON.
	 * 
	 * @param  mixed  $content
	 * 
	 * @return string|false
     */
	protected function convertToJson($content): string|false
	{
		if ($content instanceof Jsonable) {
			return $content->toJson();
		} elseif ($content instanceof Arrayable) {
			return json_encode($content->toArray());
		}
		
		return json_encode($content);
	}

	/**
	 * Prepares the Response before it is sent to the client.
	 * 
	 * @param  \Syscodes\Components\Http\Request  $request
	 * 
	 * @return static
	 */
	public function prepare($request): static
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