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
use Syscodes\Components\Database\Erostrine\Builder;
use Syscodes\Components\Database\Erostrine\Collection;
use Syscodes\Components\Database\Erostrine\Exceptions\ModelNotFoundException;
use Syscodes\Components\Database\Erostrine\Model;
use Syscodes\Components\Database\Exceptions\MultipleRecordsFoundException;
use Syscodes\Components\Support\Traits\ForwardsCalls;
use Syscodes\Components\Support\Traits\Macroable;

/**
 * This class allows the relations between tables.
 */
abstract class Relation
{
    use ForwardsCalls,
        Macroable {
            __call as macroCall;   
        }
    
    /**
     * Indicates whether the eagerly loaded relation should implicitly return an empty collection.
     *
     * @var bool
     */
    protected $eagerKeysWereEmpty = false;

    /**
     * The parent model instance.
     * 
     * @var \Syscodes\Components\Database\Erostrine\Model $parent
     */
    protected $parent;

    /**
     * The Erostrine query builder instance.
     * 
     * @var \Syscodes\Components\Database\Erostrine\builder $query
     */
    protected $query;

    /**
     * The related model instance.
     * 
     * @var \Syscodes\Components\Database\Erostrine\Model $related
     */
    protected $related;

    /**
     * Indicates if the relation is adding constraints.
     * 
     * @var bool $constraints
     */
    protected static $constraints = true;

    /**
     * Constructor. Create a new Relation instance.
     * 
     * @param  \Syscodes\Components\Database\Erostrine\Builder  $builder
     * @param  \Syscodes\Components\Database\Erostrine\Model  $parent
     * 
     * @return void
     */
    public function __construct(Builder $builder, Model $parent)
    {
        $this->query    = $builder;
        $this->parent   = $parent;
        $this->related  = $builder->getModel();

        $this->addConstraints();
    }

    /**
     * Run a callback with constraints disabled on the relation.
     * 
     * @param  \Closure  $callback
     * 
     * @return mixed
     */
    public static function noConstraints(Closure $callback)
    {
        static::$constraints = false;

        try {
            return $callback();
        } finally {
            static::$constraints = true;
        }
    }

    /**
     * Set the base constraints on the relation query.
     * 
     * @return void
     */
    abstract public function addConstraints(): void;

    /**
     * Set the constraints for an eager load of the relation.
     * 
     * @param  array  $models
     * 
     * @return void
     */
    abstract public function addEagerConstraints(array $models): void;

    /**
     * Initialize the relation on a set of models.
     * 
     * @param  array  $models
     * @param  string  $relation
     * 
     * @return array
     */
    abstract public function initRelation(array $models, $relation): array;

    /**
     * Match the eagerly loaded results to their parents.
     * 
     * @param  array  $models
     * @param  \Syscodes\Components\Database\Erostrine\Collection  $results
     * @param  string  $relation
     * 
     * @return array
     */
    abstract public function match(array $models, Collection $results, $relation): array;

    /**
     * Get the results of the relationship.
     * 
     * @return mixed
     */
    abstract public function getResults();

    /**
     * Add a whereIn eager constraint for the given set of model keys to be loaded.
     *
     * @param  string  $whereIn
     * @param  string  $key
     * @param  array  $modelKeys
     * @param  \Syscodes\Components\Database\Erostrine\Builder<|null  $builder
     * 
     * @return void
     */
    protected function whereInEager(string $whereIn, string $key, array $modelKeys, ?Builder $builder = null): void
    {
        ($builder ?? $this->query)->{$whereIn}($key, $modelKeys);

        if ($modelKeys === []) {
            $this->eagerKeysWereEmpty = true;
        }
    }

    /**
     * Get the name of the "where in" method for eager loading.
     *
     * @param  \Syscodes\Components\Database\Erostrine\Model  $model
     * @param  string  $key
     * 
     * @return string
     */
    protected function whereInMethod(Model $model, $key): string
    {
        return $model->getKeyName() === last(explode('.', $key))
            && in_array($model->getKeyType(), ['int', 'integer'])
                ? 'whereIntegerInRaw'
                : 'whereIn';
    }

    /**
     * Get the relationship for eager loading.
     * 
     * @return \Syscodes\Components\Database\Erostrine\Collection
     */
    public function getEager()
    {
        return $this->get();
    }

    /**
     * Execute the query and get the first result if it's the sole matching record.
     *
     * @param  array|string  $columns
     * 
     * @return \Syscodes\Components\Database\Erostrine\Model
     *
     * @throws \Syscodes\Components\Database\Erostrine\Exceptions\ModelNotFoundException
     * @throws \Syscodes\Components\Database\Exceptions\MultipleRecordsFoundException
     */
    public function sole($columns = ['*'])
    {
        $result = $this->limit(2)->get($columns);

        $count = $result->count();

        if ($count === 0) {
            throw (new ModelNotFoundException)->setModel(get_class($this->related));
        }

        if ($count > 1) {
            throw new MultipleRecordsFoundException($count);
        }

        return $result->first();
    }

    /**
     * Get all of the model results.
     * 
     * @param  array  $columns
     * 
     * @return array
     */
    public function get($columns = ['*'])
    {
        return $this->query->get($columns);
    }

    /**
     * Get all of the primary key for an array of models.
     * 
     * @param  array  $models
     * @param  string|null  $key
     * 
     * @return array
     */
    protected function getKeys(array $models, $key = null): array
    {
        return (new Collection($models))
            ->map(fn ($value) => $key ? $value->getAttribute($key) : $value->getKey())
            ->values()
            ->unique(null, true)
            ->sort()
            ->all();
    }

    /**
     * Get the query builder that will contain the relationship constraints.
     * 
     * @return \Syscodes\Components\Database\Erostrine\Builder
     */
    protected function getRelationQuery()
    {
        return $this->query;
    }

    /**
     * Get the underlying query for the relation.
     * 
     * @return \Syscodes\Components\Database\Erostrine\Builder
     */
    protected function getQuery()
    {
        return $this->query;
    }

    /**
     * Get the base query builder driving the Erostrine builder.
     * 
     * @return \Syscodes\Components\Database\Erostrine\Builder
     */
    public function getBaseQuery()
    {
        return $this->query->getQuery();
    }

     /**
     * Get a base query builder instance.
     *
     * @return \Syscodes\Components\Database\Query\Builder
     */
    public function toBase()
    {
        return $this->query->toBase();
    }

    /**
     * Get the parent model of the relation.
     * 
     * @return \Syscodes\Components\Database\Erostrine\Model
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Get the qualified parent key name.
     * 
     * @return string
     */
    public function getQualifiedParentKeyName(): string
    {
        return $this->parent->getQualifiedKeyName();
    }

    /**
     * Get the related model of the relation.
     * 
     * @return \Syscodes\Components\Database\Erostrine\Model
     */
    public function getRelated()
    {
        return $this->related;
    }

    /**
     * Magic method.
     * 
     * Handle dynamic method calls to the relationship.
     * 
     * @param  string  $method
     * @param  array  $parameters
     * 
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $parameters);
        }

        return $this->forwardObjectCallTo($this->query, $method, $parameters);
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
        $this->query = clone $this->query;
    }
}