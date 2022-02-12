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

namespace Syscodes\Components\Database\Erostrine\Relations;

use Syscodes\Components\Support\Str;
use Syscodes\Components\Database\Erostrine\Model;
use Syscodes\Components\Database\Erostrine\Builder;

/**
 * Allows the relation of two tables.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class Pivot extends Model
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
     * @var \Syscodes\Components\Database\Erostrine\Model $parent
     */
    protected $parent;

    /**
     * The name of the "other key" column.
     * 
     * @var string $relatedKey
     */
    protected $relatedKey;

    /**
     * Constructor. Create a new Pivot instance.
     * 
     * @param  \Syscodes\Components\Database\Erostrine\Model  $parent
     * @param  array  $attributes
     * @param  string  $table
     * @param  bool  $exists
     * 
     * @return void
     */
    public function __construct(Model $parent, $attributes, $table, $exists = false)
    {
        parent::__construct();

        $this->setRawAttributes($attributes, true);

        $this->setConnection($parent->getConnectionName())
             ->setTable($table);

        $this->parent = $parent;
        $this->exists = $exists;
    }

    /**
     * Set the keys for a save update query.
     * 
     * @param  \Syscodes\Components\Database\Erostrine\Builder  $builder
     * 
     * @return \Syscodes\Components\Database\Erostrine\Builder
     */
    protected function setKeysforSelectQuery(Builder $builder)
    {
        $builder->where(
            $this->foreignKey, $this->getAttribute($this->foreignKey)
        );

        return $builder->where(
            $this->relatedKey, $this->getAttribute($this->relatedKey)
        );
    }

    /**
     * Delete the pivot model record fron the database.
     * 
     * @return int
     */
    public function delete()
    {
        if ($this->fireModelEvent('deleting') === false) {
			return 0;
		}

        return take($this->getDeletequery()->delete(), function () {
            $this->exists = false;

            $this->fireModelEvent('deleted', false);
        });
    }

    /**
     * Get the query builder for a delete operation on the pivot.
     * 
     * @return \Syscodes\Components\Database\Erostrine\Builder
     */
    protected function getDeleteQuery()
    {
        return $this->newQuery()->where([
            $this->foreignKey => $this->getAttribute($this->foreignKey),
            $this->relatedKey => $this->getAttribute($this->relatedKey)
        ]);
    }

    /**
     * Get the table associated with the model.
     * 
     * @return string
     */
    public function getTable(): string
    {
        if ( ! isset($this->table)) {
            $this->setTable(
                str_replace('\\', '', Str::snake(Str::singular(class_basename($this))))
            );
        }

        return $this->table;
    }

    /**
     * Get the foreign key column name.
     * 
     * @return string
     */
    public function getForeignkey(): string
    {
        return $this->foreignKey;
    }

    /**
     * Get the "related key" column name.
     * 
     * @return string
     */
    public function getRelatedKey(): string
    {
        return $this->relatedKey;
    }

    /**
     * Set the key names for the pivot model instance.
     * 
     * @param  string  $foreignKey
     * @param  string  $relatedKey
     * 
     * @return self
     */
    public function setPivotKeys($foreignKey, $relatedKey): self
    {
        $this->foreignKey = $foreignKey;
        $this->relatedKey = $relatedKey;

        return $this;
    }
}