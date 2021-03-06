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

namespace Syscodes\Contracts\Translation;

/**
 * Gets the translations for the given key with default locale.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
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
     * Get the default locale being used.
     * 
     * @return string
     */
    public function getLocale();

    /**
     * Set the default locale.
     * 
     * @param  string  $locale
     * 
     * @return void
     */
    public function setLocale($locale);

     /**
     * Get the fallback locale being used.
     * 
     * @return string
     */
    public function getFallback();

    /**
     * Set the default locale.
     * 
     * @param  string  $locale
     * 
     * @return void
     */
    public function setFallback($fallback);
}