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

namespace Syscodes\Components\Database\Erostrine\Relations;

use Syscodes\Components\Database\Erostrine\Model;
use Syscodes\Components\Database\Erostrine\Builder;
use Syscodes\Components\Database\Erostrine\Collection;
use Syscodes\Components\Database\Erostrine\Relations\Concerns\SupportModelRelations;

/**
 * Relation belongTo given on the parent model.
 */
class BelongsTo extends Relation
{
    use SupportModelRelations;
    
    /**
     * The child model instance of the relation.
     * 
     * @var \Syscodes\Components\Database\Erostrine\Model $child
     */
    protected $child;

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
        $this->child = $child;
        $this->ownerKey = $ownerKey;
        $this->foreignKey = $foreignKey;
        $this->relationName = $relationName;

        parent::__construct($builder, $child); 
    }

    /**
     * Get the results of the relationship.
     * 
     * @return mixed
     */
    public function getResults()
    {
        if (is_null($this->child->{$this->foreignKey})) {
            return $this->getDefaultFor($this->parent);
        }

        return $this->getRelationQuery()->first() ?: $this->getDefaultFor($this->parent);
    }

    /**
     * Set the base constraints on the relation query.
     * 
     * @return void
     */
    public function addConstraints(): void
    {
        if (static::$constraints) {
            $table = $this->related->getTable();

            $this->getRelationQuery()->where($table.'.'.$this->ownerKey, '=', $this->child->{$this->foreignKey});
        }
    }

    /**
     * Set the constraints for an eager load of the relation.
     * 
     * @param  array  $models
     * 
     * @return void
     */
    public function addEagerConstraints(array $models): void
    {
        $key = $this->related->getTable().'.'.$this->ownerKey;

        $this->getRelationQuery()->whereIn($key, $this->getEagerModelkeys($models));
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
        
        sort($keys);

        return array_values(array_unique($keys));
    }

    /**
     * Initialize the relation on a set of models.
     * 
     * @param  array  $models
     * @param  string  $relation
     * 
     * @return array
     */
    public function initRelation(array $models, $relation): array
    {
        foreach ($models as $model) {
            $model->setRelation($relation, $this->getDefaultFor($model));
        }

        return $models;
    }
    
    /**
     * Match the eagerly loaded results to their parents.
     * 
     * @param  array  $models
     * @param  \Syscodes\Components\Database\Erostrine\Collection  $results
     * @param  string  $relation
     * 
     * @return array
     */
    public function match(array $models, Collection $results, $relation): array
    {
        $foreign = $this->foreignKey;        
        $owner   = $this->ownerKey;
        
        $dictionary = [];
        
        foreach ($results as $result) {
            $dictionary[$result->getAttribute($owner)] = $result;
        }
        
        foreach ($models as $model) {
            if (isset($dictionary[$model->{$foreign}])) {
                $model->setRelation($relation, $dictionary[$model->{$foreign}]);
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
        
        $this->child->setAttribute($this->foreignKey, $ownerKey);
        
        if ($model instanceof Model) {
            $this->child->setRelation($this->relationName, $model);
        } else {
            $this->child->unsetRelation($this->relationName);
        }
        
        return $this->child;
    }
    
    /**
     * Dissociate previously associated model from the given parent.
     * 
     * @return \Syscodes\Components\Database\Erostrine\Model
     */
    public function dissociate()
    {
        $this->child->setAttribute($this->foreignKey, null);
        
        return $this->child->setRelation($this->relationName, null);
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
        return $this->getResults()->fill($attributes)->save();
    }

    /**
     * Make a new related instance for the given model.
     * 
     * @param  \Syscodes\Components\Database\Erostrine\Model  $parent
     * 
     * @return \Syscodes\Components\Database\Erostrine\Model
     */
    protected function newRelatedInstanceFor(Model $parent)
    {
        return $this->related->newInstance();
    }
    
    /**
     * Get the child of the relationship.
     * 
     * @return \Syscodes\Components\Database\Erostrine\Model
     */
    public function getChild()
    {
        return $this->child;
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
     * Get the key value of the child's foreign key.
     * 
     * @return mixed
     */
    public function getParentKey()
    {
        return $this->child->{$this->foreignKey};
    }

    /**
     * Get the fully qualified foreign key of the relationship.
     * 
     * @return string
     */
    public function getQualifiedForeignKey(): string
    {
        return $this->child->qualifyColumn($this->foreignKey);
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
        return $this->related->qualifyColumn($this->ownerKey);
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