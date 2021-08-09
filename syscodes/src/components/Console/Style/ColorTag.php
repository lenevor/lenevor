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
use Syscodes\Console\Formatter\OutputFormatterStyle;

/**
 * Formats for color tags.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
final class ColorTag
{
    // Regex to match tags.
    public const REGEX_TAG = '/<([a-zA-Z0-9=;_]+)>(.*?)<\/\\1>/s';

    /**
     * Alias of the wrap()
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
     * Wrap a color style tag
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
     * Checks pattern for color tags.
     * 
     * @param string $text
     *
     * @return array
     */
    public static function matchAll(string $text): array
    {
        if ( ! preg_match_all(self::REGEX_TAG, $text, $matches)) {
            return [];
        }

        return $matches;
    }

    /**
     * Parser match color tags.
     * 
     * @param string $text
     *
     * @return string
     */
    public static function parse(string $text): string
    {
        if (!$text || false === strpos($text, '</')) {
            return $text;
        }

        // match color tags
        if ( ! $matches = self::matchAll($text)) {
            return $text;
        }

        foreach ((array)$matches[0] as $i => $m) {
            $key = $matches[1][$i];

            if (isset(Color::STYLES[$key])) {
                $text = self::replaceColor($text, $key, $matches[2][$i], Color::STYLES[$key]);
            } elseif (strpos($text, '=')) {
                $text = self::replaceColor($text, $key, $matches[2][$i], Color::fromString($key));
            }
        }

        return $text;
    }

    /**
     * Replace color tags in a string.
     *
     * @param string $text
     * @param string $tag       The matched tag.
     * @param string $match     The matched text
     * @param string $colorCode The color style to apply.
     *
     * @return  string
     */
    public static function replaceColor(string $text, string $tag, string $match, string $colorCode): string
    {
        $replace = sprintf("\033[%sm%s\033[0m", $colorCode, $match);

        return str_replace("<$tag>$match</$tag>", $replace, $text);
    }
}