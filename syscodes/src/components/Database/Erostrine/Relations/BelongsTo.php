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

use Syscodes\Components\Database\Erostrine\Model;
use Syscodes\Components\Database\Erostrine\Builder;
use Syscodes\Components\Database\Erostrine\Collection;

/**
 * Relation belongTo given on the parent model.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class BelongsTo extends Relation
{
    /**
     * The foreign key of the parent model.
     * 
     * @var string $foreignKey
     */
    protected $foreignKey;

    /**
     * The associated key on the parent model.
     * 
     * @var string $ownerKey
     */
    protected $ownerKey;

    /**
     * The name of the relationship.
     * 
     * @var string $relationName
     */
    protected $relationName;

    /**
     * Constructor. Create a new BelotngTo instance.
     * 
     * @param  \Syscodes\Components\Database\Erostrine\Builder  $builder
     * @param  \Syscodes\Components\Database\Erostrine\Model  $child
     * @param  string  $foreignKey
     * @param  string  $ownerKey
     * @param  string  $relationName
     * 
     * @return void
     */
    public function __construct(
        Builder $builder,
        Model $child,
        $foreignKey,
        $ownerKey,
        $relationName
    ) {
        $this->ownerKey = $ownerKey;
        $this->foreignKey = $foreignKey;
        $this->relationName = $relationName;

        parent::__construct($builder, $child);
    }

    /**
     * {@inheritdoc}
     */
    public function getResults()
    {
        return $this->query->first();
    }

    /**
     * {@inheritdoc}
     */
    public function addConstraints(): void
    {
        if (static::$constraints) {
            $table = $this->related->getTable();

            $this->query->where($table.'.'.$this->ownerKey, '=', $this->parent->{$this->foreignKey});
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addEagerConstraints(array $models): void
    {
        $key = $this->related->getTable().'.'.$this->ownerKey;

        $this->query->whereIn($key, $this->getEagerModelkeys($models));
    }

    /**
     * Gather the keys from an array of related models.
     * 
     * @param  array  $models
     * 
     * @return array
     */
    public function getEagerModelKeys(array $models): array
    {
        $keys = [];

        foreach ($models as $model) {
            if ( ! is_null($value = $model->{$this->foreignKey})) {
                $keys[]= $value;
            }
        }
        
        if (count($keys) == 0) {
            return array(0);
        }

        sort($keys);

        return array_values(array_unique($keys));
    }

     /**
     * {@inheritdoc}
     */
    public function initRelation(array $models, $relation): array
    {
        foreach ($models as $model) {
            $model->setRelation($relation, null);
        }

        return $models;
    }
    
    /**
     * {@inheritdoc}
     */
    public function match(array $models, Collection $results, $relation): array
    {
        $foreign = $this->foreignKey;        
        $other   = $this->otherKey;
        
        $dictionary = [];
        
        foreach ($results as $result) {
            $dictionary[$result->getAttribute($other)] = $result;
        }
        
        foreach ($models as $model) {
            if (isset($dictionary[$model->$foreign])) {
                $model->setRelation($relation, $dictionary[$model->$foreign]);
            }
        }
        
        return $models;
    }
    
    /**
     * Associate the model instance to the given parent.
     * 
     * @param  \Syscodes\Components\Database\Erostrine\Model|int|string|null  $model
     * 
     * @return \Syscodes\Components\Database\Erostrine\Model
     */
    public function associate($model)
    {
        $ownerKey = $model instanceof Model ? $model->getAttribute($this->ownerKey) : $model;
        
        $this->parent->setAttribute($this->foreignKey, $ownerKey);
        
        if ($model instanceof Model) {
            $this->parent->setRelation($this->relationName, $model);
        } else {
            $this->parent->unsetRelation($this->relationName);
        }
        
        return $this->parent;
    }
    
    /**
     * Dissociate previously associated model from the given parent.
     * 
     * @return \Syscodes\Components\Database\Erostrine\Model
     */
    public function dissociate()
    {
        $this->parent->setAttribute($this->foreignKey, null);
        
        return $this->parent->setRelation($this->relationName, null);
    }

    /**
     * Update the parent model on the relationship.
     * 
     * @param  array  $attributes
     * 
     * @return mixed
     */
    public function update(array $attributes)
    {
        $instance = $this->getResults();

        return $instance->fill($attributes)->save();
    }

    /**
     * Get the foreign key of the relationship.
     * 
     * @return string
     */
    public function getForeignKeyName(): string
    {
        return $this->foreignKey;
    }

    /**
     * Get the fully qualified foreign key of the relationship.
     * 
     * @return string
     */
    public function getQualifiedForeignKey(): string
    {
        return $this->parent->getTable().'.'.$this->foreignkey;
    }

    /**
     * Get the associated key of the relationship.
     * 
     * @return string
     */
    public function getOwnerKey(): string
    {
        return $this->ownerKey;
    }

    /**
     * Get the fully qualified associated key of the relationship.
     * 
     * @return string
     */
    public function getQualifiedOwnerKeyName(): string
    {
        return $this->related->getTable().'.'.$this->ownerKey;
    }

    /**
     * Get the name of the relationship.
     * 
     * @return string
     */
    public function getRelationName(): string
    {
        return $this->relationName;
    }
}