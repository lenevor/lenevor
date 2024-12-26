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
 * @copyright   Copyright (c) 2019 - 2024 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Validation;

/**
 * Allows count the number of objects in a collection. 
 */
interface PresenceInterface
{
    /**
     * Count the number of objects in a collection having the given value.
     * 
     * @param  string  $collection
     * @param  string  $column
     * @param  string  $value
     * @param  int|null  $excludeId
     * @param  string|null  $idColumn
     * @param  array  $extra
     * 
     * @return int
     */
    public function getCount(
        $collection, 
        $column, 
        $value, 
        $excludeId = null, 
        $idColumn = null, 
        array $extra = []
    ): int;
    
    /**
     * Count the number of objects in a collection with the given values.
     * 
     * @param  string  $collection
     * @param  string  $column
     * @param  array  $values
     * @param  array  $extra
     * 
     * @return int
     */
    public function getMultiCount(
        $collection, 
        $column, 
        array $values, 
        array $extra = []
    ): int;
}