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
 
namespace Syscodes\Components\Http\Resources;

use BadMethodCallException;
use InvalidArgumentException;

/**
 * This trait is responsible for loading the different HTTP status codes existing.
 */
trait HttpStatusCode
{
	/**
	 * The HTTP status code.
	 *
	 * @var int $statusCode
	 */
	protected $statusCode = 200;

	/**
	 * An array of status codes and messages.
	 *
	 * @var array $statusCodeTexts
	 */
	public static $statusCodeTexts = [
		// 1xx: Informational
		100 => 'Continue',
		101 => 'Switching Protocols',
		102 => 'Processing',

		// 2xx: Success
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
		207 => 'Multi-Status',
		208 => 'Already Reported',
		226 => 'IM Used',

		// 3xx: Redirection
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found',
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		307 => 'Temporary Redirect',
		308 => 'Permanent Redirect',

		// 4xx: Client error
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Timeout',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Long',
		415 => 'Unsupported Media Type',
		416 => 'Requested Range Not Satisfiable',
		417 => 'Expectation Failed',
		418 => 'I\'m a Teapot',
		// 419 (Authentication Timeout) is a non-standard status code with unknown origin
		421 => 'Misdirected Request',
		422 => 'Unprocessable Entity',
		423 => 'Locked',
		424 => 'Failed Dependency',
		426 => 'Upgrade Required',
		428 => 'Precondition Required',
		429 => 'Too Many Requests',
		431 => 'Request Header Fields Too Large',
		451 => 'Unavailable For Legal Reasons',

		// 5xx: Server error
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Timeout',
		505 => 'HTTP Version Not Supported',
		506 => 'Variant Also Negotiates',
		507 => 'Insufficient Storage',
		508 => 'Loop Detected',
		509 => 'Bandwidth Limit Exceeded',
		510 => 'Not Extended',
		511 => 'Network Authentication Required'
	];

	/**
	 * Gets string of status code.
	 * 
	 * @var string $statusText
	 */
	protected $statusText;

	/**
	 * Gets the response status code.
	 *
	 * The status code is a 3-digit code to specify server response results to the browser.
	 *
	 * @return int
	 *
	 * @throws \BadMethodCallException
	 */
	public function getStatusCode(): int
	{
		if (empty($this->statusCode)) {
			throw new BadMethodCallException('HTTP Response is missing a status code');
		}

		return $this->statusCode;
	}

	/**
	* Sets the response status code.
	*
	* @param  int  $code  The status code
	* @param  string|null  $text  The status text
	*
	* @return static
	*
	* @throws \InvalidArgumentException
	*/
	public function setStatusCode(int $code, $text = null): static
	{
		$this->statusCode = $code; 

		// Valid range?
		if ($this->isInvalid()) {
			throw new InvalidArgumentException(__('response.statusCodeNotValid', ['code' => $code]));			
		}

		// Check if you have an accepted status code if not shows to a message of unknown status
		if (null === $text) {
			$this->statusText = self::$statusCodeTexts[$code] ?? __('response.UnknownStatus');

			return $this;
		}

		if (false === $text) {
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
	 * @return bool
	 */
	public function isInvalid(): bool
	{
		return $this->statusCode < 100 || $this->statusCode >= 600;
	}

	/**
	 * Is response informative?
	 * 
	 * @final
	 * 
	 * @return bool
	 */
	public function isInformational(): bool
	{
		return $this->statusCode >= 100 && $this->statusCode < 200;
	}
	
	/**
	 * Is the response a redirect?
	 * 
	 * @final
	 * 
	 * @return void
	 */
	public function isRedirection(): bool
	{
		return $this->statusCode >= 300 && $this->statusCode < 400;
	}
	
	/**
	 * Is the response empty?
	 * 
	 * @final
	 * 
	 * @return bool
	 */
	public function isEmpty(): bool
	{
		return in_array($this->statusCode, [204, 304]);
	}
	
	/**
	 * Is the response a redirect of some form?
	 * 
	 * @return bool
	 */
	public function isRedirect(): bool
	{
		return in_array($this->statusCode, [301, 302, 303, 307, 308]);
	}
}