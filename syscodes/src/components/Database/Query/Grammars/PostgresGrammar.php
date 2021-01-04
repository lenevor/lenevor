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
 
namespace Syscodes\Database\Query\Grammars;

use Syscodes\Database\Query\Builder;
use Syscodes\Database\Query\Grammar;

/**
 * Allows make the grammar's for get results of the database
 * using the Postgres database manager.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
class PostGrammar extends Grammar
{
    /**
     * Compile the lock into SQL.
     * 
     * @param  \Syscodes\Database\Query\Builder  $builder
     * @param  bool|string  $value
     * 
     * @return string
     */
    public function compileLock(Builder $builder, $value)
    {
        if ( ! is_string($value))
        {
            return $value ? 'for update' : 'for share';
        }

        return $value;
    }

    /**
     * Compile an insert and get ID statement into SQL.
     * 
     * @param  \Syscodes\Database\Query\Builder  $builder
     * @param  array  $values
     * @param  string  $sequence
     * 
     * @return string
     */
    public function compileInsertGetId(Builder $builder, $values, $sequence)
    {
        if (is_null($sequence)) $sequence = 'id' ;

        return $this->compileInsert($builder, $values).' returning '.$this->wrap($sequence);
    }

     /**
     * Compile a truncate table statement into SQL.
     * 
     * @param  \Syscodes\Database\Query\Builder  $builder
     * 
     * @return array
     */
    public function truncate(Builder $builder)
    {
        return ['truncate table '.$this->wrapTable($builder->from).' restart identity cascade' => []];
    }
}