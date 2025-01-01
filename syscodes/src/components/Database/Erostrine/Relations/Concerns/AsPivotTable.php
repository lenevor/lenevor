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
 * @copyright   Copyright (c) 2019 - 2025 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Database\Erostrine\Relations\Concerns;

use Syscodes\Components\Support\Str;
use Syscodes\Components\Database\Erostrine\Model;
use Syscodes\Components\Database\Erostrine\Builder;

/**
 * Trait AsPivotTable.
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
    ): static {
        $instance = new static;

        $instance->setConnection($parent->getConnectionName())
                 ->setTable($table)
                 ->fill($attributes)
                 ->syncOriginal();

        $instance->pivotParent = $parent;

        $instance->exists = $exists;

        return $instance;
    }

    /**
     * Create a new pivot model from raw values.
     * 
     * @param  \Syscodes\Components\Database\Erostrine\Model  $model
     * @param  array  $attributes
     * @param  string  $table
     * @param  bool  $exists
     * 
     * @return static
     */
    public static function fromRawAttributtes(
        Model $parent, 
        $attributes, 
        $table,
        bool $exists = false
    ): static {
        $instance = static::fromAttributes($parent, [], $table, $exists);

        $instance->setRawAttributes($attributes, true);

        return $instance;
    }

    /**
     * Set the keys for a save update query.
     * 
     * @param  \Syscodes\Components\Database\Erostrine\Builder  $builder
     * 
     * @return \Syscodes\Components\Database\Erostrine\Builder
     */
    public function setKeysForSaveQuery(Builder $builder)
    {
        if (isset($this->attributes[$this->getKeyName()])) {
            return parent::setKeysForSaveQuery($builder);
        }
        
        $builder->where($this->foreignKey, $this->getOriginal(
            $this->foreignKey, $this->getAttribute($this->foreignKey)
        ));
        
        return $builder->where($this->relatedKey, $this->getOriginal(
            $this->relatedKey, $this->getAttribute($this->relatedKey)
        ));
    }

    /**
     * Delete the pivot model record from the database.
     * 
     * @return int
     */
    public function delete(): int
    {
        if (isset($this->attributes[$this->getKeyName()])) {
            return (int) parent::delete();
        }
        
        if ($this->fireModelEvent('deleting') === false) {
            return 0;
        }
        
        return take($this->getDeleteQuery()->delete(), function () {
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
            $this->foreignKey => $this->getOriginal($this->foreignKey, $this->getAttribute($this->foreignKey)),
            $this->relatedKey => $this->getOriginal($this->relatedKey, $this->getAttribute($this->relatedKey)),
        ]);
    }

    /**
     * Get the table associated with the model.
     * 
     * @return string
     */
    public function getTable(): string
    {
        $class = str_replace(
            '\\', '', Str::snake(Str::singular(class_basename($this)))
        );

        return $this->table ?? $this->setTable($class);
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
        return $this->relatedkey;
    }

    /**
     * Set the key names for the pivot model instance.
     * 
     * @param  string  $foreignKey
     * @param  string  $relatedKey
     * 
     * @return static
     */
    public function setPivotKeys($foreignKey, $relatedKey): static
    {
        $this->foreignKey = $foreignKey;
        $this->relatedkey = $relatedKey;

        return $this;
    }
}