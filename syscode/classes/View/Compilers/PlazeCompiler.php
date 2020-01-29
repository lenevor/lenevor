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
 * @copyright   Copyright (c) 2019-2020 Lenevor Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.6.0
 */

namespace Syscode\View\Compilers;

use Syscode\Support\Arr;
use Syscode\Support\Str;

class PlazeCompiler extends Compiler implements CompilerInterface
{
    use Establishes\CompilesEchos,
        Establishes\CompilesLayouts,
        Establishes\CompilesComments,
        Establishes\CompilesIncludes;
    /**
     * All of the available compiler functions.
     * 
     * @var array $compilers
     */
    protected $compilers = [
        'Comments',
        'Statements',
        'Echos',
    ];
    
    /**
     * Array of opening and closing tags for regular echos.
     * 
     * @var array $contentTags
     */
    protected $contentTags = ['{{', '}}'];

    /**
     * Array of opening and closing tags for escaped echos.
     *
     * @var array
     */
    protected $escapedTags = ['{{{', '}}}'];

    /**
     * The "regular" / legacy echo string format.
     *
     * @var string
     */
    protected $echoFormat = 'e(%s)';

    /**
     * Array of footer lines to be added to template.
     * 
     * @var array $footer
     */
    protected $footer = [];

    /**
     * Array of opening and closing tags for raw echos.
     *
     * @var array
     */
    protected $rawTags = ['{!!', '!!}'];

    /**
     * Compile the view at the given path.
     * 
     * @param  string|null  $path
     * 
     * @return void
     */
    public function compile($path = null)
    {
        if ( ! is_null($this->cachePath))
        {
            $contents =  $this->displayString($this->files->get($path));
            
            $this->files->put($this->getCompilePath($path), $contents);
        }        
    }

    /**
     * Compile the given Template template contents.
     * 
     * @param  string  $value
     * 
     * @return string
     */
    protected function displayString($value)
    {
        [$this->footer, $result] = [[], ''];

        foreach (token_get_all($value) as $token)
        {
            $result .= is_array($token) ? $this->parseToken($token) : $token;
        }

        if (count($this->footer) > 0)
        {
            $result = ltrim($result, PHP_EOL).PHP_EOL.implode(PHP_EOL, array_reverse($this->footer));
        }

        return $result;
    }
    
    /**
     * Parse the tokens from the template.
     * 
     * @param  array  $token
     * 
     * @return string
     */
    protected function parseToken($token)
    {
        list($id, $content) = $token;
        
        if ($id == T_INLINE_HTML)
        {
            foreach ($this->compilers as $type)
            {
                $content = $this->{"compile{$type}"}($content);
            }
        }
        
        return $content;
    }
    
    /**
     * Compile Template Statements that start with "@".
     * 
     * @param  string  $value
     * 
     * @return mixed}
     */
    protected function compileStatements($value)
    {
        $callback = function($match) 
        {
            if (method_exists($this, $method = 'compile'.ucfirst($match[1])))
            {
                $match[0] = call_user_func([$this, $method], Arr::get($match, 3));
            }
            
            return isset($match[3]) ? $match[0] : $match[0] .$match[2];
        };
        
        return preg_replace_callback('/\B<@(\w+)([ \t]*)(\( ( (?>[^()]+) | (?3) )* \))?/x', $callback, $value);
    }
    
    /**
     * Strip the parentheses from the given expression.
     * 
     * @param  string  $expression
     * 
     * @return string
     */
    public function stripParentheses($expression)
    {
        if (Str::startsWith($expression, '(') && Str::endsWith($expression, ')'))
        {
            $expression = substr($expression, 1, -1);
        }
        
        return $expression;
    }
}