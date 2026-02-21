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
 * Allows identify the ID field and results of SELECT query in a table.
 */
class Processor
{
    /**
     * Process the results of a "select" query.
     * 
     * @param  \Syscodes\Components\Database\Query\Builder  $builder
     * @param  array  $results
     * 
     * @return array
     */
    public function processSelect(Builder $builder, $results)
    {
        return $results;
    }

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
        $builder->getConnection()->insert($sql, $values);

        $id = $builder->getConnection()->getPdo()->lastInsertId($sequence);

        return is_numeric($id) ? (int) $id : $id;
    }

    /**
     * Process the results of a column listing query.
     * 
     * @param  array  $results
     * 
     * @return array
     */
    public function processColumnListing($results): array
    {
        return $results;
    }

    /**
     * Process the results of a schemas query.
     *
     * @param  list<array<string, mixed>>  $results
     * 
     * @return list<array{name: string, path: string|null, default: bool}>
     */
    public function processSchemas($results)
    {
        return array_map(function ($result) {
            $result = (object) $result;

            return [
                'name' => $result->name,
                'path' => $result->path ?? null, // SQLite Only...
                'default' => (bool) $result->default,
            ];
        }, $results);
    }

    /**
     * Process the results of a tables query.
     *
     * @param  list<array<string, mixed>>  $results
     * 
     * @return list<array{name: string, schema: string|null, schema_qualified_name: string, size: int|null, comment: string|null, collation: string|null, engine: string|null}>
     */
    public function processTables($results)
    {
        return array_map(function ($result) {
            $result = (object) $result;

            return [
                'name' => $result->name,
                'schema' => $result->schema ?? null,
                'schema_qualified_name' => isset($result->schema) ? $result->schema.'.'.$result->name : $result->name,
                'size' => isset($result->size) ? (int) $result->size : null,
                'comment' => $result->comment ?? null, // MySQL and PostgreSQL
                'collation' => $result->collation ?? null, // MySQL only
                'engine' => $result->engine ?? null, // MySQL only
            ];
        }, $results);
    }

    /**
     * Process the results of a views query.
     *
     * @param  list<array<string, mixed>>  $results
     * 
     * @return list<array{name: string, schema: string, schema_qualified_name: string, definition: string}>
     */
    public function processViews($results)
    {
        return array_map(function ($result) {
            $result = (object) $result;

            return [
                'name' => $result->name,
                'schema' => $result->schema ?? null,
                'schema_qualified_name' => isset($result->schema) ? $result->schema.'.'.$result->name : $result->name,
                'definition' => $result->definition,
            ];
        }, $results);
    }

    /**
     * Process the results of a types query.
     *
     * @param  list<array<string, mixed>>  $results
     * 
     * @return list<array{name: string, schema: string, type: string, type: string, category: string, implicit: bool}>
     */
    public function processTypes($results)
    {
        return $results;
    }

    /**
     * Process the results of a columns query.
     *
     * @param  list<array<string, mixed>>  $results
     * 
     * @return list<array{name: string, type: string, type_name: string, nullable: bool, default: mixed, auto_increment: bool, comment: string|null, generation: array{type: string, expression: string|null}|null}>
     */
    public function processColumns($results)
    {
        return $results;
    }

    /**
     * Process the results of an indexes query.
     *
     * @param  list<array<string, mixed>>  $results
     * 
     * @return list<array{name: string, columns: list<string>, type: string, unique: bool, primary: bool}>
     */
    public function processIndexes($results)
    {
        return $results;
    }

    /**
     * Process the results of a foreign keys query.
     *
     * @param  list<array<string, mixed>>  $results
     * 
     * @return list<array{name: string, columns: list<string>, foreign_schema: string, foreign_table: string, foreign_columns: list<string>, on_update: string, on_delete: string}>
     */
    public function processForeignKeys($results)
    {
        return $results;
    }
}