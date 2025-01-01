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

namespace Syscodes\Components\Contracts\Mail;

/**
 * Gets the data of header rendered.
 */
interface Header
{
    /**
	 * Adds multiple header.
	 * 
	 * @param  string  $headers  The header name
	 * 
	 * @return static
	 */
    public function add(string $headers): static;
    
    /**
     * If exist the name of header.
     * 
     * @param  string  $name
     * 
     * @return bool
     */
    public function has(string $name): bool;

    /**
     * Gets a header value by name.
     * 
     * @param  string  $name
     * 
     * @return mixed
     */
    public function get(string $name): mixed;

    /**
     * Returns all the headers.
     * 
     * @param  string|null  $name
     * 
     * @return \iterable
     */
    public function all(?string $name = null): iterable;

    /**
	 * Removes a header.
	 * 
	 * @param  string  $name  The header name
	 * 
	 * @return void
	 */
    public function remove(string $name): void;

    /**
     * Gets the name.
     * 
     * @return array
     */
    public function getNames(): array;
    
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

    /**
     * Get the instance as an array.
     * 
     * @return array
     */
    public function toArray(): array;
}