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
 * @copyright   Copyright (c) 2019-2020 Lenevor PHP Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.7.0
 */
 
namespace Syscode\Database;

use Syscode\Database\Query\Expression;

/**
 * Allows make the grammar's for get results of the database.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
abstract class Grammar
{
    /**
     * The grammar table prefix.
     * 
     * @var string $tablePrefix
     */
    protected $tablePrefix = '';

    /**
     * 
     */

    /**
     * Determine if the given value is a raw expression.
     * 
     * @param  mixed  $value
     * 
     * @return bool
     */
    public function isExpression($value)
    {
        return $value instanceof Expression;
    }

    /**
     * Get the format for database stored dates.
     * 
     * @return string
     */
    public function getDateFormat()
    {
        return 'Y-m-d H:i:s';
    }

    /**
     * Get the grammar's table prefix.
     * 
     * @return void
     */
    public function getTablePrefix()
    {
        return $this->tablePrefix;
    }

    /**
     * Set the grammar's table prefix.
     * 
     * @param  string  $tablePrefix
     * 
     * @return $this
     */
    public function setTablePrefix($tablePrefix)
    {
        $this->tablePrefix = $tablePrefix;

        return $this;
    }

}