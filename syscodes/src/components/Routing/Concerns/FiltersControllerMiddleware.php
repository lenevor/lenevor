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

namespace Syscodes\Components\Routing\Concerns;

/**
 * Allows the filters controller middleware. 
 */
trait FiltersControllerMiddleware
{
    /**
     * Determine if the given options exclude a particular method.
     * 
     * @param  string  $method
     * @param  array  $options
     * 
     * @return bool
     */
    protected function methodExcludedByOptions($method, array $options): bool
    {
        return (isset($options['only']) && ! in_array($method, (array) $options['only'])) ||
            ( ! empty($options['except']) && in_array($method, (array) $options['except']));
    }
}