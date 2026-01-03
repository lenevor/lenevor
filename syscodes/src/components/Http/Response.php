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
 
namespace Syscodes\Components\Http;

use ArrayObject;
use InvalidArgumentException;
use JsonSerializable;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Syscodes\Components\Contracts\Support\Arrayable;
use Syscodes\Components\Contracts\Support\Jsonable;
use Syscodes\Components\Contracts\Support\Renderable;

/**
 * Response represents an HTTP response.
 */
class Response extends SymfonyResponse
{
	use Concerns\ResponseContent;

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
		$this->headers = new ResponseHeaderBag($headers);
		
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
		return transform(parent::getContent(), fn ($content) => $content, '');
	}

	/**
	 * Sends the content of the message to the browser.
	 *
	 * @param  mixed  $content  The response content
	 *
	 * @return static
	 */
	#[\Override]
	public function setContent(mixed $content): static
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
		
		parent::setContent($content);

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
}