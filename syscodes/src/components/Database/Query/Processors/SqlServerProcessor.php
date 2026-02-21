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
 
namespace Syscodes\Components\Database\Query\Processors;

use Exception;
use Syscodes\Components\Database\Connections\Connection;
use Syscodes\Components\Database\Query\Builder;

/**
 * Allows show the results of a column listing query for SqlServer Database.
 */
class SqlServerProcessor extends Processor
{
    /**
     * Process an "insert get ID" query.
     *
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * @param  string  $sql
     * @param  array  $values
     * @param  string|null  $sequence
     * 
     * @return int
     */
    public function processInsertGetId(Builder $builder, $sql, $values, $sequence = null): int
    {
        $connection = $builder->getConnection();

        $connection->insert($sql, $values);

        if ($connection->getConfig('odbc') === true) {
            $id = $this->processInsertGetIdForOdbc($connection);
        } else {
            $id = $connection->getPdo()->lastInsertId();
        }

        return is_numeric($id) ? (int) $id : $id;
    }

    /**
     * Process an "insert get ID" query for ODBC.
     *
     * @param  \Syscodes\Components\Database\Connections\Connection  $connection
     * @return int
     *
     * @throws \Exception
     */
    protected function processInsertGetIdForOdbc(Connection $connection): int
    {
        $result = $connection->selectFromConnection(
            'SELECT CAST(COALESCE(SCOPE_IDENTITY(), @@IDENTITY) AS int) AS insertid'
        );

        if ( ! $result) {
            throw new Exception('Unable to retrieve lastInsertID for ODBC.');
        }

        $row = $result[0];

        return is_object($row) ? $row->insertid : $row['insertid'];
    }

    /** @inheritDoc */
    public function processColumns($results): array
    {
        return array_map(function ($result) {
            $result = (object) $result;

            $type = match ($typeName = $result->type_name) {
                'binary', 'varbinary', 'char', 'varchar', 'nchar', 'nvarchar' => $result->length == -1 ? $typeName.'(max)' : $typeName."($result->length)",
                'decimal', 'numeric' => $typeName."($result->precision,$result->places)",
                'float', 'datetime2', 'datetimeoffset', 'time' => $typeName."($result->precision)",
                default => $typeName,
            };

            return [
                'name' => $result->name,
                'type_name' => $result->type_name,
                'type' => $type,
                'collation' => $result->collation,
                'nullable' => (bool) $result->nullable,
                'default' => $result->default,
                'auto_increment' => (bool) $result->autoincrement,
                'comment' => $result->comment,
                'generation' => $result->expression ? [
                    'type' => $result->persisted ? 'stored' : 'virtual',
                    'expression' => $result->expression,
                ] : null,
            ];
        }, $results);
    }

    /** @inheritDoc */
    public function processIndexes($results): array
    {
        return array_map(function ($result) {
            $result = (object) $result;

            return [
                'name' => strtolower($result->name),
                'columns' => $result->columns ? explode(',', $result->columns) : [],
                'type' => strtolower($result->type),
                'unique' => (bool) $result->unique,
                'primary' => (bool) $result->primary,
            ];
        }, $results);
    }

    /** @inheritDoc */
    public function processForeignKeys($results): array
    {
        return array_map(function ($result) {
            $result = (object) $result;

            return [
                'name' => $result->name,
                'columns' => explode(',', $result->columns),
                'foreign_schema' => $result->foreign_schema,
                'foreign_table' => $result->foreign_table,
                'foreign_columns' => explode(',', $result->foreign_columns),
                'on_update' => strtolower(str_replace('_', ' ', $result->on_update)),
                'on_delete' => strtolower(str_replace('_', ' ', $result->on_delete)),
            ];
        }, $results);
    }
}