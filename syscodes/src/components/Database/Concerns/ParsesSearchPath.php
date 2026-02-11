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

namespace Syscodes\Components\Database\Concerns;

/**
 * Allows the Postgres database "search_path" configuration.
 */
trait ParsesSearchPath
{
    /**
     * Parse the Postgres "search_path" configuration value into an array.
     *
     * @param  string|array|null  $searchPath
     * 
     * @return array
     */
    protected function parseSearchPath($searchPath): array
    {
        if (is_string($searchPath)) {
            preg_match_all('/[^\s,"\']+/', $searchPath, $matches);

            $searchPath = $matches[0];
        }

        return array_map(function ($schema) {
            return trim($schema, '\'"');
        }, $searchPath ?? []);
    }
}