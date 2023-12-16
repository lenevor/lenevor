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

namespace Syscodes\Components\Mail\Mime;

use Syscodes\Components\Contracts\Mail\Mime\MimeType;

/**
 * This class loads an array of mimetypes.
 */
class MimeTypes
{
    /**
     * Gets the MIME types by default.
     * 
     * @var MimeTypes $default
     */
    protected static MimeTypes $default;

    /**
     * The extension of mimetypes.
     * 
     * @var array $extensions
     */
    protected array $extensions;

    /**
     * The guessers instance in array.
     * 
     * @var array $guessers
     */
    protected array $guessers = [];

    /**
     * Get the mimetypes.
     * 
     * @var array $mimeTypes
     */
    protected array $mimeTypes;

    /**
     * Constructor. Create a new MimeTypes class instance.
     * 
     * @param  array  $maps
     * 
     * @return void
     */
    public function __construct(array $maps = [])
    {
        foreach ($maps as $mimeType => $extensions) {
            $this->extensions[$mimeType] = $extensions;
            
            foreach ($extensions as $extension) {
                $this->mimeTypes[$extension][] = $mimeType;
            }
        }
    }    
}