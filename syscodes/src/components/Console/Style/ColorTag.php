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

namespace Syscodes\Console\Style;

use Syscodes\Console\Style\Color;
use Syscodes\Console\Formatter\OutputFormatter;

/**
 * Formats for color tags.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
final class ColorTag
{
    // Regex used for removing color tags.
    public const REGEX_STRIP_TAGS = '/<[\/]?[a-zA-Z0-9=;]+>/';

    // Regex to match tags.
    public const REGEX_TAG = '/<([a-zA-Z0-9=;_]+)>(.*?)<\/\\1>/s';

    /**
     * Alias of the wrap().
     *
     * @param string $text
     * @param string $tag
     *
     * @return string
     */
    public static function add(string $text, string $tag): string
    {
        return self::wrap($text, $tag);
    }

    /**
     * Wrap a color style tag.
     *
     * @param string $text
     * @param string $tag
     *
     * @return string
     */
    public static function wrap(string $text, string $tag): string
    {
        if (!$text || !$tag) {
            return $text;
        }

        return "<$tag>$text</$tag>";
    }
    
    /**
     * Parser a text on command console.
     * 
     * @param  string  $text
     * @param  bool  $recursive  Parse nested tags
     * 
     * @return string
     */
    public static function parse(string $text, bool $recursive = false): string
    {
        if ( ! $text || false === strpos($text, '</')) {
            return $text;
        }
        
        return static::pregReplaceTags($text, $recursive);
    }
    
    /**
     * Replaces tag for formatted text on command console.
     * 
     * @param  string  $text
     * @param  bool  $recursive
     * 
     * @return string
     */
    public static function pregReplaceTags(string $text, bool $recursive = false): string
    {
        return preg_replace_callback(self::REGEX_TAG, static function (array $match) use ($recursive) {
            $colorCode = '';            
            $tagName   = $match[1];
            
            if (isset(Color::STYLES[$tagName])) {
                $colorCode = Color::STYLES[$tagName];
            } elseif (strpos($tagName, '=')) {
                $colorCode = Color::stringToCode($tagName);
            }
            
            // Enhance: support parse nested tags
            $body = $match[2];
            
            if ($recursive && false !== strpos($body, '</')) {
                $body = self::pregReplaceTags($body, $recursive);dd($body);
            }
            
            // wrap body with color codes
            if ($colorCode) {
                return sprintf("\033[%sm%s\033[0m", $colorCode, $body);
            }
            
            // return raw contents.
            return $match[0];
        }, $text);
    }
}