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

namespace Syscodes\Components\Database\Console\Migrations;

/**
 * Gets to guess the table name and creation status of the given migration.
 */
class TableGuesser
{
    const CREATE_PATTERNS = [
        '/^create_(\w+)_table$/',
        '/^create_(\w+)$/',
    ];

    const CHANGE_PATTERNS = [
        '/.+_(to|from|in)_(\w+)_table$/',
        '/.+_(to|from|in)_(\w+)$/',
    ];

    /**
     * Attempt to guess the table name and "creation" status of the given migration.
     *
     * @param  string  $migration
     * 
     * @return array
     */
    public static function guess($migration)
    {
        foreach (self::CREATE_PATTERNS as $pattern) {
            if (preg_match($pattern, $migration, $matches)) {
                return [$matches[1], $create = true];
            }
        }

        foreach (self::CHANGE_PATTERNS as $pattern) {
            if (preg_match($pattern, $migration, $matches)) {
                return [$matches[2], $create = false];
            }
        }
    }
}