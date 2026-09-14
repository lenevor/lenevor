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

namespace Syscodes\Components\Translation\Concerns;

/**
 * Allows the creates pontentially translated strings.
 */
trait CreatesPotentiallyTranslatedStrings
{
    /**
     * Create a pending potentially translated string.
     *
     * @param  string  $attribute
     * @param  string|null  $message
     * @return \Syscodes\Components\Translation\PotentiallyTranslatedString
     */
    protected function pendingPotentiallyTranslatedString($attribute, $message)
    {
        $destructor = $message === null
            ? fn ($message) => $this->messages[] = $message
            : fn ($message) => $this->messages[$attribute] = $message;

        return new class($message ?? $attribute, $this->validator->getTranslator(), $destructor) extends PotentiallyTranslatedString
        {
            /**
             * The callback to call when the object destructs.
             *
             * @var \Closure
             */
            protected $destructor;

            /**
             * Constructor. Create a new pending potentially translated string.
             *
             * @param  string  $message
             * @param  \Syscodes\Components\Contracts\Translation\Translator  $translator
             * @param  \Closure  $destructor
             */
            public function __construct($message, $translator, $destructor)
            {
                parent::__construct($message, $translator);

                $this->destructor = $destructor;
            }

            /**
             * Magic method.
             * 
             * Handle the object's destruction.
             *
             * @return void
             */
            public function __destruct()
            {
                ($this->destructor)($this->toString());
            }
        };
    }
}