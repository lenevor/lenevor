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

namespace Syscodes\Components\Validation\Rules\Concerns;

/**
 * Allows the formats parameters for validation.
 */
trait FormatsParameters
{
    /**
     * Magic method.
     * 
     * Convert the rule to a validation string.
     * 
     * @return string 
     */
    public function __toString(): string
    {
        $parameters = $this->toArray();
        $rule = array_shift($parameters);

        return $rule.($parameters ? ':'.$this->formatParameters($parameters) : '');
    }

    /**
     * Format parameters for a legacy validation rule string.
     * 
     * @param  array  $parameters
     * @return string
     */
    protected function formatParameters(array $parameters): string
    {
        return implode(',', array_map(static function ($parameter) {
            $parameter = (string) $parameter;

            return str_contains($parameter, ',') || str_contains($parameter, '"')
                ? '"'.str_replace('"', '""', $parameter).'"'
                : $parameter;
        }, $parameters));
    }
}