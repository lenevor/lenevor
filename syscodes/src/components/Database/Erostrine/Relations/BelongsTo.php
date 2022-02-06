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
use Syscodes\Components\Database\Erostrine\Relations\Relation;

/**
 * Relation belongTo given on the parent model.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class BelongsTo extends Relation
{
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
        return $this->queryBuilder->first();
    }

    /**
     * Set the base constraints on the relation query.
     * 
     * @return void
     */
    public function addConstraints()
    {
        if (static::$constraints) {
            $table = $this->related->getTable();

            $this->queryBuilder->where($table.'.'.$this->ownerKey, '=', $this->child->{$this->foreignKey});
        }
    }

    /**
     * Set the constraints for an eager load of the relation.
     * 
     * @param  array  $models
     * 
     * @return void
     */
    public function addEagerConstraints(array $models)
    {
        $key = $this->related->getTable().'.'.$this->ownerKey;

        $this->queryBuilder->whereIn($key, $this->getEagerModelkeys($models));
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

        foreach ($mdoels as $model) {
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
    public function initRelation(array $models, $relation)
    {
        foreach ($models as $model) {
            $model->setRelation($relation, null);
        }

        return $models;
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
    public function getForeignKey(): string
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
        return $this->child->getTable().'.'.$this->foreignkey;
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
    public function getQuafiedOwnerKeyName(): string
    {
        return $this->related->getTable().'.'.$this->ownerKey;
    }
}