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

namespace Syscodes\Components\Database\Erostrine\Relations;

use Closure;
use InvalidArgumentException;
use Syscodes\Components\Contracts\Support\Arrayable;
use Syscodes\Components\Database\Erostrine\Builder;
use Syscodes\Components\Database\Erostrine\Collection;
use Syscodes\Components\Database\Erostrine\Exceptions\ModelNotFoundException;
use Syscodes\Components\Database\Erostrine\Model;
use Syscodes\Components\Database\Erostrine\Relations\Concerns\InteractsWithPivotTable;
use Syscodes\Components\Support\Str;

/**
 * Relation belongToMany given on the parent model.
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
     * The default values for the pivot columns.
     * 
     * @var array $pivotValues
     */
    protected $pivotValues = [];
    
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
     * Any pivot table restrictions for whereNull clauses.
     * 
     * @var array $pivotWhereNulls
     */
    protected $pivotWhereNulls = [];
    
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
     * Resolve the table name from the given string.
     * 
     * @param  string  $table
     * 
     * @return string
     */
    protected function resolveTable($table): string
    {
        if ( ! Str::contains($table, '\\') || ! class_exists($table)) {
            return $table;
        }

        $model = new $table;

        if ( ! $model instanceof Model) {
            return $table;
        }

        if ($model instanceof Pivot) {
            $this->using($table);
        }

        return $model->getTable();
    }

    /**
     * Get the results of the relationship.
     * 
     * @return mixed
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
    protected function getAliasedPivotColumns(): array
    {
        $defaults = [$this->foreignPivotKey, $this->relatedKey];
        
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
     * Set the base constraints on the relation query.
     * 
     * @return void
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
     * @return static
     */
    protected function setJoin($query = null): static
    {
        $query = $query ?: $this->getRelationQuery();
        
        $query->join($this->table, $this->getQualifiedRelatedKeyName(), '=', $this->getQualifiedRelatedPivotKeyName());
        
        return $this;
    }
    
    /**
     * Set the where clause for the relation query.
     * 
     * @return static
     */
    protected function setWhereConstraints(): static
    {
        $this->getRelationQuery()->where(
            $this->getQualifiedForeignPivotKeyName(), '=', $this->parent->{$this->parentKey}
        );
        
        return $this;
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
        $this->getRelationQuery()->whereIn(
            $this->getQualifiedForeignPivotKeyName(),
            $this->getKeys($models, $this->parentKey)
        );
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
            $model->setRelation($relation, $this->related->newCollection());
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
     * Set a where clause for a pivot table column.
     * 
     * @param  string  $column
     * @param  mixed  $operator
     * @param  mixed  $value
     * @param  mixed  $boolean
     * 
     * @return static
     */
    public function wherePivot($column, $operator = null, $value = null, $boolean = 'and'): static
    {
        $this->pivotWheres[] = func_get_args();

        return $this->where($this->qualifyPivotColumn($column), $operator, $value, $boolean);
    }

    /**
     * Set a "or where" clause for a pivot table column.
     * 
     * @param  string  $column
     * @param  mixed  $operator
     * @param  mixed  $value
     * 
     * @return static
     */
    public function orWherePivot($column, $operator = null, $value = null): static
    {
        return $this->wherePivot($column, $operator, $value, 'or');
    }

    /**
     * Set a "where between" clause for a pivot table column.
     * 
     * @param  string  $column
     * @param  array  $values
     * @param  string  $boolean
     * @param  bool  $negative
     * 
     * @return static
     */
    public function wherePivotBetween($column, array $values, $boolean = 'and', $negative = false): static
    {
        return $this->whereBetween($this->qualifyPivotColumn($column), $values, $boolean, $negative);
    }

    /**
     * Set a "or where between" clause for a pivot table column.
     * 
     * @param  string  $column
     * @param  array  $values
     * 
     * @return static
     */
    public function orWherePivotBetween($column, array $values): static
    {
        return $this->wherePivotBetween($column, $values, 'or');
    }

    /**
     * Set a "where pivot not between" clause for a pivot table column.
     * 
     * @param  string  $column
     * @param  array  $values
     * @param  string  $boolean
     * 
     * @return static
     */
    public function wherePivotNotBetween($column, array $values, $boolean = 'and'): static
    {
        return $this->wherePivotBetween($column, $values, $boolean, true);
    }

    /**
     * Set a "or where not between" clause for a pivot table column.
     * 
     * @param  string  $column
     * @param  array  $values
     * 
     * @return static
     */
    public function orWherePivotNotBetween($column, array $values): static
    {
        return $this->wherePivotBetween($column, $values, 'or', true);
    }

    /**
     * Set a "where in" clause for a pivot table column.
     * 
     * @param  string  $column
     * @param  mixed  $values
     * @param  string boolean
     * @param  bool  $negative
     * 
     * @return static
     */
    public function wherePivotIn($column, array $values, $boolean = 'and', $negative = false): static
    {
        $this->pivotWhereIns[] = func_get_args();

        return $this->whereIn($this->qualifyPivotColumn($column), $values, $boolean, $negative);
    }

    /**
     * Set an "or where in" clause for a pivot table column.
     * 
     * @param  string  $column
     * @param  mixed  $values
     * 
     * @return static
     */
    public function orWherePivotIn($column, array $values): static
    {
        return $this->wherePivotIn($column, $values, 'or');
    }

    /**
     * Set a "where not in" clause for a pivot table column.
     * 
     * @param  string  $column
     * @param  mixed  $values
     * @param  string  $boolean
     * 
     * @return static
     */
    public function wherePivotNotIn($column, $values, $boolean = 'and'): static
    {
        return $this->wherePivotIn($column, $values, $boolean, true);
    }

    /**
     * Set an "or where not in" clause for a pivot table column.
     * 
     * @param  string  $column
     * @param  mixed  $values
     * 
     * @return static
     */
    public function orWherePivotNotIn($column, $values): static
    {
        return $this->wherePivotNotIn($column, $values, 'or');
    }

    /**
     * Set a "where null" clause for a pivot table column.
     * 
     * @param  string  $column
     * @param  string  $boolean
     * @param  bool  $negative
     * 
     * @return static
     */
    public function wherePivotNull($column, $boolean = 'and', $negative =  false): static
    {
        $this->pivotWhereNulls[] = func_get_args();

        return $this->whereNull($this->qualifyPivotColumn($column), $boolean, $negative);
    }

    /**
     * Set a "where not null" clause for a pivot table column.
     * 
     * @param  string  $column
     * @param  string  $boolean
     * 
     * @return static
     */
    public function wherePivotNotNull($column, $boolean = 'and'): static
    {
        return $this->wherePivotNull($column, $boolean, true);
    }

    /**
     * Set a "or where null" clause for a pivot table column.
     * 
     * @param  string  $column
     * @param  bool  $negative
     * 
     * @return static
     */
    public function orWherePivotNull($column, $negative = false): static
    {
        return $this->wherePivotNull($column, 'or', $negative);
    }

    /**
     * Set a "or where not null" clause for a pivot table column.
     * 
     * @param  string  $column
     * 
     * @return static
     */
    public function orWherePivotNotNull($column): static
    {
        return $this->orWherePivotNull($column, true);
    }

    /**
     * Add an "order by" clause for a pivot table column.
     * 
     * @param  string  $column
     * @param  string  $direction
     * 
     * @return static
     */
    public function orderByPivot($column, $direction = 'asc'): static
    {
        return $this->orderBy($column, $direction);
    }

    /**
     * Find a related model by its primary key or return a new instance of the related model.
     *
     * @param  mixed  $id
     * @param  array  $columns
     * @return \Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Model
     */
    public function findOrNew($id, $columns = ['*'])
    {
        if (is_null($instance = $this->find($id, $columns))) {
            $instance = $this->related->newInstance();
        }

        return $instance;
    }

    /**
     * Get the first related model record matching the attributes or instantiate it.
     *
     * @param  array  $attributes
     * @param  array  $values
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function firstOrNew(array $attributes = [], array $values = [])
    {
        if (is_null($instance = $this->related->where($attributes)->first())) {
            $instance = $this->related->newInstance(array_merge($attributes, $values));
        }

        return $instance;
    }

    /**
     * Get the first related record matching the attributes or create it.
     *
     * @param  array  $attributes
     * @param  array  $values
     * @param  array  $joining
     * @param  bool  $touch
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function firstOrCreate(array $attributes = [], array $values = [], array $joining = [], $touch = true)
    {
        if (is_null($instance = $this->related->where($attributes)->first())) {
            $instance = $this->create(array_merge($attributes, $values), $joining, $touch);
        }

        return $instance;
    }

    /**
     * Create or update a related record matching the attributes, and fill it with values.
     *
     * @param  array  $attributes
     * @param  array  $values
     * @param  array  $joining
     * @param  bool  $touch
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function updateOrCreate(array $attributes, array $values = [], array $joining = [], $touch = true)
    {
        if (is_null($instance = $this->related->where($attributes)->first())) {
            return $this->create(array_merge($attributes, $values), $joining, $touch);
        }

        $instance->fill($values);

        $instance->save(['touch' => false]);

        return $instance;
    }
    
    /**
     * Find a related model by its primary key.
     * 
     * @param  mixed  $id
     * @param  array  $columns
     * 
     * @return \Syscodes\components\Database\Erostrine\Model|\Syscodes\components\Database\Erostrine\Collection|null
     */
    public function find($id, $columns = ['*'])
    {
        if ( ! $id instanceof Model && (is_array($id) || $id instanceof Arrayable)) {
            return $this->findMany($id, $columns);
        }
        
        return $this->where(
            $this->getRelated()->getQualifiedKeyName(), '=', $this->parseWithIds($id)
        )->first($columns);
    }
    
    /**
     * Find multiple related models by their primary keys.
     * 
     * @param  \Syscodes\Components\Contracts\Support\Arrayable|array  $ids
     * @param  array  $columns
     * 
     * @return \Syscodes\Components\Database\Eloquent\Collection
     */
    public function findMany($ids, $columns = ['*'])
    {
        $ids = $ids instanceof Arrayable ? $ids->toArray() : $ids;
        
        if (empty($ids)) {
            return $this->getRelated()->newCollection();
        }
        
        return $this->whereIn(
            $this->getRelated()->getQualifiedKeyName(), $this->parseWithIds($ids)
        )->get($columns);
    }
    
    /**
     * Find a related model by its primary key or throw an exception.
     * 
     * @param  mixed  $id
     * @param  array  $columns
     * 
     * @return \Syscodes\Database\Erostrine\Model|\Syscodes\Database\Erostrine\Collection
     * 
     * @throws \Syscodes\Database\Erostrine\ModelNotFoundException<\Syscodes\Database\Erostrine\Model>
     */
    public function findOrFail($id, $columns = ['*'])
    {
        $result = $this->find($id, $columns);
        
        $id = $id instanceof Arrayable ? $id->toArray() : $id;
        
        if (is_array($id)) {
            if (count($result) === count(array_unique($id))) {
                return $result;
            }
        } elseif (! is_null($result)) {
            return $result;
        }
        
        throw (new ModelNotFoundException)->setModel(get_class($this->related));
    }
    
    /**
     * Add a basic where clause to the query, and return the first result.
     * 
     * @param  \Closure|string|array  $column
     * @param  mixed  $operator
     * @param  mixed  $value
     * @param  string  $boolean
     * 
     * @return \Syscodes\Components\Database\Erostrine\Model|static
     */
    public function firstWhere($column, $operator = null, $value = null, $boolean = 'and')
    {
        return $this->where($column, $operator, $value, $boolean)->first();
    }
    
    /**
     * Execute the query and get the first result.
     * 
     * @param  array  $columns
     * 
     * @return mixed
     */
    public function first($columns = ['*'])
    {
        $results = $this->limit(1)->get($columns);
        
        return count($results) > 0 ? $results->first() : null;
    }

    /**
     * Execute the query and get the first result or throw an exception.
     * 
     * @param  array  $columns
     * @return \Syscodes\Components\Database\Erostrine\Model|static
     * 
     * @throws \Syscodes\Components\Database\Erostrine\ModelNotFoundException<\Syscodes\Components\Database\Erostrine\Model>
     */
    public function firstOrFail($columns = ['*'])
    {
        if ( ! is_null($model = $this->first($columns))) {
            return $model;
        }
        
        throw (new ModelNotFoundException)->setModel(get_class($this->related));
    }
    
    /**
     * Execute the query and get the first result or call a callback.
     * 
     * @param  \Closure|array  $columns
     * @param  \Closure|null  $callback
     *
     * @return \Syscodes\Components\Database\Eloquent\Model|static|mixed
     */
    public function firstOr($columns = ['*'], ?Closure $callback = null)
    {
        if ($columns instanceof Closure) {
            $callback = $columns;
            
            $columns = ['*'];
        }
        
        if ( ! is_null($model = $this->first($columns))) {
            return $model;
        }
        
        return $callback();
    }

    /**
     * Set a where clause for a pivot table column.
     * 
     * @param  string|array  $column
     * @param  mixed  $value
     * 
     * @return static
     * 
     * @throws \InvalidArgumentException
     */
    public function withPivotValue($column, $value = null): static
    {
        if (is_array($column)) {
            foreach ($column as $name => $value) {
                $this->withPivoteValue($name, $value);
            }

            return $this;
        }

        if (is_null($value)) {
            throw new InvalidArgumentException('The provided value may not be null');
        }

        $this->pivotValues[] = compact('column', 'value');

        return $this->wherePivot($column, '=', $value);
    }

    /**
     * Specify the custom Pivot Model to use for the relationship.
     * 
     * @param  string  $classname
     * 
     * @return static
     */
    public function using($classname): static
    {
        $this->using = $classname;

        return $this;
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