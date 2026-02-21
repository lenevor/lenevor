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
 * Allows show the results of a column listing query for Postgres Database.
 */
class PostgresProcessor extends Processor
{
    /**
     * Process an  "insert get ID" query.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * @param  string  $sql
     * @param  array  $values
     * @param  string  $sequence 
     * 
     * @return int
     */
    public function processInsertGetId(Builder $builder, $sql, $values, $sequence = null): int
    {
        $connection = $builder->getConnection();

        $connection->recordsHaveBeenModified();

        $result = $connection->selectFromConnection($sql, $values)[0];

        $sequence = $sequence ?: 'id';

        $id = is_object($result) ? $result->{$sequence} : $sequence[$result];

        return is_numeric($id) ? (int) $id : $id;
    }

    /** @inheritDoc */
    public function processTypes($results): array
    {
        return array_map(function ($result) {
            $result = (object) $result;

            return [
                'name' => $result->name,
                'schema' => $result->schema,
                'schema_qualified_name' => $result->schema.'.'.$result->name,
                'implicit' => (bool) $result->implicit,
                'type' => match (strtolower($result->type)) {
                    'b' => 'base',
                    'c' => 'composite',
                    'd' => 'domain',
                    'e' => 'enum',
                    'p' => 'pseudo',
                    'r' => 'range',
                    'm' => 'multirange',
                    default => null,
                },
                'category' => match (strtolower($result->category)) {
                    'a' => 'array',
                    'b' => 'boolean',
                    'c' => 'composite',
                    'd' => 'date_time',
                    'e' => 'enum',
                    'g' => 'geometric',
                    'i' => 'network_address',
                    'n' => 'numeric',
                    'p' => 'pseudo',
                    'r' => 'range',
                    's' => 'string',
                    't' => 'timespan',
                    'u' => 'user_defined',
                    'v' => 'bit_string',
                    'x' => 'unknown',
                    'z' => 'internal_use',
                    default => null,
                },
            ];
        }, $results);
    }

    /** @inheritDoc */
    public function processColumns($results): array
    {
        return array_map(function ($result) {
            $result = (object) $result;

            $autoincrement = $result->default !== null && str_starts_with($result->default, 'nextval(');

            return [
                'name' => $result->name,
                'type_name' => $result->type_name,
                'type' => $result->type,
                'collation' => $result->collation,
                'nullable' => (bool) $result->nullable,
                'default' => $result->generated ? null : $result->default,
                'auto_increment' => $autoincrement,
                'comment' => $result->comment,
                'generation' => $result->generated ? [
                    'type' => match ($result->generated) {
                        's' => 'stored',
                        'v' => 'virtual',
                        default => null,
                    },
                    'expression' => $result->default,
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
                'on_update' => match (strtolower($result->on_update)) {
                    'a' => 'no action',
                    'r' => 'restrict',
                    'c' => 'cascade',
                    'n' => 'set null',
                    'd' => 'set default',
                    default => null,
                },
                'on_delete' => match (strtolower($result->on_delete)) {
                    'a' => 'no action',
                    'r' => 'restrict',
                    'c' => 'cascade',
                    'n' => 'set null',
                    'd' => 'set default',
                    default => null,
                },
            ];
        }, $results);
    }
}