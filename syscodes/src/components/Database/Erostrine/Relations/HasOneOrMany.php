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

use Syscodes\Components\Database\Erostrine\Builder;
use Syscodes\Components\Database\Erostrine\Collection;
use Syscodes\Components\Database\Erostrine\Model;

/**
 * Relation HasOneOrMany given on the parent model.
 */
abstract class HasOneOrMany extends Relation
{
    /**
     * The foreign key of the parent model.
     * 
     * @var string $foreignKey
     */
    protected $foreignKey;

    /**
     * The local key of the parent model.
     * 
     * @var string $localKey
     */
    protected $localKey;

    /**
     * Constructor. Create a new HasOneOrMany instance.
     * 
     * @param  \Syscodes\Components\Database\Erostrine\Builder  $builder
     * @param  \Syscodes\Components\Database\Erostrine\Model  $parent
     * @param  string  $foreingnKey
     * @param  string  $localKey
     * 
     * @return void
     */
    public function __construct(
        Builder $builder,
        Model $parent,
        $foreignKey,
        $localKey
    ) {
        $this->foreignKey = $foreignKey;
        $this->localKey   = $localKey;
        
        parent::__construct($builder, $parent);
    }

    /**
     * Set the base constraints on the relation query.
     * 
     * @return void
     */
    public function addConstraints(): void
    {
        if (static::$constraints) {
            $query = $this->getRelationQuery();
            
            $query->where($this->foreignKey, '=', $this->getParentKey());
            
            $query->whereNotNull($this->foreignKey);
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
        $query = $this->getRelationQuery();

        $query->whereIn(
            $this->foreignKey, $this->getKeys($models, $this->localKey)
        );
    }
    
    /**
     * Match the eagerly loaded results to their single parents.
     * 
     * @param  array   $models
     * @param  \Syscodes\Components\Database\Erostrine\Collection  $results
     * @param  string  $relation
     * 
     * @return array
     */
    public function matchOne(array $models, Collection $results, $relation): array
    {
        return $this->matchOneOrMany($models, $results, $relation, 'one');
    }
    
    /**
     * Match the eagerly loaded results to their many parents.
     * 
     * @param  array   $models
     * @param  \Syscodes\Components\Database\Erostrine\Collection  $results
     * @param  string  $relation
     * 
     * @return array
     */
    public function matchMany(array $models, Collection $results, $relation): array
    {
        return $this->matchOneOrMany($models, $results, $relation, 'many');
    }
    
    /**
     * Match the eagerly loaded results to their many parents.
     * 
     * @param  array   $models
     * @param  \Syscodes\Components\Database\Erostrine\Collection  $results
     * @param  string  $relation
     * @param  string  $type
     * 
     * @return array
     */
    protected function matchOneOrMany(
        array $models, 
        Collection $results, 
        $relation, 
        $type
    ): array {
        $dictionary = $this->buildDictionary($results);
        
        foreach ($models as $model) {
            $key = $model->getAttribute($this->localKey);
            
            if (isset($dictionary[$key])) {
                $value = $this->getRelationValue($dictionary, $key, $type);
                
                $model->setRelation($relation, $value);
            }
        }
        
        return $models;
    }
    
    /**
     * Get the value of a relationship by one or many type.
     * 
     * @param  array   $dictionary
     * @param  string  $key
     * @param  string  $type
     * 
     * @return mixed
     */
    protected function getRelationValue(array $dictionary, $key, $type)
    {
        $value = $dictionary[$key];
        
        return $type == 'one' ? head($value, true) : $this->related->newCollection($value);
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
        
        $foreign = $this->getPlainForeignKey();
        
        foreach ($results as $result) {
            $key = $result->{$foreign};
            
            $dictionary[$key][] = $result;
        }
        
        return $dictionary;
    }
    
    /**
     * Attach a model instance to the parent model.
     * 
     * @param  \Syscodes\Components\Database\Erostrine\Model  $model
     * 
     * @return \Syscodes\Components\Database\Erostrine\Model
     */
    public function save(Model $model)
    {
        $model->setAttribute($this->getPlainForeignKey(), $this->getParentKey());
        
        return $model->save() ? $model : false;
    }
    
    /**
     * Attach an array of models to the parent instance.
     * 
     * @param  array  $models
     * 
     * @return array
     */
    public function saveMany(array $models): array
    {
        foreach ($models as $model) {
            $this->save($model);
        }
        
        return $models;
    }
    
    /**
     * Get the plain foreign key.
     * 
     * @return string[]
     */
    public function getPlainForeignKey()
    {
        $segments = explode('.', $this->getForeignKeyName());
        
        return $segments[count($segments) - 1];
    }
    
    /**
     * Get the key value of the parent's local key.
     * 
     * @return mixed
     */
    public function getParentKey()
    {
        return $this->parent->getAttribute($this->localKey);
    }
    
    /**
     * Get the foreign key for the relationship.
     * 
     * @return string
     */
    public function getForeignKeyName(): string
    {
        return $this->foreignKey;
    }
    
    /**
     * Get the local key for the relationship.
     * 
     * @return string
     */
    public function getLocalKeyName(): string
    {
        return $this->localKey;
    }
}