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

namespace Syscodes\Components\Mail\Encoder;

use TypeError;

/**
 * Get the content Qp encoded for text strings.
 */
final class QpContentEncoder
{
    /**
     * Gets the encoded byte stream.
     * 
     * @param  mixed  $stream
     * @param  int  $maxLineLength
     * 
     * @return \iterable 
     */
    public function encodeByteStream($stream, int $maxLineLength = 0): iterable
    {
        if ( ! \is_resource($stream)) {
            throw new TypeError(sprintf('Method "%s" takes a stream as a first argument', __METHOD__));
        }
        
        yield $this->encodeString(stream_get_contents($stream), 'utf-8', 0, $maxLineLength);
    }
    
    /**
     * Get the encoded name.
     * 
     * @return string
     */
    public function getName(): string
    {
        return 'quoted-printable';
    }
    
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
        return $this->standardize(quoted_printable_encode($string));
    }
    
    /**
     * Make sure CRLF is correct and HT/SPACE are in valid places.
     *
     * @param  string  $string
     * 
     * @return string
     */
    private function standardize(string $string): string
    {
        // Transform CR or LF to CRLF
        $string = preg_replace('~=0D(?!=0A)|(?<!=0D)=0A~', '=0D=0A', $string);
        
        // Transform =0D=0A to CRLF
        $string = str_replace(["\t=0D=0A", ' =0D=0A', '=0D=0A'], ["=09\r\n", "=20\r\n", "\r\n"], $string);
        
        return match (\ord(substr($string, -1))) {
            0x09 => substr_replace($string, '=09', -1),
            0x20 => substr_replace($string, '=20', -1),
            default => $string,
        };
    }
}