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

namespace Syscodes\Components\Contracts\Validation;

use Syscodes\Components\Contracts\Support\MessageProvider;

/**
 * Get validator.
 */
interface Validator extends MessageProvider
{
    /**
     * Add an after validation callback.
     *
     * @param  callable|array|string  $callback
     * @return static
     */
    public function after($callback): static;

    /**
     * Determine if the data fails the validation rules.
     *
     * @return bool
     */
    public function fails(): bool;

    /**
     * Run the validator's rules against its data.
     *
     * @return array
     *
     * @throws \Syscodes\Components\Validation\Exceptions\ValidationException
     */
    public function validate(): array;

    /**
     * Get the attributes and values that were validated.
     *
     * @return array
     *
     * @throws \Syscodes\Components\Validation\Exceptions\ValidationException
     */
    public function validated();

    /**
     * Get the failed validation rules.
     *
     * @return array
     */
    public function failed(): array;

    /**
     * An alternative more semantic shortcut to the message container.
     *
     * @return \Syscodes\Components\Support\MessageBag
     */
    public function errors();
}