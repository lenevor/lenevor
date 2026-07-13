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

/**
 * Allows show the results of a column listing query for SQLite Database.
 */
class SQLiteProcessor extends Processor
{
    /** @inheritDoc */
    public function processForeignKeys($results): array
    {
        return array_map(function ($result) {
            $result = (object) $result;

            return [
                'name' => null,
                'columns' => explode(',', $result->columns),
                'foreign_schema' => $result->foreign_schema,
                'foreign_table' => $result->foreign_table,
                'foreign_columns' => explode(',', $result->foreign_columns),
                'on_update' => strtolower($result->on_update),
                'on_delete' => strtolower($result->on_delete),
            ];
        }, $results);
    }

    /**
     * Process the results of a columns query.
     *
     * @param  array  $results
     * @param  string  $sql
     * @return array
     */
    public function processColumns($results, $sql = ''): array
    {
        $hasPrimaryKey = array_sum(array_column($results, 'primary')) === 1;

        return array_map(function ($result) use ($hasPrimaryKey, $sql) {
            $result = (object) $result;

            $type = strtolower($result->type);

            $safeName = preg_quote($result->name, '/');

            $collation = preg_match(
                '/\b'.$safeName.'\b[^,(]+(?:\([^()]+\)[^,]*)?(?:(?:default|check|as)\s*(?:\(.*?\))?[^,]*)*collate\s+["\'`]?(\w+)/i',
                $sql,
                $matches
            ) === 1 ? strtolower($matches[1]) : null;

            $isGenerated = in_array($result->extra, [2, 3]);

            $expression = $isGenerated && preg_match(
                '/\b'.$safeName.'\b[^,]+\s+as\s+\(((?:[^()]+|\((?:[^()]+|\([^()]*\))*\))*)\)/i',
                $sql,
                $matches
            ) === 1 ? $matches[1] : null;

            return [
                'name' => $result->name,
                'type_name' => strtok($type, '(') ?: '',
                'type' => $type,
                'collation' => $collation,
                'nullable' => (bool) $result->nullable,
                'default' => $result->default,
                'auto_increment' => $hasPrimaryKey && $result->primary && $type === 'integer',
                'comment' => null,
                'generation' => $isGenerated ? [
                    'type' => match ((int) $result->extra) {
                        3 => 'stored',
                        2 => 'virtual',
                        default => null,
                    },
                    'expression' => $expression,
                ] : null,
            ];
        }, $results);
    }
}