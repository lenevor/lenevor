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

namespace Syscodes\View\Transpilers;

use Syscodes\Support\Str;
use Syscodes\Collections\Arr;

class PlazeTranspiler extends Transpiler implements TranspilerInterface
{
    use Establishes\TranspilesEchos,
        Establishes\TranspilesLoops,
        Establishes\TranspilesRawPhp,
        Establishes\TranspilesHelpers,
        Establishes\TranspilesLayouts,
        Establishes\TranspilesComments,
        Establishes\TranspilesIncludes,
        Establishes\TranspilesComponents,
        Establishes\TranspilesConditionals,
        Establishes\TranspilesTranslations;
        
    /**
     * All of the available transpiler functions.
     * 
     * @var array $transpilers
     */
    protected $transpilers = [
        'Comments',
        'Extensions',
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
     * All of the registered extensions.
     * 
     * @var array $extensions
     */
    protected $extensions = [];

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
     * Transpile the view at the given path.
     * 
     * @param  string|null  $path  (null by default)
     * 
     * @return void
     */
    public function transpile($path = null)
    {
        if ( ! is_null($this->cachePath))
        {
            $contents = $this->displayString($this->files->get($path));
            
            $this->files->put(
                $this->getTranspilePath($path), $contents
            );
        }        
    }

    /**
     * Transpile the given template contents.
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
            foreach ($this->transpilers as $type)
            {
                $content = $this->{"transpile{$type}"}($content);
            }
        }
        
        return $content;
    }
    
    /**
     * Transpile template Statements that start with "@".
     * 
     * @param  string  $value
     * 
     * @return mixed}
     */
    protected function transpileStatements($value)
    {
        $callback = function($match) 
        {
            if (method_exists($this, $method = 'transpile'.ucfirst($match[1])))
            {
                $match[0] = call_user_func([$this, $method], Arr::get($match, 3));
            }
            
            return isset($match[3]) ? $match[0] : $match[0].$match[2];
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

    /**
     * Gets the user defined extensions.
     * 
     * @param  string  $value
     * 
     * @return string
     */
    protected function transpileExtensions($value)
    {
        foreach ($this->extensions as $extension)
        {
            $value = $extension($value, $this);
        }

        return $value;
    }

    /**
     * Register a custom transpiler the Plaze engine.
     * 
     * @param  \Callable  $extend
     * 
     * @return void
     */
    public function extend($extend)
    {
        $this->extensions[] = $extend;
    }

    /**
     * Get the extensions.
     * 
     * @return array
     */
    public function getExtensions()
    {
        return $this->extensions;
    }
}