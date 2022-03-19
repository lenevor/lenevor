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
use Syscodes\Components\Database\Erostrine\Collection;

/**
 * Relation belongToMany given on the parent model.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class BelongsToMany extends Relation
{
    /**
     * The foreign key of the parent model.
     * 
     * @var string $foreignKey
     */
    protected $foreignKey;

    /**
     * The associated key of the relation.
     * 
     * @var string $ownerKey
     */
    protected $ownerKey;
    
    /**
     * The pivot table columns to retrieve.
     * 
     * @var array $pivotColumns
     */
    protected $pivotColumns = [];
    
    /**
     * Any pivot table restrictions.
     * 
     * @var array $pivotWheres
     */
    protected $pivotWheres = [];

    /**
     * The "name" of the relationship.
     * 
     * @var string $relationName
     */
    protected $relationName;

    /**
     * The intermediate table for the relation.
     * 
     * @var string $table
     */
    protected $table;
    
    /**
     * The class name of the custom pivot model to use for the relationship.
     * 
     * @var string $using
     */
    protected $using;
    
    /**
     * Constructor. Create a new has many relationship instance.
     * 
     * @param  \Syscodes\Components\Database\Erostrine\Builder  $query
     * @param  \Syscodes\Components\Database\Erostrine\Model  $parent
     * @param  string  $table
     * @param  string  $foreignKey
     * @param  string  $ownerKey
     * @param  string|null  $relationName
     * 
     * @return void
     */
    public function __construct(
        Builder $query, 
        Model $parent, 
        $table, 
        $foreignKey, 
        $ownerKey, 
        $relationName = null
    ) {
        $this->table = $table;
        $this->ownerKey = $ownerKey;
        $this->foreignKey = $foreignKey;
        $this->relationName = $relationName;
        
        parent::__construct($query, $parent);
    }

    /**
     * {@inheritdoc}
     */
    public function getResults()
    {
        return $this->get();
    }
    
    /**
     * Get all of the model results.
     * 
     * @param  array  $columns
     * 
     * @return \Syscodes\Components\Database\Erostrine\Collection
     */
    public function get($columns = ['*'])
    {
        $builder = $this->query->applyScopes();

        $columns = $builder->getQuery()->columns ? array() : $columns;

        $models = $builder->addSelect(
            $this->getSelectColumns($columns)
        )->getModels();
        
        $this->hydratePivotRelation($models);
        
        if (count($models) > 0) {
            $models = $builder->eagerLoadRelations($models);
        }
        
        return $this->related->newCollection($models);
    }
    
    /**
     * Set the select clause for the relation query.
     * 
     * @param  array  $columns
     * 
     * @return array
     */
    protected function getSelectColumns(array $columns = array('*')): array
    {
        if ($columns == ['*']) {
            $columns = [$this->related->getTable().'.*'];
        }
        
        return array_merge($columns, $this->getAliasedPivotColumns());
    }
    
    /**
     * Get the pivot columns for the relation.
     * 
     * @return array
     */
    protected function getAliasedPivotColumns()
    {
        $defaults = [$this->foreignKey, $this->ownerKey];
        
        return collect(array_merge($defaults, $this->pivotColumns))->map(function ($column) {
            return $this->qualifyPivotColumn($column).' as pivot_'.$column;
        })->unique()->all();
    }
    
    /**
     * Hydrate the pivot table relationship on the models.
     * 
     * @param  array  $models
     * 
     * @return void
     */
    protected function hydratePivotRelation(array $models): void
    {
        foreach ($models as $model) {
            $model->setRelation($this->accessor, $this->newExistingPivot(
                $this->migratePivotAttributes($model)
            ));
        }
    }
    
    /**
     * Get the pivot attributes from a model.
     * 
     * @param  \Syscodes\Components\Database\Erostrine\Model  $model
     * 
     * @return array
     */
    protected function migratePivotAttributes(Model $model): array
    {
        $values = [];
        
        foreach ($model->getAttributes() as $key => $value) {
            if (Str::startsWith($key, 'pivot_')) {
                $values[substr($key, 6)] = $value;
                
                unset($model->$key);
            }
        }
        
        return $values;
    }

    /**
     * Specify the custom Pivot Model to use for the relationship.
     * 
     * @param  string  $classname
     * 
     * @return self
     */
    public function using($classname): self
    {
        $this->using = $classname;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addConstraints(): void
    {

    }

    /**
     * {@inheritdoc}
     */
    public function addEagerConstraints(array $models): void
    {

    }

    /**
     * {@inheritdoc}
     */
    public function initRelation(array $models, $relation): array
    {
        return $models;
    }

    /**
     * {@inheritdoc}
     */
    public function match(array $models, Collection $results, $relation): array
    {
        return $models;
    }
    
    /**
     * Qualify the given column name by the pivot table.
     * 
     * @param  string  $column
     * 
     * @return string
     */
    public function qualifyPivotColumn($column)
    {
        return Str::contains($column, '.')
                    ? $column
                    : $this->table.'.'.$column;
    }
}