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
 * @author      Javier Alexander Campo M. <jalexcam@gmail.com>
 * @link        https://lenevor.com 
 * @copyright   Copyright (c) 2019-2021 Lenevor Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.1.0 
 */

namespace Syscodes\Config;

use InvalidArgumentException;

/**
 * Manages .env files.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
class ParserEnv
{
    const ALFANUMERIC_REGEX = '/\${([a-zA-Z0-9_]+)}/';

    /**
     * The directory where the .env file is located.
     * 
     * @var string $path  
     */
    protected $path;

    /**
     * Activate use of putenv, by default is true.
     * 
     * @var bool $usePutenv 
     */
    protected $usePutenv = true;

    /**
     * Constructor. Builds the path to our file.
     * 
     * @param  string  $path
     * @param  string|null  $file
     * 
     * @return void
     */
    public function __construct(string $path, bool $usePutenv = true, string $file = null)
    {
        $this->usePutenv = $usePutenv;
        $this->path      = $this->getFilePath($path, $file ?: '.env');
    }

    /**
     * Returns the full path to the file.
     * 
     * @param  string  $path
     * @param  string  $file
     * 
     * @return string
     */
    protected function getFilePath(string $path, string $file)
    {
       return rtrim($path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$file;
    }

    /**
     * Determine if the line in the file is a comment.
     * 
     * @param  string  $line
     * 
     * @return bool
     */
    protected function isComment(string $line)
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
    protected function checkedLikeSetter(string $line)
    {
        return strpos($line, '=') !== false;
    }

    /**
     * Will load the .env file and process it. So that we end all settings in the PHP 
     * environment vars: getenv(), $_ENV, and $_SERVER.
     * 
     * @return bool
     */
    public function load()
    {
        // Ensure file is readable
        if ( ! is_readable($this->path) && ! is_file($this->path))
        {
            throw new InvalidArgumentException(sprintf("The .env file is not readable: %s", $this->path));
        }
        
        $lines = file($this->path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line)
        {
            // Is it a comment?
            if ($this->isComment($line))
            {
                continue;
            }

            // If there is an equal sign, then we know we
			// are assigning a variable.
            if ($this->checkedLikeSetter($line))
            {
                $this->setVariable($line);
            }
        }

        return true;
    }

    /**
     * Sets the variable into the environment. 
     * Will parse the string to look for {name}={value} pattern.
     * 
     * @param  string  $name
     * @param  string|null  $value
     * 
     * @return void
     */
    protected function setVariable(string $name, $value = null)
    {
        list($name, $value) = $this->normaliseEnvironmentVariable($name, $value);
        $notHttpName = 0 !== strpos($name, 'HTTP_');

        if ($this->usePutenv)
        {
            putenv("$name=$value");
        }   
        
        if (empty($_ENV[$name]))
        {
            $_ENV[$name] = $value;
        }

        if ($notHttpName)
        {
            $_SERVER[$name] = $value;
        }
    }

    /**
     * Normalise the given environment variable.
     * 
     * @param  string  $name
     * @param  string  $value
     * 
     * @return array
     */
    public function normaliseEnvironmentVariable(string $name, $value)
    {
        // Split our compound string into it's parts.
        if (strpos($name, '=') !== false)
        {
            list($name, $value) = explode('=', $name, 2);
        }
        
        $name  = trim($name);
        $value = trim($value);
        
        // Sanitize the name
        list($name, $value) = $this->sanitizeName($name, $value);
        
        // Sanitize the value
        list($name, $value) = $this->sanitizeValue($name, $value);
        
        $value = $this->resolveNestedVariables($value);
        
        return [$name, $value];
    }

    /**
     * Strips quotes and the optional leading "export " from the environment variable name.
     * 
     * @param  string  $name
     * @param  string  $value
     * 
     * @return array
     */
    protected function sanitizeName(string $name, $value)
    {
        $name = str_replace(array('export ', '\'', '"'), '', $name);

        return [$name, $value];
    }

    /**
     * Strips quotes from the environment variable value.
     * 
     * This was borrowed from the excellent phpdotenv with very few changes.
     * https://github.com/vlucas/phpdotenv
     * 
     * @param  string  $name
     * @param  string  $value
     * 
     * @return array
     * 
     * @throws \InvalidArgumentException
     */
    protected function sanitizeValue(string $name, $value)
    {
        if ( ! $value)
        {
            return [$name, $value];
        }
        
        // Does it begin with a quote?
        if (strpbrk($value[0], '"\'') !== false)
        {
            // value starts with a quote
            $quote        = $value[0];

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
            
            $value        = preg_replace($regexPattern, '$1', $value);
            $value        = str_replace("\\$quote", $quote, $value);
            $value        = str_replace('\\\\', '\\', $value);
        }
        else
        {
            $parts = explode(' #', $value, 2);
            $value = trim($parts[0]);
            // Unquoted values cannot contain whitespace
            if (preg_match('/\s+/', $value) > 0)
            {
                throw new InvalidArgumentException('.env values containing spaces must be surrounded by quotes.');
            }
        }
        
        return [$name, $value];
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
    protected function resolveNestedVariables(string $value)
    {
        if (strpos($value, '$') !== false)
        {
            $loader = $this;
            $value = preg_replace_callback(self::ALFANUMERIC_REGEX, function ($matchedPatterns) use ($loader) {
                
                $nestedVariable = $loader->getVariable($matchedPatterns[1]);

                if (is_null($nestedVariable))
                {
                    return $matchedPatterns[0];
                }
                
                return $nestedVariable;
                
            }, $value);
        }
        
        return $value;
    }
    
    /**
     * Search the different places for environment variables and return first value found.
     * This was borrowed from the excellent phpdotenv with very few changes.
     * https://github.com/vlucas/phpdoten
     * 
     * @param  string  $name
     * 
     * @return string|null
     */
    protected function getVariable(string $name)
    {
        switch (true)
        {
            case array_key_exists($name, $_ENV):
                return $_ENV[$name];
                break;
            case array_key_exists($name, $_SERVER):
                return $_SERVER[$name];
                break;
            default:
                $value = getenv($name);
                // switch getenv default to null
                return $value === false ? null : $value;
        }
    }
}