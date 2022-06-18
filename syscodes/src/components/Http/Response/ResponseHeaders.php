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
 * @copyright   Copyright (c) 2019 - 2022 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Http\Response;

use Syscodes\Components\Http\Contributors\Headers;

/**
 * ResponseHeaders is a container for Response HTTP headers.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class ResponseHeaders extends Headers
{
	const COOKIE_FLAT = 'flat';
	const COOKIE_ARRAY = 'array';
	
	/**
	 * The list of cookies.
	 * 
	 * @var array $cookie
	 */
	protected $cookie = [];

    /**
     * The header names.
	 * 
	 * @var array $headerNames 
     */
	protected $headerNames = [];

	/**
	 * Constructor. Create a new ResponseHeaders class instance.
	 * 
	 * @param  array  $headers
	 * 
	 * @return void 
	 */
	public function __construct(array $headers = [])
	{
		parent::__construct($headers);
		
		if ( ! isset($this->headers['cache-control'])) {
			$this->set('Cache-Control', '');
		}
	}

    /**
	 * Returns the headers, with original capitalizations.
	 * 
	 * @return array An array of headers
	 */
	public function allPreserveCase(): array
	{
		$headers = [];
		
		foreach ($this->all() as $name => $value) {
			$headers[$this->headerNames[$name] ?? $name] = $value;
		}
		
		return $headers;
	}

	/**
	 * Initialize the date.
	 * 
	 * @return void
	 */
	private function initDate(): void
	{
		//
	}
}
