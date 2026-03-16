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
use Syscodes\Components\Contracts\Support\Arrayable;
use Syscodes\Components\Database\Erostrine\Builder;
use Syscodes\Components\Database\Erostrine\Collection as ErostrineCollection;
use Syscodes\Components\Database\Erostrine\Exceptions\ModelNotFoundException;
use Syscodes\Components\Database\Erostrine\Model;
use Syscodes\Components\Database\Erostrine\Relations\Concerns\InteractsWithDictionary;
use Syscodes\Components\Support\Arr;

/**
 * Relation HasOneOrManyThrough given on the parent model.
 */
abstract class HasOneOrManyThrough extends Relation
{
   use InteractsWithDictionary;
   
    /**
     * The far parent model instance.
     *
     * @var \Syscodes\Components\Database\Erostrine\Model
     */
    protected $farParent;

    /**
     * The near key on the relationship.
     *
     * @var string
     */
    protected $firstKey;

    /**
     * The local key on the relationship.
     *
     * @var string
     */
    protected $localKey;

    /**
     * The far key on the relationship.
     *
     * @var string
     */
    protected $secondKey;

    /**
     * The local key on the intermediary model.
     *
     * @var string
     */
    protected $secondLocalKey;

    /**
     * The "through" parent model instance.
     *
     * @var \Syscodes\Components\Database\Erostrine\Model
     */
    protected $throughParent;

    /**
     * Create a new has many through relationship instance.
     *
     * @param  \Syscodes\Components\Database\Erostrine\Builder  $builder
     * @param  \Syscodes\Components\Database\Erostrine\Model  $farParent
     * @param  \Syscodes\Components\Database\Erostrine\Model  $throughParent
     * @param  string  $firstKey
     * @param  string  $secondKey
     * @param  string  $localKey
     * @param  string  $secondLocalKey
     * 
     * @return void
     */
    public function __construct(Builder $builder, Model $farParent, Model $throughParent, $firstKey, $secondKey, $localKey, $secondLocalKey)
    {
        $this->localKey = $localKey;
        $this->firstKey = $firstKey;
        $this->secondKey = $secondKey;
        $this->farParent = $farParent;
        $this->throughParent = $throughParent;
        $this->secondLocalKey = $secondLocalKey;

        parent::__construct($builder, $throughParent);
    }

    /**
     * Set the base constraints on the relation query.
     *
     * @return void
     */
    public function addConstraints(): void
    {
        $query = $this->getRelationQuery();

        $this->performJoin($query);

        if (static::$constraints) {
            $localValue = $this->farParent[$this->localKey];

            $query->where($this->getQualifiedFirstKeyName(), '=', $localValue);
        }
    }

    /** @inheritDoc */
    public function addEagerConstraints(array $models): void
    {
        $whereIn = $this->whereInMethod($this->farParent, $this->localKey);

        $this->whereInEager(
            $whereIn,
            $this->getQualifiedFirstKeyName(),
            $this->getKeys($models, $this->localKey),
            $this->getRelationQuery(),
        );
    }

    /**
     * Set the join clause on the query.
     *
     * @param  \Syscodes\Components\Database\Erostrine\Builder|null  $builder
     * 
     * @return void
     */
    protected function performJoin(?Builder $builder = null): void
    {
        $builder ??= $this->query;

        $farKey = $this->getQualifiedFarKeyName();

        $builder->join($this->throughParent->getTable(), $this->getQualifiedParentKeyName(), '=', $farKey);
    }

     /**
     * Build model dictionary keyed by the relation's foreign key.
     *
     * @param  \Syscodes\Components\Database\Erostrine\Collection  $results
     * 
     * @return array
     */
    protected function buildDictionary(ErostrineCollection $results): array
    {
        $dictionary = [];

        $isAssociative = Arr::isAssoc($results->all());

        // First we will create a dictionary of models keyed by the foreign key of the
        // relationship as this will allow us to quickly access all of the related
        // models without having to do nested looping which will be quite slow.
        foreach ($results as $key => $result) {
            if ($isAssociative) {
                $dictionary[$result->lenevor_through_key][$key] = $result;
            } else {
                $dictionary[$result->lenevor_through_key][] = $result;
            }
        }

        return $dictionary;
    }

    /**
     * Execute the query and get the first related model.
     *
     * @param  array  $columns
     * 
     * @return \Syscodes\Components\Database\Erostrine\Model|null
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
     * 
     * @return \Syscodes\Components\Database\Erostrine\Model
     *
     * @throws \Syscodes\Components\Database\Erostrine\ModelNotFoundException
     */
    public function firstOrFail($columns = ['*'])
    {
        if (! is_null($model = $this->first($columns))) {
            return $model;
        }

        throw (new ModelNotFoundException)->setModel(get_class($this->related));
    }

    /**
     * Execute the query and get the first result or call a callback.
     *
     * @template TValue
     *
     * @param  aray|string  $columns
     * @param  \Closure|null  $callback
     * 
     * @return \Closure
     */
    public function firstOr($columns = ['*'], ?Closure $callback = null)
    {
        if ($columns instanceof Closure) {
            $callback = $columns;

            $columns = ['*'];
        }

        if (! is_null($model = $this->first($columns))) {
            return $model;
        }

        return $callback();
    }

    /**
     * Find a related model by its primary key.
     *
     * @param  mixed  $id
     * @param  array  $columns
     * 
     * @return void
     */
    public function find($id, $columns = ['*'])
    {
        if (is_array($id) || $id instanceof Arrayable) {
            return $this->findMany($id, $columns);
        }

        return $this->where(
            $this->getRelated()->getQualifiedKeyName(), '=', $id
        )->first($columns);
    }

    /**
     * Find multiple related models by their primary keys.
     *
     * @param  \Syscodes\Components\Contracts\Support\Arrayable|array  $ids
     * @param  array  $columns
     * 
     * @return \Syscodes\Components\Database\Erostrine\Collection
     */
    public function findMany($ids, $columns = ['*'])
    {
        $ids = $ids instanceof Arrayable ? $ids->toArray() : $ids;

        if (empty($ids)) {
            return $this->getRelated()->newCollection();
        }

        return $this->whereIn(
            $this->getRelated()->getQualifiedKeyName(), $ids
        )->get($columns);
    }

    /**
     * Find a related model by its primary key or throw an exception.
     *
     * @param  mixed  $id
     * @param  array  $columns
     * 
     * @return \Syscodes\Components\Contracts\Support\Arrayable|null
     *
     * @throws \Syscodes\Components\Database\Erostrine\ModelNotFoundException
     */
    public function findOrFail($id, $columns = ['*'])
    {
        $result = $this->find($id, $columns);

        $id = $id instanceof Arrayable ? $id->toArray() : $id;

        if (is_array($id)) {
            if (count($result) === count(array_unique($id))) {
                return $result;
            }
        } elseif ( ! is_null($result)) {
            return $result;
        }

        throw (new ModelNotFoundException)->setModel(get_class($this->related), $id);
    }

    /**
     * Find a related model by its primary key or call a callback.
     *
     * @template TValue
     *
     * @param  mixed  $id
     * @param  array|string  $columns
     * @param  \Closure|null  $callback
     * 
     * @return callable
     */
    public function findOr($id, $columns = ['*'], ?Closure $callback = null)
    {
        if ($columns instanceof Closure) {
            $callback = $columns;

            $columns = ['*'];
        }

        $result = $this->find($id, $columns);

        $id = $id instanceof Arrayable ? $id->toArray() : $id;

        if (is_array($id)) {
            if (count($result) === count(array_unique($id))) {
                return $result;
            }
        } elseif (! is_null($result)) {
            return $result;
        }

        return $callback();
    }

    /** @inheritDoc */
    public function get($columns = ['*'])
    {
        $builder = $this->prepareQueryBuilder($columns);

        $models = $builder->getModels();

        // If we actually found models we will also eager load any relationships that
        // have been specified as needing to be eager loaded. This will solve the
        // n + 1 query problem for the developer and also increase performance.
        if (count($models) > 0) {
            $models = $builder->eagerLoadRelations($models);
        }

        return $this->query->applyAfterQueryCallbacks(
            $this->related->newCollection($models)
        );
    }

    /**
     * Get a paginator for the "select" statement.
     *
     * @param  int|null  $perPage
     * @param  array  $columns
     * @param  string  $pageName
     * @param  int|null  $page
     * 
     * @return \Syscodes\Components\Pagination\LengthAwarePaginator
     */
    public function paginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
    {
        $this->query->addSelect($this->shouldSelect($columns));

        return $this->query->paginate($perPage, $columns, $pageName, $page);
    }

    /**
     * Paginate the given query into a simple paginator.
     *
     * @param  int|null  $perPage
     * @param  array  $columns
     * @param  string  $pageName
     * @param  int|null  $page
     * 
     * @return \Syscodes\Components\Contracts\Pagination\Paginator
     */
    public function simplePaginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
    {
        $this->query->addSelect($this->shouldSelect($columns));

        return $this->query->simplePaginate($perPage, $columns, $pageName, $page);
    }

    /**
     * Set the select clause for the relation query.
     *
     * @param  array  $columns
     * 
     * @return array
     */
    protected function shouldSelect(array $columns = ['*']): array
    {
        if ($columns == ['*']) {
            $columns = [$this->related->qualifyColumn('*')];
        }

        return array_merge($columns, [$this->getQualifiedFirstKeyName().' as laravel_through_key']);
    }

    /**
     * Prepare the query builder for query execution.
     *
     * @param  array  $columns
     * 
     * @return \Syscodes\Components\Database\Erostrine\Builder
     */
    protected function prepareQueryBuilder($columns = ['*'])
    {
        $builder = $this->query->applyScopes();

        return $builder->addSelect(
            $this->shouldSelect($builder->getQuery()->columns ? [] : $columns)
        );
    }

    /**
     * Set the "limit" value of the query.
     *
     * @param  int  $value
     * 
     * @return static
     */
    public function limit($value): static
    {
        if ($this->farParent->exists) {
            $this->query->limit($value);
        } 

        return $this;
    }

    /**
     * Get the qualified foreign key on the related model.
     *
     * @return string
     */
    public function getQualifiedFarKeyName(): string
    {
        return $this->getQualifiedForeignKeyName();
    }

    /**
     * Get the foreign key on the "through" model.
     *
     * @return string
     */
    public function getFirstKeyName(): string
    {
        return $this->firstKey;
    }

    /**
     * Get the qualified foreign key on the "through" model.
     *
     * @return string
     */
    public function getQualifiedFirstKeyName(): string
    {
        return $this->throughParent->qualifyColumn($this->firstKey);
    }

    /**
     * Get the foreign key on the related model.
     *
     * @return string
     */
    public function getForeignKeyName(): string
    {
        return $this->secondKey;
    }

    /**
     * Get the qualified foreign key on the related model.
     *
     * @return string
     */
    public function getQualifiedForeignKeyName(): string
    {
        return $this->related->qualifyColumn($this->secondKey);
    }

    /**
     * Get the local key on the far parent model.
     *
     * @return string
     */
    public function getLocalKeyName(): string
    {
        return $this->localKey;
    }

    /**
     * Get the qualified local key on the far parent model.
     *
     * @return string
     */
    public function getQualifiedLocalKeyName(): string
    {
        return $this->farParent->qualifyColumn($this->localKey);
    }

    /**
     * Get the local key on the intermediary model.
     *
     * @return string
     */
    public function getSecondLocalKeyName(): string
    {
        return $this->secondLocalKey;
    }
}