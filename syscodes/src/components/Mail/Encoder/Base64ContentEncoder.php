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

use TypeError;
use RuntimeException;

/**
 * Get the content Base64 encoded for text strings.
 */
final class Base64ContentEncoder extends Base64Encoder
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
        
        $filter = stream_filter_append($stream, 'convert.base64-encode', \STREAM_FILTER_READ, [
            'line-length' => 0 >= $maxLineLength || 76 < $maxLineLength ? 76 : $maxLineLength,
            'line-break-chars' => "\r\n",
        ]);
        
        if ( ! \is_resource($filter)) {
            throw new RuntimeException('Unable to set the base64 content encoder to the filter');
        }

        while ( ! feof($stream)) {
            yield fread($stream, 16372);
        }
        
        stream_filter_remove($filter);
    }
    
    /**
     * Get the encoded name.
     * 
     * @return string
     */
    public function getName(): string
    {
        return 'base64';
    }
}