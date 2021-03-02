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

namespace Syscodes\Translation;

use MessageFormatter;
use Syscodes\Support\Finder;

/**
 * Handle system messages and localization. Locale-based, 
 * built on top of PHP internationalization.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class Translator
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
     * 
     * @return void
     */
    public function __construct($locale)
    {   
        $this->locale = $locale;

        if (class_exists('\MessageFormatter')) {
            $this->intlSupport = true;
        }
    }

    /**
     * Advanced message formatting.
     * 
     * @param  string  $message
     * @param  array  $args
     * 
     * @return string|array  Returns formatted message
     */
    protected function formatMessage($message, array $args = [])
    {
        if ( ! $this->intlSupport || ! count($args)) {
            return $message;
        }

        if (is_array($message)) {
            foreach ($message as $index => $value) {
                $message[$index] = $this->formatMessage($value, $args);
            }

            return $message;
        }

        return MessageFormatter::formatMessage($this->locale, $message, $args);
    }

    /**
     * Parses the language string for a file, loads the file, if necessary,
     * getting the line.
     * 
     * @param  string  $line
     * @param  array  $args
     * 
     * @return string|array  Returns line
     */
    public function getLine($line, array $args = [])
    {
        // Parse out the file name and the actual alias.
        // Will load the language file and strings.
        list($file, $parseLine) = $this->parseLine($line);
        
        $output = $this->language[$this->locale][$file][$parseLine] ?? $line;

        if ( ! empty($args)) {
            $output = $this->formatMessage($output, $args);
        }
        
        return $output;
    }
    
    /**
     * Parses the language string which should include the
     * filename as the first segment (separated by period).
     * 
     * @param  string  $line
     * 
     * @return array
     */
    protected function parseLine($line)
    {
        // If there's no possibility of a filename being in the string
        // simply return the string, and they can parse the replacement
        // without it being in a file.
        if (strpos($line, '.') === false) {
            return [
                null,
                $line
            ];
        }
        
        $file = substr($line, 0, strpos($line, '.'));
        $line = substr($line, strlen($file) + 1);

        if ( ! array_key_exists($line, $this->language)) {
            $this->load($file, $this->locale);
        }
        
        return [
            $file,
            $this->language[$this->locale][$line] ?? $line
        ];
    }

    /**
     * Loads a language file in the current locale. If $return is true
     * will return the file's contents, otherwise will merge with the 
     * existing language lines.
     * 
     * @param  string  $file
     * @param  string  $locale
     * @param  bool  $return  
     * 
     * @return array|null
     */
    protected function load($file, $locale, $return = false)
    {
        if ( ! array_key_exists($locale, $this->loaded)) {
            $this->loaded[$locale] = [];
        }
        
        if (in_array($file, $this->loaded)) {
            return [];
        }

        if ( ! array_key_exists($locale, $this->language)) {
            $this->language[$locale] = [];
        }

        if ( ! array_key_exists($file, $this->language[$locale])) {
            $this->language[$locale][$file] = [];
        }

        $path = $locale.DIRECTORY_SEPARATOR.$file;

        $lang = $this->requireFile($path);

        if ($return) {
            return $lang;
        }

        $this->loaded[$locale][] = $file;

        $this->language[$this->locale][$file] = $lang;
    }

    /**
     * A simple method for includin files.
     * 
     * @param  string  $path
     * 
     * @return array
     */
    protected function requireFile($path)
    {
        $files = (array) Finder::search($path, 'lang');

        foreach ($files as $file) {
            if ( ! is_file($file)) {
                continue;
            }
            
            return require $file;
        }

        return [];
    }
}