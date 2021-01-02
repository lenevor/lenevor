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
 * @author      Javier Alexander Campo M. <jalexcam@gmail.com>
 * @link        https://lenevor.com 
 * @copyright   Copyright (c) 2019-2021 Lenevor PHP Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.7.0
 */
 
namespace Syscodes\Database\Query;

/**
 * Allows get the clause for add a join of atrributes in query sql.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
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
     * @param  string|null  $operator  (null by default)
     * @param  string|null  $second  (null by default)
     * @param  string  $boolean  ('and' by default)
     * @param  array  $where  (false by default)
     * 
     * @return $this
     */
    public function on($first, $operator = null, $second = null, $boolean = 'and', $where = false)
    {
        $this->clauses[] = compact('first', 'operator', 'second', 'boolean', 'where');

        if ($where)
        {
            $this->bindings[] = $second;
        }

        return $this;
    }

    /**
     * Add an "or on" clause to the join.
     * 
     * @param  string  $first
     * @param  string|null  $operator  (null by default)
     * @param  string|null  $second  (null by default)
     * 
     * @return \Syscodes\Database\Query\JoinClause
     */
    public function orOn($first, $operator = null, $second = null)
    {
        return $this->on($first, $operator, $second, 'or');
    }

    /**
     * Add an "on where" clause to the join.
     * 
     * @param  string  $first
     * @param  string|null  $operator  (null by default)
     * @param  string|null  $second  (null by default)
     * @param  string  $boolean  ('and' by default)
     * 
     * @return \Syscodes\Database\Query\JoinClause
     */
    public function where($first, $operator = null, $second = null, $boolean = 'and')
    {
        return $this->on($first, $operator, $second, $boolean, true);
    }

    /**
     * Add an "or on where" clause to the join.
     * 
     * @param  string  $first
     * @param  string|null  $operator  (null by default)
     * @param  string|null  $second  (null by default)
     * 
     * @return \Syscodes\Database\Query\JoinClause
     */
    public function orWhere($first, $operator = null, $second = null)
    {
        return $this->on($first, $operator, $second, 'or', true);
    }

    /**
     * Add an "on where is null" clause to the join
     * 
     * @param  string  $column
     * @param  string|null  $operator  (null by default)
     * 
     * @return \Syscodes\Database\Query\JoinClause
     */
    public function whereNull($column, $boolean = 'and')
    {
        return $this->on($column, 'is', new Expression('null'), $boolean, false);
    }
}