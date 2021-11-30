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
 * @copyright   Copyright (c) 2019 - 2021 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Database\Holisen;

use Closure;
use Exception;
use ReflectionClass;
use ReflectionMethod;
use BadMethodCallException;
use Syscodes\Components\Support\Str;
use Syscodes\Components\Collections\Arr;
use Syscodes\Components\Database\Query\Builder as QueryBuilder;

/**
 * Creates a ORM query builder.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class Builder
{
    /**
     * The relationships that should be eagerly loaded by the query.
     * 
     * @var array $eagerLoad
     */
    protected $eagerLoad = [];

    /**
     * The model being queried.
     * 
     * @var \Syscodes\Components\Database\Holisen $model
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