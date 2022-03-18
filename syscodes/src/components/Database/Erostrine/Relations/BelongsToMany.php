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
}