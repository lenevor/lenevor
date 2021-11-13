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

namespace Syscodes\Components\Translation;

use MessageFormatter;
use InvalidArgumentException;
use Syscodes\Components\Support\Str;
use Syscodes\Components\Collections\Arr;
use Syscodes\Components\Contracts\Translation\Loader;
use Syscodes\Components\Contracts\Translation\Translator as TranslatorContract;

/**
 * Handle system messages and localization. Locale-based, 
 * built on top of PHP internationalization.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class Translator implements TranslatorContract
{
    /**
     * The fallback locale used by the translator.
     * 
     * @var string $fallback
     */
    protected $fallback;

    /**
     * Get the language lines from files.
     * 
     * @var array $language
     */
    protected $language = [];

    /**
     * Array of loaded files.
     * 
     * @var array $loaded
     */
    protected $loaded = [];

    /**
     * The loader implementation.
     * 
     * @var \Syscodes\Components\Contracts\Translation\Loader $loader
     */
    protected $loader;

    /**
     * The default locale being used by the translator.
     * 
     * @var array $locale
     */
    protected $locale;

     /**
     * Boolean value whether the intl libraries exist on the system.
     * 
     * @var bool $intlSupport
     */
    protected $intlSupport = false;

    /**
     * Constructor language.
     * 
     * @param  string  $locale
     * @param  \Syscodes\Components\Contracts\Translation\Loader  $loader
     * 
     * @return void
     */
    public function __construct($locale, Loader $loader)
    {   
        $this->setLocale($locale);

        $this->loader = $loader;

        if (class_exists('\MessageFormatter')) {
            $this->intlSupport = true;
        }
    }

    /**
     * Get the translation for the given key.
     * 
     * @param  string  $key
     * @param  array  $replace
     * @param  string|null  $locale
     * @param  bool  $fallback
     * 
     * @return string|array
     */
    public function get($key, array $replace = [], string $locale = null, bool $fallback = true)
    {
        $locale = $locale ?: $this->locale;

        $this->load('*', $locale);
        
        $line = $this->loaded['*'][$locale][$key] ?? null;

        if ( ! isset($line)) {
            // Parse out the file name and the actual alias.
            // Will load the language file and strings.
            [$group, $item] = $this->parseLine($key);

            $locales = $fallback ? $this->localeArray($locale) : [$locale];
            
            foreach ($locales as $locale) {
                if ( ! is_null($line = $this->getLine(
                        $group, $locale, $item, $replace
                ))) {
                    return $line;
                }
            }
        }

        return $this->makeReplacements($line ?: $key, $replace);
    }

    /**
     * Parses the language string which should include the
     * filename as the first segment (separated by period).
     * 
     * @param  string  $key
     * 
     * @return array
     */
    protected function parseLine(string $key)
    {
        if (isset($this->loaded[$key])) {
            return $this->loaded[$key];
        }
        
        if (strpos($key, '::') === false) {
            $segments = explode('.', $key);
            
            $parsed = $this->parseSegments($segments);
        }

        return $this->loaded[$key] = $parsed;
    }
    
    /**
     * Parse an array of segments.
     * 
     * @param  array  $segments
     * 
     * @return array
     */
    protected function parseSegments(array $segments)
    {
        $group = $segments[0];
        
        $item = count($segments) === 1
                    ? null
                    : implode('.', array_slice($segments, 1));
                    
        return [$group, $item];
    }

    /**
     * Parses the language string for a file, loads the file, if necessary,
     * getting the line.
     * 
     * @param  string  $line
     * @param  string  $locale
     * @param  array  $replace
     * 
     * @return string|array  Returns line
     */
    protected function getLine(
        $group, 
        $locale, 
        $item, 
        array $replace = []
    ) {   
        $this->load($group, $locale);       
        
        $output = Arr::get($this->loaded[$group][$locale], $item);

        if (is_string($output)) {
            return $this->makeReplacements($output, $replace);
        } elseif (is_array($output) && count($output) > 0) {
            foreach ($output as $key => $value) {
                $output[$key] = $this->makeReplacements($value, $replace);
            }

            return $output;
        }
    }

    /**
     * Loads a language group in the current locale, otherwise will merge with the 
     * existing language lines.
     * 
     * @param  string  $group
     * @param  string  $locale  
     * 
     * @return void
     */
    protected function load($group, $locale)
    {
        if ($this->isLoaded($group, $locale)) {
            return;
        }
        
        $lang = $this->loader->load($locale, $group);

        $this->loaded[$group][$locale] = $lang;
    }
    
    /**
     * Determine if the given group has been loaded.
     * 
     * @param  string  $group
     * @param  string  $locale
     * 
     * @return bool
     */
    protected function isLoaded($group, $locale)
    {
        return isset($this->loaded[$group][$locale]);
    }

    /**
     * Make the place-holder replacements on a line.
     * 
     * @param  string  $line
     * @param  array  $replace
     * 
     * @return string
     */
    protected function makeReplacements($line, array $replace): string
    {
        $line = $this->formatMessage($line, $replace);

        if (empty($replace)) {
            return $line;
        }

        $shouldReplace = [];

        foreach ($replace as $key => $value) {
            $shouldReplace[':'.$key]               = $value;
            $shouldReplace[':'.Str::upper($key)]   = Str::upper($value); 
            $shouldReplace[':'.Str::ucfirst($key)] = Str::ucfirst($value);
        }

        return strtr($line, $shouldReplace);
    }

    /**
     * Advanced line formatting.
     * 
     * @param  string  $line
     * @param  array  $replace
     * 
     * @return string|array
     */
    protected function formatMessage($line, array $replace = [])
    {
        if ( ! $this->intlSupport || ! count($replace)) {
            return $line;
        }

        return MessageFormatter::formatMessage($this->locale, $line, $replace);
    }
    
    /**
     * Get the array of locales to be checked.
     * 
     * @param  string|null  $locale
     * 
     * @return array
     */
    protected function localeArray($locale): array
    {
        return array_filter([$locale ?: $this->locale, $this->fallback]);
    }

    /**
     * Get the default locale being used.
     * 
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * Set the default locale.
     * 
     * @param  string  $locale
     * 
     * @return void
     */
    public function setLocale($locale)
    {
        if (Str::contains($locale, ['/', '\\'])) {
            throw new InvalidArgumentException('Invalid characters present in locale.');
        }
        
        $this->locale = $locale;
    }

    /**
     * Get the fallback locale being used.
     * 
     * @return string
     */
    public function getFallback(): string
    {
        return $this->fallback;
    }

    /**
     * Set the default locale.
     * 
     * @param  string  $locale
     * 
     * @return void
     */
    public function setFallback($fallback): void
    {        
        $this->fallback = $fallback;
    }
}