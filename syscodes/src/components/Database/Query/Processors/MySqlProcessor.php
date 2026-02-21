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

use Syscodes\Components\Database\Query\Builder;

/**
 * Allows show the results of a column listing query for Mysql Database.
 */
class MySqlProcessor extends Processor
{
    /**
     * Process the results of a column listing query.
     * 
     * @param  array  $results
     * 
     * @return array
     */
    public function processColumnListing($results): array
    {
        return array_map(fn ($result) => ((object) $result)->column_name, $results);
    }

    /**
     * Process an  "insert get ID" query.
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
        $builder->getConnection()->insert($sql, $values, $sequence);

        $id = $builder->getConnection()->getLastInsertId();

        return is_numeric($id) ? (int) $id : $id;
    }

    /** @inheritDoc */
    public function processColumns($results): array
    {
        return array_map(function ($result) {
            $result = (object) $result;

            return [
                'name' => $result->name,
                'type_name' => $result->type_name,
                'type' => $result->type,
                'collation' => $result->collation,
                'nullable' => $result->nullable === 'YES',
                'default' => $result->default,
                'auto_increment' => $result->extra === 'auto_increment',
                'comment' => $result->comment ?: null,
                'generation' => $result->expression ? [
                    'type' => match ($result->extra) {
                        'STORED GENERATED' => 'stored',
                        'VIRTUAL GENERATED' => 'virtual',
                        default => null,
                    },
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
                'name' => $name = strtolower($result->name),
                'columns' => $result->columns ? explode(',', $result->columns) : [],
                'type' => strtolower($result->type),
                'unique' => (bool) $result->unique,
                'primary' => $name === 'primary',
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
                'on_update' => strtolower($result->on_update),
                'on_delete' => strtolower($result->on_delete),
            ];
        }, $results);
    }
}