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
use Syscodes\Components\Database\Erostrine\Relations\Concerns\InteractsWithPivotTable;

/**
 * Relation belongToMany given on the parent model.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class BelongsToMany extends Relation
{
    use InteractsWithPivotTable;

    /**
     * The foreign key of the parent model.
     * 
     * @var string $foreignKey
     */
    protected $foreignPivotKey;
    
    /**
     * The key name of the parent model.
     * 
     * @var string $parentKey
     */
    protected $parentKey;
    
    /**
     * The pivot table columns to retrieve.
     * 
     * @var array $pivotColumns
     */
    protected $pivotColumns = [];
    
    /**
     * Any pivot table restrictions for where clauses.
     * 
     * @var array $pivotWheres
     */
    protected $pivotWheres = [];

    /**
     * Any pivot table restrictions for whereIn clauses.
     * 
     * @var array $pivotWhereIns
     */
    protected $pivotWhereIns = [];
    
    /**
     * The key name of the related model.
     * 
     * @var string $relatedKey
     */
    protected $relatedKey;

    /**
     * The associated key of the relation.
     * 
     * @var string $relatedKey
     */
    protected $relatedPivotKey;

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
     * @param  string  $foreignPivotKey
     * @param  string  $relatedPivotKey
     * @param  string  $parentKey
     * @param  string  $relatedKey
     * @param  string|null  $relationName
     * 
     * @return void
     */
    public function __construct(
        Builder $query, 
        Model $parent, 
        $table, 
        $foreignPivotKey, 
        $relatedPivotKey,
        $parentKey,
        $relatedKey,
        $relationName = null
    ) {
        $this->parentKey = $parentKey;
        $this->relatedKey = $relatedKey;
        $this->relationName = $relationName;
        $this->foreignPivotKey = $foreignPivotKey;
        $this->relatedPivotKey = $relatedPivotKey;
        $this->table = $this->resolveTable($table);
        
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
        $defaults = [$this->foreignKey, $this->relatedKey];
        
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
            $model->setRelation('pivot', $this->newExistingPivot(
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
        $this->setJoin();
        
        if (static::$constraints) {
            $this->setWhereConstraints();
        }
    }
    
    /**
     * Set the join clause for the relation query.
     * 
     * @param  \Syscodes\Components\Database\Erostrine\Builder|null $query
     * 
     * @return self
     */
    protected function setJoin($query = null): self
    {
        $query = $query ?: $this->getRelationQuery();
        
        $query->join($this->table, $this->getQualifiedRelatedKeyName(), '=', $this->getQualifiedRelatedPivotKeyName());
        
        return $this;
    }
    
    /**
     * Set the where clause for the relation query.
     * 
     * @return self
     */
    protected function setWhereConstraints(): self
    {
        $this->getRelationQuery()->where(
            $this->getQualifiedForeignPivotKeyName(), '=', $this->parent->{$this->parentKey}
        );
        
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addEagerConstraints(array $models): void
    {
        $this->getRelationQuery()->whereIn(
            $this->getQualifiedForeignPivotKeyName(),
            $this->getKeys($models, $this->parentKey)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function initRelation(array $models, $relation): array
    {
        foreach ($models as $model) {
            $model->setRelation($relation, $this->related->newCollection());
        }
        
        return $models;
    }

    /**
     * {@inheritdoc}
     */
    public function match(array $models, Collection $results, $relation): array
    {
        $dictionary = $this->buildDictionary($results);
        
        foreach ($models as $model) {
            if (isset($dictionary[$key = $model->{$this->parentKey}])) {
                $model->setRelation(
                    $relation, $this->related->newCollection($dictionary[$key])
                );
            }
        }
        
        return $models;
    }
    
    /**
     * Build model dictionary keyed by the relation's foreign key.
     * 
     * @param  \Syscodes\Components\Database\Erostrine\Collection  $results
     * 
     * @return array
     */
    protected function buildDictionary(Collection $results): array
    {
        $dictionary = [];
        
        foreach ($results as $result) {
            $dictionary[$result->pivot->{$this->foreignPivotKey}][] = $result;
        }
        
        return $dictionary;
    }
    
    /**
     * Get the foreign key for the relation.
     * 
     * @return string
     */
    public function getForeignPivotKeyName(): string
    {
        return $this->foreignPivotKey;
    }
    
    /**
     * Get the fully qualified foreign key for the relation.
     * 
     * @return string
     */
    public function getQualifiedForeignPivotKeyName(): string
    {
        return $this->qualifyPivotColumn($this->foreignPivotKey);
    }
    
    /**
     * Get the "related key" for the relation.
     * 
     * @return string
     */
    public function getRelatedPivotKeyName(): string
    {
        return $this->relatedPivotKey;
    }
    
    /**
     * Get the fully qualified "related key" for the relation.
     * 
     * @return string
     */
    public function getQualifiedRelatedPivotKeyName(): string
    {
        return $this->qualifyPivotColumn($this->relatedPivotKey);
    }
    
    /**
     * Get the parent key for the relationship.
     * 
     * @return string
     */
    public function getParentKeyName(): string
    {
        return $this->parentKey;
    }
    
    /**
     * Get the fully qualified parent key name for the relation.
     * 
     * @return string
     */
    public function getQualifiedParentKeyName(): string
    {
        return $this->parent->qualifyColumn($this->parentKey);
    }
    
    /**
     * Get the related key for the relationship.
     * 
     * @return string
     */
    public function getRelatedKeyName(): string
    {
        return $this->relatedKey;
    }
    
    /**
     * Get the fully qualified related key name for the relation.
     * 
     * @return string
     */
    public function getQualifiedRelatedKeyName(): string
    {
        return $this->related->qualifyColumn($this->relatedKey);
    }
    
    /**
     * Get the intermediate table for the relationship.
     * 
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }
    
    /**
     * Get the relationship name for the relationship.
     * 
     * @return string
     */
    public function getRelationName(): string
    {
        return $this->relationName;
    }

    /**
     * Get the pivot columns for this relationship.
     *
     * @return array
     */
    public function getPivotColumns(): array
    {
        return $this->pivotColumns;
    }

    /**
     * Qualify the given column name by the pivot table.
     * 
     * @param  string  $column
     * 
     * @return string
     */
    public function qualifyPivotColumn($column): string
    {
        return Str::contains($column, '.')
                    ? $column
                    : $this->table.'.'.$column;
    }
}