<?php 

/**
 * Lenevor PHP Framework
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
 
namespace Syscodes\Components\Database\Query;

use Closure;

/**
 * Allows get the clause for add a join of atrributes in query sql.
 */
class JoinClause extends Builder
{
    /**
     * The class name of the parent query builder.
     *
     * @var string
     */
    protected $parentClass;

    /**
     * The connection of the parent query builder.
     *
     * @var \Syscodes\Components\Database\ConnectionInterface
     */
    protected $parentConnection;

    /**
     * The grammar of the parent query builder.
     *
     * @var \Syscodes\Components\Database\Query\Grammars\Grammar
     */
    protected $parentGrammar;

    /**
     * The processor of the parent query builder.
     *
     * @var \Syscodes\Components\Database\Query\Processors\Processor
     */
    protected $parentProcessor;    

    /**
     * The table the join clause is joining to.
     * 
     * @var string $table
     */
    public $table;

    /**
     * The type of join being performed.
     * 
     * @var string $type
     */
    public $type;

    /**
     * Constructor. Create a new JoinClause class instance.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * @param  string  $type
     * @param  string  $table
     * 
     * @return void
     */
    public function __construct(Builder $builder, $type, $table)
    {
        $this->type  = $type;
        $this->table = $table;
        $this->parentClass = get_class($builder);
        $this->parentGrammar = $builder->getQueryGrammar();
        $this->parentProcessor = $builder->getQueryProcessor();
        $this->parentConnection = $builder->getConnection();

        parent::__construct(
            $this->parentConnection, $this->parentGrammar, $this->parentProcessor
        );
    }

    /**
     * Add an "on" clause to the join.
     * 
     * @param  \Closure|\Syscodes\Components\Contracts\Database\Query\Expression|string  $first
     * @param  string|null  $operator  
     * @param  \Syscodes\Components\Contracts\Database\Query\Expression|string|null  $second  
     * @param  string  $boolean
     * 
     * @return static
     */
    public function on(
        $first,
        $operator = null,
        $second = null,
        $boolean = 'and'
    ): static {
        if ($first instanceof Closure) {
            return $this->whereNested($first, $boolean);
        }

        return $this->whereColumn($first, $operator, $second, $boolean);
    }

    /**
     * Add an "or on" clause to the join.
     * 
     * @param  string  $first
     * @param  string|null  $operator  
     * @param  string|null  $second  
     * 
     * @return \Syscodes\Components\Database\Query\JoinClause
     */
    public function orOn($first, $operator = null, $second = null): static
    {
        return $this->on($first, $operator, $second, 'or');
    }

    /**
     * Get a new instance of the join clause builder.
     *
     * @return \Syscodes\Components\Database\Query\JoinClause
     */
    public function newQuery(): static
    {
        return new static($this->newParentQuery(), $this->type, $this->table);
    }

    /**
     * Create a new query instance for sub-query.
     *
     * @return \Syscodes\Components\Database\Query\Builder
     */
    protected function forSubQuery()
    {
        return $this->newParentQuery()->newQuery();
    }

    /**
     * Create a new parent query instance.
     *
     * @return \Syscodes\Components\Database\Query\Builder
     */
    protected function newParentQuery()
    {
        $class = $this->parentClass;

        return new $class($this->parentConnection, $this->parentGrammar, $this->parentProcessor);
    }
}