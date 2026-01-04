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

namespace Syscodes\Components\Routing;

use Syscodes\Components\Support\Str;

/**
 * Gets the route uri for parse.
 */
class RouteUri
{
    /**
     * The fields that should be used when resolving bindings.
     * 
     * @var array
     */
    public $bindingFields = [];
    
    /**
     * The route uri.
     * 
     * @var string $uri
     */
    public $uri;

    /**
     * Constructor. Create a new route URI class instance.
     * 
     * @param  string  $uri
     * @param  array  $bindingFields
     * 
     * @return void
     */
    public function __construct(string $uri, array $bindingFields = [])
    {
        $this->uri = $uri;
        $this->bindingFields = $bindingFields;
    }

    /**
	 * Parse the given URI.
	 * 
	 * @param  string  $uri
	 * 
	 * @return static
	 */
	public static function parse(string $uri): static
	{
		preg_match_all('/\{([\w\:]+?)\??\}/', $uri, $matches);
        
        $bindingFields = [];
		
		foreach ($matches[0] as $match) {
			if ( ! Str::contains($match, ':')) {
				continue;
			} 

			$segments = explode(':', trim($match, '{}?'));
            
            $bindingFields[$segments[0]] = $segments[1];
			
			$uri = Str::contains($match, '?')
                ? str_replace($match, '{'.$segments[0].'?}', $uri)
                : str_replace($match, '{'.$segments[0].'}', $uri);
		}

		return new static($uri, $bindingFields);
	}
}