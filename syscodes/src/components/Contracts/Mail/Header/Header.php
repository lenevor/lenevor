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

namespace Syscodes\Components\Contracts\Mail;

/**
 * Gets the data of header rendered.
 */
interface Header
{
    /**
     * Sets the body.
     * 
     * @param  mixed  $body
     * 
     * @return void
     */
    public function setBody(mixed $body): void;
    
    /**
     * Gets the body.
     * 
     * @return mixed
     */    
    public function getBody(): mixed;
    
    /**
     * Sets the charset.
     * 
     * @param  string  $chaset
     * 
     * @return void
     */
    public function setCharset(string $charset): void;
    
    /**
     * Gets the charset.
     * 
     * @return string|null
     */
    public function getCharset(): ?string;
    
    /**
     * Sets the language.
     * 
     * @param  string  $lang
     * 
     * @return void
     */
    public function setLanguage(string $lang): void;
    
    /**
     * Gets the language.
     * 
     * @return string|null
     */
    public function getLanguage(): ?string;
    
    /**
     * Gets the name.
     * 
     * @return string
     */
    public function getName(): string;
    
    /**
     * Sets the max line length.
     * 
     * @param  int  $lineLength
     * 
     * @return void
     */
    public function setMaxLineLength(int $lineLength): void;
    
    /**
     * Gets the max line length.
     * 
     * @return int
     */
    public function getMaxLineLength(): int;
    
    /**
     * Gets this Header rendered as a compliant string.
     * 
     * @return string
     */
    public function toString(): string;
}