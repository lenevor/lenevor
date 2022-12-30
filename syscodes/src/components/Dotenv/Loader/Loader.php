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

namespace Syscodes\Components\Dotenv\Loader;

use InvalidArgumentException;

/**
 * Allows will load the .env file and process it.
 */
final class Loader
{
    /**
     * The Repository creator instance.
     * 
     * @var \Syscodes\Dotenv\Repository\RepositoryCreator $repository
     */
    protected $repository;

    /**
     * Constructor. Create a new Loader instance.
     * 
     * @param  \Syscodes\Dotenv\Repository\RepositoryCreator  $repository
     * 
     * @return void 
     */
    public function __construct($repository)
    {
        $this->repository = $repository;
    }

    /**
     * Will load the .env file and process it. So that we end all settings in the PHP 
     * environment vars: getenv(), $_ENV, and $_SERVER.
     * 
     * @param  array  $entries
     * 
     * @return bool
     */
    public function load(array $entries): bool
    {
        foreach ($entries as $line) {
            // Is it a comment?
            if ($this->isComment($line)) {
                continue;
            }

            // If there is an equal sign, then we know we
            // are assigning a variable.
            if ($this->checkedLikeSetter($line)) {
                list($name, $value) = $this->normaliseEnvironmentVariable($line);
                $this->setVariable($name, $value);
            }
        }

        return true;
    }

    /**
     * Determine if the line in the file is a comment.
     * 
     * @param  string  $line
     * 
     * @return bool
     */
    protected function isComment(string $line): bool
    {
        return strpos(ltrim($line), '#') === 0;
    }

    /**
     * Determine if the given line looks like it's setting a variable.
     * 
     * @param  string  $line
     * 
     * @return bool
     */
    protected function checkedLikeSetter(string $line): bool
    {
        return strpos($line, '=') !== false;
    }

    /**
     * Normalise the given environment variable.
     * 
     * @param  string  $name
     * @param  string  $value
     * 
     * @return array
     */
    public function normaliseEnvironmentVariable(string $name, string $value = ''): array
    {
        // Split our compound string into it's parts.
        if (strpos($name, '=') !== false) {
            list($name, $value) = explode('=', $name, 2);
        }
        
        $name  = trim($name);
        $value = trim($value);
        
        // Sanitize the name
        $name = $this->sanitizeName($name);
        
        // Sanitize the value
        $value = $this->sanitizeValue($value);
        
        // Get environment variables
        $value = $this->getResolverVariables($value);
        
        return [$name, $value];
    }

    /**
     * Strips quotes and the optional leading "export" from the environment variable name.
     * 
     * @param  string  $name
     * 
     * @return string
     */
    protected function sanitizeName(string $name): string
    {
        return str_replace(array('export ', '\'', '"'), '', $name);
    }

    /**
     * Sets the variable into the environment. 
     * Will parse the string to look for {name}={value} pattern.
     * 
     * @param  string  $name
     * @param  string|null  $value  (null by default)
     * 
     * @return void
     */
    protected function setVariable(string $name, $value = null)
    {        
        return $this->repository->set($name, $value);
    }

    /**
     * Strips quotes from the environment variable value.
     * 
     * This was borrowed from the excellent phpdotenv with very few changes.
     * https://github.com/vlucas/phpdotenv
     * 
     * @param  string  $value
     * 
     * @return string
     * 
     * @throws \InvalidArgumentException
     */
    protected function sanitizeValue($value)
    {
        if ( ! $value) {
            return $value;
        }
        
        // Does it begin with a quote?
        if (strpbrk($value[0], '"\'') !== false) {
            // value starts with a quote
            $quote = $value[0];

            $regexPattern = sprintf(
					'/^
					%1$s          # match a quote at the start of the value
					(             # capturing sub-pattern used
								  (?:          # we do not need to capture this
								   [^%1$s\\\\] # any character other than a quote or backslash
								   |\\\\\\\\   # or two backslashes together
								   |\\\\%1$s   # or an escaped quote e.g \"
								  )*           # as many characters that match the previous rules
					)             # end of the capturing sub-pattern
					%1$s          # and the closing quote
					.*$           # and discard any string after the closing quote
					/mx', $quote
            );
            
            $value = preg_replace($regexPattern, '$1', $value);
            $value = str_replace("\\$quote", $quote, $value);
            $value = str_replace('\\\\', '\\', $value);
        } else {
            $parts = explode(' #', $value, 2);
            $value = trim($parts[0]);
            // Unquoted values cannot contain whitespace
            if (preg_match('/\s+/', $value) > 0) {
                throw new InvalidArgumentException('.env values containing spaces must be surrounded by quotes');
            }
        }
        
        return $value;
    }
    
    /**
     * Resolve the nested variables.
     * 
     * Look for ${varname} patterns in the variable value and replace with an existing
     * environment variable.
     * 
     * This was borrowed from the excellent phpdotenv with very few changes.
     * https://github.com/vlucas/phpdotenv
     * 
     * @param  string  $value
     * 
     * @return string
     */
    protected function getResolverVariables(string $value): string
    {
        if (strpos($value, '$') !== false) {
            $repository = $this->repository;

            $value = preg_replace_callback('~\${([a-zA-Z0-9_]+)}~', function ($pattern) use ($repository) {
                $nestedVariable = $repository->get($pattern[1]);

                if (is_null($nestedVariable)) {
                    return $pattern[0];
                }
                
                return $nestedVariable;
                
            }, $value);
        }
        
        return $value;
    }
}