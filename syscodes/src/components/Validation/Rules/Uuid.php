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
 * Allows specify the Uuid depends your version.
 */
class Uuid implements ParameterizedRule
{
    use FormatsParameters;

    /**
     * @var int<0, 8>|'nil'|'max'|null
     */
    protected int|string|null $version = null;

    /**
     * Specify the UUID version.
     *
     * @param  int<0, 8>|'nil'|'max'  $version
     * @return $this
     */
    public function version(int|string $version): static
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get the rule name followed by its parameters.
     * 
     * @return array
     */
    public function toArray(): array
    {
        return $this->version === null ? ['uuid'] : ['uuid', $this->version];
    }
}