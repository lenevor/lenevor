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

namespace Syscodes\Components\Validation\Rules;

use Syscodes\Components\Contracts\Validation\ParameterizedRule;
use Syscodes\Components\Validation\Rules\Concerns\FormatsParameters;

/**
 * Allows the confirmation filed for a rule.
 */
class Confirmed implements ParameterizedRule
{
    use FormatsParameters;

    /**
     * Get the confirmation field.
     * 
     * @var string|null
     */
    protected ?string $confirmationField = null;

    /**
     * Specify the confirmation field.
     * 
     * @param  string  $confirmationField
     * 
     * @return $this
     */
    public function customField(string $confirmationField): static
    {
        $this->confirmationField = $confirmationField;

        return $this;
    }

    /**
     * Get the rule name followed by its parameters.
     * 
     * @return array
     */
    public function toArray(): array
    {
        return $this->confirmationField === null ? ['confirmed'] : ['confirmed', $this->confirmationField];
    }
}