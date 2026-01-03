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

namespace Syscodes\Components\Database\Erostrine\Concerns;

use Syscodes\Components\Database\Erostrine\Builder;
use Syscodes\Components\Database\Erostrine\Model;
use Syscodes\Components\Database\Erostrine\Relations\BelongsTo;
use Syscodes\Components\Database\Erostrine\Relations\HasMany;
use Syscodes\Components\Database\Erostrine\Relations\HasOne;
use Syscodes\Components\Support\Str;

/**
 * HasRelations.
 */
trait HasRelations
{
    /**
     * The loaded relationships for the model.
     * 
     * @var array|object
     */
    protected $relations = [];
    
    /**
     * Define a one-to-one relationship.
     * 
     * @param  string  $related
     * @param  string|null  $foreignKey
     * @param  string|null  $localKey
     * 
     * @return \Syscodes\Components\Database\Erostrine\Relations\HasOne
     */
    public function hasOne($related, $foreignKey = null, $localKey = null)
    {
        $instance   = $this->newRelatedInstance($related);        
        $foreignKey = $foreignKey ?: $this->getForeignKey();
        $localKey   = $localKey ?: $this->getKeyName();
        
        return $this->newHasOne(
            $instance->newQuery(), $this, $instance->getTable().'.'.$foreignKey, $localKey
        );
    }
    
    /**
     * Instantiate a new HasOne relationship.
     * 
     * @param  \Syscodes\Components\Database\Erostrine\Builder  $builder
     * @param  \Syscodes\Components\Database\Erostrine\Model  $parent
     * @param  string  $foreignKey
     * @param  string  $localKey
     * 
     * @return \Syscodes\Components\Database\Erostrine\Relations\HasOne
     */
    protected function newHasOne(Builder $builder, Model $parent, $foreignKey, $localKey)
    {
        return new HasOne($builder, $parent, $foreignKey, $localKey);
    }

    /**
     * Define a one-to-many relationship.
     * 
     * @param  string  $related
     * @param  string|null  $foreignKey
     * @param  string|null  $localKey
     * 
     * @return \Syscodes\Components\Database\Erostrine\Relations\HasOne
     */
    public function hasMany($related, $foreignKey = null, $localKey = null)
    {
        $instance   = $this->newRelatedInstance($related);        
        $foreignKey = $foreignKey ?: $this->getForeignKey();
        $localKey   = $localKey ?: $this->getKeyName();
        
        return $this->newHasMany(
            $instance->newQuery(), $this, $instance->getTable().'.'.$foreignKey, $localKey
        );
    }
    
    /**
     * Instantiate a new HasMany relationship.
     * 
     * @param  \Syscodes\Components\Database\Erostrine\Builder  $builder
     * @param  \Syscodes\Components\Database\Erostrine\Model  $parent
     * @param  string  $foreignKey
     * @param  string  $localKey
     * 
     * @return \Syscodes\Components\Database\Erostrine\Relations\HasMany
     */
    protected function newHasMany(Builder $builder, Model $parent, $foreignKey, $localKey)
    {
        return new HasMany($builder, $parent, $foreignKey, $localKey);
    }

    /**
     * Define an inverse one-to-one or many relationship.
     * 
     * @param  string  $related
     * @param  string|null  $foreignKey
     * @param  string|null  $ownerKey
     * @param  string|null  $relation
     * 
     * @return \Syscodes\Components\Database\Erostrine\Relations\BelongsTo
     */
    public function belongsTo($related, $foreignKey = null, $ownerKey = null, $relation = null)
    {
        if (is_null($relation)) {
            $relation = $this->callBelongsToRelation();
        }

        $instance = $this->newRelatedInstance($related);

        if (is_null($foreignKey)) {
            $foreignKey = Str::snake($relation).'_'.$instance->getKeyName();
        }

        $ownerKey = $ownerKey ?: $instance->getKeyName();

        return $this->newBelongsTo(
            $instance->newQuery(), $this, $foreignKey, $ownerKey, $relation
        );
    }
    
    /**
     * Instantiate a new BelongsTo relationship.
     * 
     * @param  \Syscodes\Components\Database\Erostrine\Builder  $builder
     * @param  \Syscodes\Components\Database\Erostrine\Model  $child
     * @param  string  $foreignKey
     * @param  string  $ownerKey
     * @param  string  $relation
     * 
     * @return \Syscodes\Components\Database\Erostrine\Relations\BelongsTo
     */
    protected function newBelongsTo(Builder $builder, Model $child, $foreignKey, $ownerKey, $relation)
    {
        return new BelongsTo($builder, $child, $foreignKey, $ownerKey, $relation);
    }
    
    /**
     * Calls the "belongs to" relationship name.
     * 
     * @return string
     */
    protected function callBelongsToRelation(): string
    {
        [$one, $two, $caller] = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
        
        return $caller['function'];
    }
    
    /**
     * Create a new model instance for a related model.
     * 
     * @param  string  $class
     * 
     * @return mixed
     */
    protected function newRelatedInstance($class)
    {
        return take(new $class, function ($instance) {
            if ( ! $instance->getConnectionName()) {
                $instance->setConnection($this->connection);
            }
        });
    }

    /**
     * Get all the loaded relations for the instance.
     * 
     * @return array
     */
    public function getRelations(): array
    {
        return $this->relations;
    }

    /**
     * Get a specified relationship.
     * 
     * @param  string  $relation
     * 
     * @return mixed
     */
    public function getRelation(string $relation): mixed
    {
        return $this->relations[$relation];
    }

    /**
     * Set the given relationship on the model.
     * 
     * @param  string  $relation
     * @param  mixed  $value
     * 
     * @return self
     */
    public function setRelation(string $relation, $value): self
    {
        $this->relations[$relation] = $value;

        return $this;
    }

    /**
     * Determine if the given relation is loaded.
     * 
     * @param  string  $key
     * 
     * @return bool
     */
    public function relationLoaded(string $key): bool
    {
        return array_key_exists($key, $this->relations);
    }

    /**
     * Set the entire relations array on the model.
     * 
     * @param  array  $relations
     * 
     * @return static
     */
    public function setRelations(array $relations): static
    {
        $this->relations = $relations;

        return $this;
    }

    /**
     * Unset a loaded relationship.
     * 
     * @param  string  $relation
     * 
     * @return static
     */
    public function unsetRelation(string $relation): static
    {
        unset($this->relations[$relation]);

        return $this;
    }
}