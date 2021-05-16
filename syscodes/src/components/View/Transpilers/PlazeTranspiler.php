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
 * @copyright   Copyright (c) 2019 - 2021 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\View\Transpilers;

use Syscodes\Support\Str;
use Syscodes\Collections\Arr;

/**
 * System to transpile views according to your label or your template.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class PlazeTranspiler extends Transpiler implements TranspilerInterface
{
    use Concerns\TranspilesEchos,
        Concerns\TranspilesLoops,
        Concerns\TranspilesRawPhp,
        Concerns\TranspilesHelpers,
        Concerns\TranspilesLayouts,
        Concerns\TranspilesComments,
        Concerns\TranspilesIncludes,
        Concerns\TranspilesComponents,
        Concerns\TranspilesConditionals,
        Concerns\TranspilesTranslations;
        
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
     * All custom "conditions" handlers.
     * 
     * @var array $conditions
     */
    protected $conditions = [];
    
    /**
     * Array of opening and closing tags for regular echos.
     * 
     * @var array $contentTags
     */
    protected $contentTags = ['{{', '}}'];

    /**
     * All custom "directives" handlers.
     * 
     * @var array $customDirectives
     */
    protected $customDirectives = [];

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
     * @param  string|null  $path  
     * 
     * @return void
     */
    public function transpile($path = null)
    {
        if ( ! is_null($this->cachePath)) {
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

        $value = $this->transpileComments($this->storeUncompiledBlocks($value));

        foreach (token_get_all($value) as $token) {
            $result .= is_array($token) ? $this->parseToken($token) : $token;
        }
        
        if (count($this->footer) > 0) {
            $result = ltrim($result, PHP_EOL).PHP_EOL.implode(PHP_EOL, array_reverse($this->footer));
        }
        
        return $result;
    }

    /**
     * Store the blocks that do not receive compilation.
     * 
     * @param  string  $value
     * 
     * @return string
     */
    protected function storeUncompiledBlocks($value)
    {
        if (strpos($value, '<@php') !== false) {
            $value = $this->registerPhpBlocks($value);
        }

        return $value;
    }
    
    /**
     * Register the PHP blocks and program for expressions or
     * functions according to your need.
     * 
     * @param  string  $value
     * 
     * @return string
     */
    protected function registerPhpBlocks($value)
    {
        return preg_replace_callback('/(?<!<@)<@php(.*?)<@endphp/s', function ($matches) {
            return "<?php{$matches[1]}?>";
        }, $value);
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
        
        if ($id == T_INLINE_HTML) {
            foreach ($this->transpilers as $type) {
                $content = $this->{"transpile{$type}"}($content);
            }
        }
        
        return $content;
    }
    
    /**
     * Transpile Plaze Statements that start with "@".
     * 
     * @param  string  $value
     * 
     * @return string
     */
    protected function transpileStatements($value)
    {
        $pattern = '/\B<@(\w+)([ \t]*)(\( ( (?>[^()]+) | (?3) )* \))?/x';

        $callback = function ($match) {
            return $this->transpileStatement($match);
        };

        return preg_replace_callback($pattern, $callback, $value);
    }

    /**
     * Transpile a single Plaze @ statement.
     * 
     * @param  string  $match
     * 
     * @return string
     */
    protected function transpileStatement($match)
    {
        if (Str::contains($match[1], '@')) {
            $match[0] = isset($match[3]) ? $match[1].$match[3] : $match[1];
        } elseif (isset($this->customDirectives[$match[1]])) {
            $match[0] = $this->customDirective($match[1]);
        } elseif (method_exists($this, $method = 'transpile'.ucfirst($match[1]))) {
            $match[0] = call_user_func([$this, $method], Arr::get($match, 3));
        }
        
        return isset($match[3]) ? $match[0] : $match[0].$match[2];
    }
    
    /**
     * Gets the given directive with the given value.
     * 
     * @param  string  $name
     * @param  string|null  $value
     * 
     * @return string
     */
    protected function customDirective($name, $value)
    {
        $value = $value ?? '';

        if (Str::startsWith($value, '(') && Str::endsWith($value, ')')) {
            $value = Str::substr($value, 1, -1);
        }
        
        return call_user_func($this->customDirectives[$name], trim($value));
    }
    
    /**
     * Register an "if" statement directive.
     * 
     * @param  string  $name
     * @param  \callable  $callback
     * 
     * @return void
     */
    public function if($name, callable $callback)
    {
        $this->conditions[$name] = $callback;
        
        $this->directive($name, function ($expression) use ($name) {
            return $expression !== ''
                    ? "<?php if (\Syscodes\Support\Facades\Plaze::check('{$name}', {$expression})): ?>"
                    : "<?php if (\Syscodes\Support\Facades\Plaze::check('{$name}')): ?>";
        });
        
        $this->directive('unless'.$name, function ($expression) use ($name) {
            return $expression !== ''
                ? "<?php if (! \Syscodes\Support\Facades\Plaze::check('{$name}', {$expression})): ?>"
                : "<?php if (! \Syscodes\Support\Facades\Plaze::check('{$name}')): ?>";
        });
        
        $this->directive('else'.$name, function ($expression) use ($name) {
            return $expression !== ''
                ? "<?php elseif (\Syscodes\Support\Facades\Plaze::check('{$name}', {$expression})): ?>"
                : "<?php elseif (\Syscodes\Support\Facades\Plaze::check('{$name}')): ?>";
        });
        
        $this->directive('end'.$name, function () {
            return '<?php endif; ?>';
        });
    }
    
    /**
     * Check the result of a condition.
     * 
     * @param  string  $name
     * @param  array  $parameters
     * 
     * @return bool
     */
    public function check($name, ...$parameters)
    {
        return call_user_func($this->conditions[$name], ...$parameters);
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
        if (Str::startsWith($expression, '(') && Str::endsWith($expression, ')')) {
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
        foreach ($this->extensions as $extension) {
            $value = $extension($value, $this);
        }

        return $value;
    }

    /**
     * Register a callback for custom directives.
     * 
     * @param  string  $name
     * @param  \callable  $callback
     * 
     * @param  void
     * 
     * @throws \InvalidArgumentException
     */
    protected function directive($name, callable $callback)
    {
        if (preg_match('/^\w+(?:::\w+)?$/x', $name)) {
            throw new InvalidArgumentException("The directive name [{$name}] is not valid. Directive names must only contain alphanumeric characters and underscores.");
        }

        $this->customDirectives[$name] = $callback;
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