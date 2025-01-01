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
 * @copyright   Copyright (c) 2019 - 2025 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Console\Util;

use Syscodes\Components\Support\Arr;
use Syscodes\Components\Support\Str;

/**
 * This class allows to format data useful for console.
 */
final class FormatUtil
{
    /**
     * Slice array.
     * 
     * @param  array  $data
     * @param  array  $options
     * 
     * @return string
     */
    public static function spliceKeyValue(array $data, array $options = []): string
    {
        $text = '';

        $options = array_merge([
            'leftChar'    => '',   // e.g '  ', ' * '
            'sepChar'     => ' ',  // e.g ' | ' OUT: key | value
            'keyStyle'    => '',   // e.g 'info','comment'
            'valStyle'    => '',   // e.g 'info','comment'
            'keyMinWidth' => 8,
            'keyMaxWidth' => 0,
            'keyPadPos'   => 'right',
            'ucFirst'     => true,  // upper first char for value
        ], $options);

        $keyStyle  = trim($options['keyStyle']);
        $keyPadPos = (string) $options['keyPadPos'];
        
        if ($options['keyMaxWidth'] < 1) {
            $options['keyMaxWidth'] = Arr::getMaxWidth($data);
        }
        
        // compare
        if ((int) $options['keyMinWidth'] > $options['keyMaxWidth']) {
            $options['keyMaxWidth'] = $options['keyMinWidth'];
        }

        foreach ($data as $key => $value) {
            $hasKey = ! is_int($key);
            $text  .= $options['leftChar'];

            if ($hasKey) {
                $key   = Str::padBoth((string) $key, $options['keyMaxWidth'], $keyPadPos);
                $text .= static::wrap($key, $keyStyle).$options['sepChar'];
            }

            // if value is array, translate array to string
            if (is_array($value)) {
                $temp = '[';

                foreach ($value as $k => $val) {
                    if (is_bool($val)) {
                        $val = $val ? '(True)' : '(False)';
                    } else {
                        $val = is_scalar($val) ? (string) $val : $val;
                    }

                    $temp .= ( ! is_numeric($k) ? "$k: " : '') . "$val, ";
                }

                $value = rtrim($temp, ' ,') . ']';
            } elseif (is_bool($value)) {
                $value = $value ? '(True)' : '(False)';
            } else {
                $value = (string) $value;
            }

            $value  = $hasKey && $options['ucFirst'] ? ucfirst($value) : $value;
            $text  .= static::wrap($value, $options['valStyle'])."\n";
        }

        return $text;
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
        if ( ! $text || ! $tag) {
            return $text;
        }
        
        return "<$tag>$text</$tag>";
    }
}