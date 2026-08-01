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
 * @copyright   Copyright (c) 2019 - 2026 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Translation;

use Stringable;

/**
 * Allows the potentially translated in string.
 */
class PotentiallyTranslatedString implements Stringable
{
    /**
     * The string that may be translated.
     *
     * @var string
     */
    protected $string;

    /**
     * The translated string.
     *
     * @var string|null
     */
    protected $translation;

    /**
     * The validator that may perform the translation.
     *
     * @var \Syscodes\Components\Contracts\Translation\Translator
     */
    protected $translator;

    /**
     * Constructor. Create a new potentially translated string.
     *
     * @param  string  $string
     * @param  \Syscodes\Components\Contracts\Translation\Translator  $translator
     * @return void
     */
    public function __construct($string, $translator)
    {
        $this->string = $string;

        $this->translator = $translator;
    }

    /**
     * Translate the string.
     *
     * @param  array  $replace
     * @param  string|null  $locale
     * @return static
     */
    public function translate($replace = [], $locale = null): static
    {
        $this->translation = $this->translator->get($this->string, $replace, $locale);

        return $this;
    }

    /**
     * Get the original string.
     *
     * @return string
     */
    public function original(): string
    {
        return $this->string;
    }

    /**
     * Get the potentially translated string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->translation ?? $this->string;
    }

    /**
     * Get the potentially translated string.
     *
     * @return string
     */
    public function toString(): string
    {
        return (string) $this;
    }
}