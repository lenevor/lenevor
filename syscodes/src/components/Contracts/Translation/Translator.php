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

namespace Syscodes\Components\Contracts\Translation;

/**
 * Gets the translations for the given key with default locale.
 */
interface Translator
{
    /**
     * Get the translation for the given key.
     * 
     * @param  string  $key
     * @param  array  $replace
     * @param  string|null  $locale
     * @param  bool  $fallback
     * 
     * @return string|array
     */
    public function get($key, array $replace = [], string $locale = null, bool $fallback = true);

    /**
     * Determine if a translation exists for a given locale.
     * 
     * @param  string  $key
     * @param  string|null  $locale
     * 
     * @return bool
     */
    public function hasForLocale($key, $locale = null): bool;

    /**
     * Determine if a translation exists.
     * 
     * @param  string  $key
     * @param  string|null  $locale
     * @param  bool  $fallback
     * 
     * @return bool
     */
    public function has($key, $locale = null, $fallback = true): bool;

    /**
     * Get the default locale being used.
     * 
     * @return string
     */
    public function getLocale(): string;

    /**
     * Set the default locale.
     * 
     * @param  string  $locale
     * 
     * @return void
     */
    public function setLocale(string $locale): void;

    /**
     * Get the fallback locale being used.
     * 
     * @return string
     */
    public function getFallback(): string;

    /**
     * Set the default locale.
     * 
     * @param  string  $locale
     * 
     * @return void
     */
    public function setFallback($fallback): void;

    /**
     * Set the loaded translation groups.
     * 
     * @param  array  $loaded
     * 
     * @return void
     */
    public function setLoaded(array $loaded): void;
}