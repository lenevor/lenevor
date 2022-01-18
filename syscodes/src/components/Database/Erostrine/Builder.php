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

namespace Syscodes\Components\Database\Erostrine;

use Closure;
use Exception;
use ReflectionClass;
use ReflectionMethod;
use BadMethodCallException;
use Syscodes\Components\Support\Str;
use Syscodes\Components\Collections\Arr;
use Syscodes\Components\Contracts\Support\Arrayable;
use Syscodes\Components\Database\Concerns\MakeQueries;
use Syscodes\Components\Database\Query\Builder as QueryBuilder;
use Syscodes\Components\Database\Erostrine\Exceptions\ModelNotFoundException;

/**
 * Creates a Erostrine query builder.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class Builder
{
    use MakeQueries;
    
    /**
     * The relationships that should be eagerly loaded by the query.
     * 
     * @var array $eagerLoad
     */
    protected $eagerLoad = [];

    /**
     * The model being queried.
     * 
     * @var \Syscodes\Components\Database\Erostrine\Model $model
     */
    protected $model;

    /**
     * The methods that should be returned from query builder.
     * 
     * @var array $passthru
     */
    protected $passthru = [
        'aggregate',
        'average',
        'avg',
        'count',
        'exists',
        'getBindings',
        'getConnection',
        'getGrammar',
        'insert',
        'insertGetId',
        'insertOrIgnore',
        'insertUsing',
        'max',
        'min',
        'raw',
        'sum',
        'toSql',
    ];

    /**
     * The query builder instance.
     * 
     * @var \Syscodes\Components\Database\Query\Builder $querybuilder
     */
    protected $queryBuilder;

    /**
     * The relation tables to be instanced.
     * 
     * @var \Syscodes\Components\Database\Erostrine\Relations\Relation $relation
     */
    protected $relation;

    /**
     * Constructor. The new Holisen query builder instance.
     * 
     * @param  \Syscodes\Components\Database\query\Builder  $queryBuilder
     * 
     * @return void
     */
    public function __construct(QueryBuilder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }
    
    /**
     * Find a model by its primary key.
     * 
     * @param  mixed  $id
     * @param  array  $columns
     * 
     * @return \Syscodes\Components\Database\Erostrine\Model|static|null
     */
    public function find($id, array $columns = ['*'])
    {
        if (is_array($id) || $id instanceof Arrayable) {
            return $this->findMany($id, $columns);
        }

        return $this->whereClauseKey($id)->first($columns);
    }
    
    /**
     * Find a model by its primary key.
     * 
     * @param  array  $ids
     * @param  array  $columns
     * 
     * @return \Syscodes\Components\Database\Erostrine\Model|Collection|static
     */
    public function findMany($ids, array $columns = ['*'])
    {
        $ids = $ids instanceof Arrayable ? $ids->toArray() : $ids;

        if (empty($ids)) {
            return $this->model->newCollection();
        }

        return $this->whereClauseKey($ids)->get($columns);
    }

    /**
     * Add a where clause on the primary key to the query.
     * 
     * @param  mixed  $id
     * 
     * @return self
     */
    public function whereClauseKey($id)
    {
        if ($id instanceof Model) {
            $id = $id->getKey();
        }

        $keyName = $this->model->getQualifiedKeyName();

        if (is_array($id) || $id instanceof Arrayable) {
            $this->queryBuilder->whereIn($keyName, $id);

            return $this;
        }

        return $this->where($keyName, '=', $id);
    }

    /**
     * Add a basic where clause to the query.
     * 
     * @param  \Closure|string|array|\Syscodes\Component\Database\Query\Expression  $column
     * @param  mixed  $operator
     * @param  mixed  $value
     * @param  string  $boolean
     * 
     * @return self
     */
    public function where($column, $operator = null, $value = null, $boolean = 'and'): self
    {
       $this->queryBuilder->where(...func_get_args());

       return $this;
    }
    
    /**
     * Find a model by its primary key or throw an exception.
     * 
     * @param  mixed  $id
     * @param  array  $columns
     * 
     * @return \Syscodes\Components\Database\Erostrine\Model|\Syscodes\Components\Database\Erostrine\Collection|static|static[]
     * 
     * @throws \Syscodes\Components\Database\Erostrine\Exceptions\ModelNotFoundException
     */
    public function findOrFail($id, array $columns = ['*'])
    {
        $model = $this->find($id, $columns);

        $id = $id instanceof Arrayable ? $id->toArray() : $id;

        if (is_array($id)) {
            if (count($model) === count(array_unique($id))) {
                return $model;
            }
        } elseif ( ! is_null($model)) {
            return $model;
        }
        
        $className = get_class($this->model);
        
        throw (new ModelNotFoundException)->setModel($className);
    }

    /**
     * Execute the query and get the first result or throw an exception.
     *
     * @param  array  $columns
     * 
     * @return \Syscodes\Components\Database\Erostrine\Model|static
     *
     * @throws \Syscodes\Components\Database\Erostrine\Exceptions\ModelNotFoundException
     */
    public function firstOrFail($columns = ['*'])
    {
        if ( ! is_null($model = $this->first($columns))) {
            return $model;
        }

        $className = get_class($this->model);

        throw (new ModelNotFoundException)->setModel($className);
    }

    /**
     * Execute the query as a "select" statement.
     *
     * @param  array  $columns
     * 
     * @return \Syscodes\Components\Database\Erostrine\Collection|static[]
     */
    public function get($columns = ['*'])
    {
        $models = $this->getModels($columns);

        return $this->model->newCollection($models);
    }
    
    /**
     * Get the hydrated models.
     * 
     * @param  array  $columns
     * 
     * @return \Syscodes\Components\Database\Erostrine\Model[]
     */
    public function getModels($columns = ['*'])
    {
        $results = $this->queryBuilder->get($columns); 
        
        $connection = $this->model->getConnectionName();
        
        $models = [];
        
        foreach ($results as $result) {
            $models[] = $model = $this->model->newFromBuilder($result);
            
            $model->setConnection($connection);
        }
        
        return $models;
    }

    /**
     * Get the query builder instance.
     * 
     * @return \Syscodes\Components\Database\Query\Builder
     */
    public function getQuery()
    {
        return $this->queryBuilder;
    }

    /**
     * Set the query builder instance.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * 
     * @return self
     */
    public function setQuery($builder): self
    {
        $this->queryBuilder = $builder;

        return $this;
    }

    /**
     * Get the model instance.
     * 
     * @return \Syscodes\Components\Database\Erostrine\Model|static
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Set the model instance.
     * 
     * @param  \Syscodes\Components\Database\Erostrine\Model  $model
     * 
     * @return self
     */
    public function setModel(Model $model): self
    {
        $this->model = $model;

        $this->queryBuilder->from($model->getTable());

        return $this; 
    }
    
    /**
     * Create a new instance of the model being queried.
     * 
     * @param  array  $attributes
     * 
     * @return \Syscodes\Components\Database\Erostrine\Model|static
     */
    public function newModelInstance($attributes = [])
    {
        return $this->model->newInstance($attributes)->setConnection(
            $this->queryBuilder->getConnection()->getName()
        );
    }

    /**
     * Magic method.
     * 
     * Dynamically handle method calls into the Builder instance.
     * 
     * @param  string  $method
     * @param  array  $parameters
     * 
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        $result = $this->getQuery()->{$method}(...$parameters);

        return in_array($method, $this->passthru) ? $result : $this;
    }
    
    /**
     * Magic method.
     * 
     * Force a clone of the underlying query builder when cloning.
     * 
     * @return void
     */
    public function __clone()
    {
        $this->queryBuider = clone $this->queryBuilder;
    }
}