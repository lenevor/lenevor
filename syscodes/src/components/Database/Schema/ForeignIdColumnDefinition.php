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

namespace Syscodes\Components\Database\Schema;

use Syscodes\Components\Support\Stringable;

/**
 * Allows the foreign id column definition.
 */
class ForeignIdColumnDefinition extends ColumnDefinition
{
    /**
     * The schema builder Dataprint instance.
     *
     * @var \Syscodes\Components\Database\Schema\Dataprint
     */
    protected $dataprint;

    /**
     * Constructor. Create a new foreign ID column definition.
     *
     * @param  \Syscodes\Components\Database\Schema\Dataprint  $dataprint
     * @param  array  $attributes 
     * @return void
     */
    public function __construct(Dataprint $dataprint, $attributes = [])
    {
        parent::__construct($attributes);

        $this->dataprint = $dataprint;
    }

    /**
     * Create a foreign key constraint on this column referencing the "id" column of the conventionally related table.
     *
     * @param  string|null  $table
     * @param  string|null  $column
     * @param  string|null  $indexName 
     * @return \Syscodes\Components\Database\Schema\ForeignKeyDefinition
     */
    public function constrained($table = null, $column = null, $indexName = null)
    {
        $table ??= $this->table;
        $column ??= $this->referencesModelColumn ?? 'id';

        return $this->references($column, $indexName)->on($table ?? (new Stringable($this->name))->beforeLast('_'.$column)->plural());
    }

    /**
     * Specify which column this foreign ID references on another table.
     *
     * @param  string  $column
     * @param  string|null  $indexName 
     * @return \Syscodes\Components\Database\Schema\ForeignKeyDefinition
     */
    public function references($column, $indexName = null)
    {
        return $this->dataprint->foreign($this->name, $indexName)->references($column);
    }
}