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
 * @copyright   Copyright (c) 2019 - 2022 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Database\Erostrine\Relations\Concerns;

use Syscodes\Components\Database\Erostrine\Model;

/**
 * Trait AsPivotTable.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
trait AsPivotTable
{
    /**
     * The name of the foreign key column.
     * 
     * @var string $foreignKey
     */
    protected $foreignKey;

    /**
     * The parent model of the relationship.
     * 
     * @var \Syscodes\Components\Database\Erostrine\Model $pivotParent
     */
    protected $pivotParent;

    /**
     * The name of the "other key" column.
     * 
     * @var string $relatedKey
     */
    protected $relatedKey;

    /**
     * Create a new Pivot model instance.
     * 
     * @param  \Syscodes\Components\Database\Erostrine\Model  $model
     * @param  array  $attributes
     * @param  string  $table
     * @param  bool  $exists
     * 
     * @return static
     */
    public static function fromAttributes(
        Model $parent, 
        $attributes, 
        $table,
        bool $exists = false
    ) {
        $instance = new static;

        $instance->setConnection($parent->getConnectionName())
                 ->setTable($table)
                 ->fill($attributes)
                 ->syncOriginal();

        $instance->pivotParent = $parent;

        $instance->exists = $exists;

        return $instance;
    }
}