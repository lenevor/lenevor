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

namespace Syscodes\Components\View\Transpilers;

use InvalidArgumentException;
use Syscodes\Components\Support\Arr;
use Syscodes\Components\Support\Str;

/**
 * System to transpile views according to your label or your template.
 */
class PlazeTranspiler extends Transpiler implements TranspilerInterface
{
    use Concerns\TranspilesJson,
        Concerns\TranspilesEchos,
        Concerns\TranspilesLoops,
        Concerns\TranspilesStacks,
        Concerns\TranspilesRawPhp,
        Concerns\TranspilesHelpers,
        Concerns\TranspilesLayouts,
        Concerns\TranspilesComments,
        Concerns\TranspilesIncludes,
        Concerns\TranspilesComponents,
        Concerns\TranspilesConditionals,
        Concerns\TranspilesTranslations;

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
     * Gets the path that was transpiled.
     * 
     * @var string $path
     */
    protected $path;

    /**
     * Array of opening and closing tags for raw echos.
     *
     * @var array
     */
    protected $rawTags = ['{!!', '!!}'];

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
     * Transpile the view at the given path.
     * 
     * @param  string|null  $path
     * 
     * @return void
     */
    public function transpile($path = null): void
    {
        if ($path) {
            $this->setPath($path);
        }

        if ( ! is_null($this->cachePath)) {
            $contents = $this->displayString($this->files->get($this->getPath()));

            if ( ! empty($this->getPath())) {
                $contents = $this->AppendFilePath($contents);
            }

            $this->transpiledDirectoryExists(
                $transpiledPath = $this->getTranspilePath($this->getPath())
            );
            
            $this->files->put($transpiledPath, $contents);
        }        
    }

    /**
     * Transpile the given template contents.
     * 
     * @param  string  $value
     * 
     * @return string
     */
    protected function displayString($value): string
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
    protected function storeUncompiledBlocks($value): string
    {
        if (strpos($value, '<@literal') !== false) {
            $value = $this->registerLiteralBlocks($value);
        }

        if (strpos($value, '<@php') !== false) {
            $value = $this->registerPhpBlocks($value);
        }

        return $value;
    }

    /**
     * Register the literal blocks and program for expressions or
     * functions according to your need.
     * 
     * @param  string  $value
     * 
     * @return string
     */
    protected function registerLiteralBlocks($value): string
    {
        return preg_replace_callback('/(?<!<@)<@literal(.*?)<@endliteral/s', fn ($matches) => "{$matches[1]}", $value);
    }
    
    /**
     * Register the PHP blocks and program for expressions or
     * functions according to your need.
     * 
     * @param  string  $value
     * 
     * @return string
     */
    protected function registerPhpBlocks($value): string
    {
        return preg_replace_callback('/(?<!<@)<@php(.*?)<@endphp/s', fn ($matches) => "<?php{$matches[1]}?>", $value);
    }
    
    /**
     * Parse the tokens from the template.
     * 
     * @param  array  $token
     * 
     * @return string
     */
    protected function parseToken($token): string
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
     * Append the file path to the compiled string.
     * 
     * @param  string  $contents
     * 
     * @return string
     */
    protected function AppendFilePath($contents): string
    {
        $tokens = $this->getCollectionPHPTokens($contents);

        if ($tokens->isNotEmpty() && $tokens->last() !== T_CLOSE_TAG) {
            $contents .= ' ?>';
        }

        return $contents."\n\n<?php /** BEGINPATH {$this->getPath()} ENDPATH **/?>";
    }

    /**
     * Get the open and closing PHP tag tokens from the given string.
     * 
     * @param  string  $contents
     * 
     * @return \Syscodes\Components\Collections\Collection
     */
    protected function getCollectionPHPTokens($contents)
    {
        return collect(token_get_all($contents))
                    ->pluck(0)
                    ->filter(fn ($token) => in_array($token, [T_OPEN_TAG, T_OPEN_TAG_WITH_ECHO, T_CLOSE_TAG]));
    }
    
    /**
     * Transpile Plaze Statements that start with "@".
     * 
     * @param  string  $value
     * 
     * @return string
     */
    protected function transpileStatements($value): string
    {
        $pattern = '/\B<@(\w+)([ \t]*)(\( ( (?>[^()]+) | (?3) )* \))?/x';

        $callback = fn ($match) => $this->transpileStatement($match);

        return preg_replace_callback($pattern, $callback, $value);
    }

    /**
     * Transpile a single Plaze @ statement.
     * 
     * @param  string  $match
     * 
     * @return string
     */
    protected function transpileStatement($match): string
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
    protected function customDirective($name, $value = null)
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
    public function if($name, callable $callback): void
    {
        $this->conditions[$name] = $callback;
        
        $this->directive($name, function ($expression) use ($name) {
            return $expression !== ''
                ? "<?php if (\Syscodes\Components\Support\Facades\Plaze::checkpoint('{$name}', {$expression})): ?>"
                : "<?php if (\Syscodes\Components\Support\Facades\Plaze::checkpoint('{$name}')): ?>";
        });
        
        $this->directive('unless'.$name, function ($expression) use ($name) {
            return $expression !== ''
                ? "<?php if (! \Syscodes\Components\Support\Facades\Plaze::checkpoint('{$name}', {$expression})): ?>"
                : "<?php if (! \Syscodes\Components\Support\Facades\Plaze::checkpoint('{$name}')): ?>";
        });
        
        $this->directive('else'.$name, function ($expression) use ($name) {
            return $expression !== ''
                ? "<?php elseif (\Syscodes\Components\Support\Facades\Plaze::checkpoint('{$name}', {$expression})): ?>"
                : "<?php elseif (\Syscodes\Components\Support\Facades\Plaze::checkpoint('{$name}')): ?>";
        });
        
        $this->directive('end'.$name, function () {
            return '<?php endif; ?>';
        });
    }
    
    /**
     * Checkpoint the result of a condition.
     * 
     * @param  string  $name
     * @param  array  $parameters
     * 
     * @return bool
     */
    public function checkpoint($name, ...$parameters): bool
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
    public function stripParentheses($expression): string
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
    protected function transpileExtensions($value): string
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
    protected function directive($name, callable $callback): void
    {
        if (preg_match('/^\w+(?:::\w+)?$/x', $name)) {
            throw new InvalidArgumentException("The directive name [{$name}] is not valid. Directive names must only contain alphanumeric characters and underscores.");
        }

        $this->customDirectives[$name] = $callback;
    }

    /**
     * Get the path currently being transpiled.
     * 
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Set the path currently being transpiled.
     * 
     * @param  string  $path
     * 
     * @return void
     */
    public function setPath($path): void
    {
        $this->path = $path;
    }

    /**
     * Register a custom transpiler the Plaze engine.
     * 
     * @param  \Callable  $extend
     * 
     * @return void
     */
    public function extend($extend): void
    {
        $this->extensions[] = $extend;
    }

    /**
     * Get the extensions.
     * 
     * @return array
     */
    public function getExtensions(): array
    {
        return $this->extensions;
    }
}