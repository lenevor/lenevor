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
namespace Syscodes\Components\Mail\Encoder;

/**
 * Get the Base64 encoded for text strings.
 */
class Base64Encoder
{
    /**
     * Takes an unencoded string and produces a Base64 encoded string from it.
     * 
     * @param  string  $string
     * @param  int  $firstLineOffset
     * @param  int  $maxLineLength
     * 
     * @return string
     */
    public function encodeString(string $string, int $firstLineOffset = 0, int $maxLineLength = 0): string
    {
        if (0 >= $maxLineLength || 76 < $maxLineLength) {
            $maxLineLength = 76;
        }
        
        $encodedString = base64_encode($string);
        $firstLine     = '';
        
        if (0 !== $firstLineOffset) {
            $firstLine = substr($encodedString, 0, $maxLineLength - $firstLineOffset)."\r\n";
            $encodedString = substr($encodedString, $maxLineLength - $firstLineOffset);
        }
        
        return $firstLine.trim(chunk_split($encodedString, $maxLineLength, "\r\n"));
    }
}