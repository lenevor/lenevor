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

namespace Syscodes\Components\Routing\Generator;

use LogicException;
use DomainException;
use Syscodes\Components\Support\Arr;
use Syscodes\Components\Support\Str;
use Syscodes\Components\Routing\Route;

/**
 * Allows compile the route patterns.
 */
class RouteCompiler
{
    /**
     * This string defines which separators will be in optional placeholders 
     * for matching and generating URLs.
     */
    public const SEPARATOR = '/,;.:-_~+*=@';

    /**
     * The maximum supported length.
     */
    public const VARIABLE_MAXMUM_LENGTH = 32;
    
    /**
     * Compile the inner Route pattern.
     * 
     * @param  \Syscodes\Components\Routing\Route  $route
     * 
     * @return string
     * 
     * @throws \LogicException|\DomainException
     */
    public static function compile(Route $route): string
    {
        $uri       = $route->getRoute();
        $patterns  = $route->getPatterns();
        $optionals = 0;
        $variables = [];
        echo static::compilePattern($route, $uri, true);
        $pattern = preg_replace_callback('~/\{(.*?)(\?)?\}~', function ($matches) use ($uri, $patterns, &$optionals, &$variables) {
            list(, $name, $optional) = array_pad($matches, 3, false);
            
            if (in_array($name, $variables)) {
                throw new LogicException("Route pattern [{$uri}] cannot reference variable name [{$name}] more than once");
            } elseif (strlen($name) > 32) {
                throw new DomainException("Variable name [{$name}] cannot be longer than 32 characters in route pattern [{$uri}]");
            } elseif (preg_match('/^\d/', $name) === 1) {
                throw new DomainException("Variable name [{$name}] cannot start with a digit in route pattern [{$uri}]");
            }
            
            $variables[] = $name;

            $pattern = Arr::get($patterns, $name, '[^/]+');
            
            if ($optional) {
                $optionals++;
                
                return sprintf('(?:/(?P<%s>%s)', $name, $pattern);
            } elseif ($optionals > 0) {
                throw new LogicException("Route pattern [{$pattern}] cannot reference standard variable [{$name}] after optionals");
            }
            
            return sprintf('/(?P<%s>%s)', $name, $pattern);
        }, $uri);
       
        return sprintf('~^%s%s~sDu', $pattern, str_repeat(')?', $optionals));
    }

    /**
     * The compile pattern for iterate over variables in the routes.
     * 
     * @param  \Syscodes\Components\Routing\Route  $route
     * @param  string  $pattern
     * @param  bool  $isHost
     * 
     * @return array
     */
    private static function compilePattern(Route $route, string $pattern, bool $ishost)
    {
        $tokens           = [];
        $variables        = [];
        $pos              = 0;
        $dafaultSeparator = $ishost ? '.' : '/';
        $useUtf8          = preg_match('//u', $pattern);

        if ($useUtf8 && preg_match('~[\x80-\xFF]~', $pattern)) {
            throw new LogicException(
                sprintf('Cannot use UTF-8 route patterns without setting the "utf8" option for route "%s".', $route->getRoute())
            );
        }

        if ( ! $useUtf8) {
            throw new LogicException(
                sprintf('Cannot mix UTF-8 requirements with non-UTF-8 pattern "%s"', $pattern)
            );
        }

        preg_match_all('~\{(!)?([\w\x80-\xFF]+)\}~', $pattern, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);

        foreach ($matches as $match) {
            $main = $match[1][1] >= 0;
            $varName = $match[2][0];
            $precedingText = substr($pattern, $pos, $match[0][1] - $pos);
            $pos = $match[0][1] + strlen($match[0][0]);

            if ( ! strlen($precedingText)) {
                $precedingChar = '';
            } elseif ($useUtf8) {
                preg_match('/.$/u', $precedingText, $precedingChar);

                $preceddingChar = $precedingChar[0];
            } else {
                $preceddingChar = substr($precedingText, -1);
            }

            $separator = '' != $preceddingChar && Str::contains(static::SEPARATOR, $preceddingChar);

            if (preg_match('~^\d~', $varName) === 1) {
                throw new DomainException(
                    sprintf('Variable name "%s" cannot start with a digit in route pattern "%s"', $varName, $pattern)
                );
            }

            if (in_array($varName, $variables)) {
                throw new LogicException(
                    sprintf('Route pattern "%s" cannot reference variable name "%s" more than once', $pattern, $varName)
                );
            }
            
            if (strlen($varName) > self::VARIABLE_MAXMUM_LENGTH) {
                throw new DomainException(
                    sprintf('Variable name "%s" cannot be longer than %d characters in route pattern "%s"', $varName, self::VARIABLE_MAXMUM_LENGTH, $pattern)
                );
            }
            
            if ($separator && $precedingText !== $precedingChar) {
                $tokens[] = ['text', substr($precedingText, 0, -strlen($precedingChar))];
            } elseif ( ! $separator && '' !== $precedingText) {
                $tokens[] = ['text', $precedingText];
            }
            
            $regex = $route->setPattern($varName);         
        }
    }
}