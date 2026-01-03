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

/**
 * Allows get the clause for add a join of atrributes in query sql.
 */
class JoinClause
{
    /**
     * The "on" bindings for the join.
     * 
     * @var array $bindings
     */
    protected $bindings = [];

    /**
     * The "on" clauses for the join.
     * 
     * @var array $clauses
     */
    protected $clauses = [];

    /**
     * The table the join clause is joining to.
     * 
     * @var string $table
     */
    protected $table;

    /**
     * The type of join being performed.
     * 
     * @var string $type
     */
    protected $type;

    /**
     * Constructor. Create a new JoinClause class instance.
     * 
     * @param  string  $type
     * @param  string  $table
     * 
     * @return void
     */
    public function __construct($type, $table)
    {
        $this->type  = $type;
        $this->table = $table;
    }

    /**
     * Add an "on" clause to the join.
     * 
     * @param  string  $first
     * @param  string|null  $operator  
     * @param  string|null  $second  
     * @param  string  $boolean  
     * @param  bool  $where  
     * 
     * @return static
     */
    public function on(
        string $first,
        ?string $operator = null,
        ?string $second = null,
        string $boolean = 'and',
        bool $where = false
    ): static {
        $this->clauses[] = compact('first', 'operator', 'second', 'boolean', 'where');

        if ($where) {
            $this->bindings[] = $second;
        }

        return $this;
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
    public function orOn(string $first, ?string $operator = null, ?string $second = null)
    {
        return $this->on($first, $operator, $second, 'or');
    }

    /**
     * Add an "on where" clause to the join.
     * 
     * @param  string  $first
     * @param  string|null  $operator  
     * @param  string|null  $second  
     * @param  string  $boolean  
     * 
     * @return \Syscodes\Components\Database\Query\JoinClause
     */
    public function where(string $first, ?string $operator = null, ?string $second = null, string $boolean = 'and')
    {
        return $this->on($first, $operator, $second, $boolean, true);
    }

    /**
     * Add an "or on where" clause to the join.
     * 
     * @param  string  $first
     * @param  string|null  $operator  
     * @param  string|null  $second  
     * 
     * @return \Syscodes\Components\Database\Query\JoinClause
     */
    public function orWhere(string $first, ?string $operator = null, ?string $second = null)
    {
        return $this->on($first, $operator, $second, 'or', true);
    }

    /**
     * Add an "on where is null" clause to the join
     * 
     * @param  string  $column
     * @param  string|null  $operator  
     * 
     * @return \Syscodes\Components\Database\Query\JoinClause
     */
    public function whereNull(string $column, string $boolean = 'and')
    {
        return $this->on($column, 'is', new Expression('null'), $boolean, false);
    }
}