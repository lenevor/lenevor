<?php

/**
 * Lenevor Framework
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

namespace Syscodes\Components\Database\Erostrine\Relations\Concerns;

use Syscodes\Components\Database\Erostrine\Collection;
use Syscodes\Components\Database\Erostrine\Model;
use Syscodes\Components\Support\Collection as BaseCollection;

/**
 * Trait InteractsWithPivotTable.
 */
trait InteractsWithPivotTable
{
    /**
     * Sync the joining table with the array of given IDs.
     * 
     * @param  array  $ids
     * @param  bool  $detaching
     * 
     * @return array
     */
    public function sync($ids, bool $detaching = true): array
    {
        $changes = [
            'attached' => [], 'detached' => [], 'updated' => [],
        ];

        $current = $this->newPivotQuery()->pluck($this->relatedKey)->all();

        $detach = array_diff($current, array_keys(
            $records = $this->formatSyncList($this->parseWithIds($ids))
        ));

        if ($detaching && count($detach) > 0) {
            $this->detach($detach);

            $changes['detached'] = (array) array_map(fn ($value) => (int) $value, $detach);
        }
        
        $changes = array_merge(
            $changes, $this->attachNew($records, $current)
        );

        return $changes;
    }

    /**
     * Format the sync list so that it is keyed by ID.
     * 
     * @param  array  $records
     * 
     * @return array
     */
    protected function formatSyncList(array $records): array
    {
        return collect($records)->mapKeys(function ($attributes, $id) {
            if ( ! is_array($attributes)) {
                list($id, $attributes) = [$attributes, []];
            }
            
            return [$id => $attributes];
        })->all();
    }
    
    /**
     * Attach all of the IDs that aren't in the current array.
     * 
     * @param  array  $records
     * @param  array  $current
     * 
     * @return array
     */
    protected function attachNew(array $records, array $current): array
    {
        $changes = [
            'attached' => [], 'updated' => []
        ];
        
        foreach ($records as $id => $attributes) {
            if ( ! in_array($id, $current)) {
                $this->attach($id, $attributes);
                
                $changes['attached'][] = (int) $id;
            } elseif ((count($attributes) > 0) && 
                $this->updateExistingPivot($id, $attributes)) {
                $changes['updated'][] = (int) $id;
            }
        }
        
        return $changes;
    }
    
    /**
     * Update an existing pivot record on the table.
     * 
     * @param  mixed  $id
     * @param  array  $attributes
     * 
     * @return int
     */
    public function updateExistingPivot($id, array $attributes): int
    {
        $updated = $this->newPivotStatementForId($id)->update($attributes);
        
        return $updated;
    }
    
    /**
     * Attach a model to the parent.
     * 
     * @param  mixed  $id
     * @param  array  $attributes
     * 
     * @return void
     */
    public function attach($id, array $attributes = []): void
    {
        $this->newPivotStatement()->insert($this->createAttachRecords(
            $this->parseWithIds($id), $attributes
        ));
    }
    
    /**
     * Create an array of records to insert into the pivot table.
     * 
     * @param  array  $ids
     * @param  array  $attributes
     * 
     * @return array
     */
    protected function createAttachRecords($ids, array $attributes): array
    {
        $records = [];

        foreach ($ids as $key => $value) {
            $records[] = $this->formatAttachRecord($key, $value, $attributes);
        }

        return $records;
    }
    
    /**
     * Create a full attachment record payload.
     * 
     * @param  int    $key
     * @param  mixed  $value
     * @param  array  $attributes
     * 
     * @return array
     */
    protected function formatAttachRecord($key, $value, $attributes): array
    {
        list($id, $attributes) = $this->getAttachIdAndAttributes($key, $value, $attributes);

        return array_merge($this->createAttachRecord($id), $attributes);
    }
    
    /**
     * Get the attach record ID and extra attributes.
     * 
     * @param  mixed  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * 
     * @return array
     */
    protected function getAttachIdAndAttributes($key, $value, array $attributes): array
    {
        return is_array($value)
                    ? [$key, array_merge($value, $attributes)]
                    : [$value, $attributes];
    }
    
    /**
     * Create a new pivot attachment record ID.
     * 
     * @param  int   $id
     * 
     * @return array
     */
    protected function createAttachRecord($id): array
    {
        $record[$this->relatedKey] = $id;        
        $record[$this->foreignKey] = $this->parent->getKey();
        
        return $record;
    }
    
    /**
     * Detach models from the relationship.
     * 
     * @param  mixed  $ids
     * 
     * @return int
     */
    public function detach($ids = null): int
    {
        $query = $this->newPivotQuery();
        
        if ( ! is_null($ids)) {
            $ids = $this->parseWithIds($ids);
            
            if (empty($ids)) return 0;
            
            $query->whereIn($this->relatedKey, $ids);
        }
        
        $results = $query->delete();
        
        return $results;
    }

    /**
     * Create a new pivot model instance.
     * 
     * @param  array  $attributes
     * @param  bool  $exists
     * 
     * @return \Syscodes\Components\Database\Erostrine\Relations\Pivot
     */
    public function newPivot(array $attributes = [], bool $exists = false)
    {
        $pivot = $this->related->newPivot(
            $this->parent, $attributes, $this->table, $exists, $this->using
        );

        return $pivot->setPivotKeys($this->foreignKey, $this->relatedKey);
    }
    
    /**
     * Create a new existing pivot model instance.
     * 
     * @param  array  $attributes
     * 
     * @return \Syscodes\Components\Database\Erostrine\Relations\Pivot
     */
    public function newExistingPivot(array $attributes = [])
    {
        return $this->newPivot($attributes, true);
    }
    
    /**
     * Get a new plain query builder for the pivot table.
     * 
     * @return \Syscodes\Components\Database\Query\Builder
     */
    public function newPivotStatement()
    {
        return $this->query->getQuery()->newQuery()->from($this->table);
    }

    /**
     * Get a new pivot statement for a given "other" ID.
     * 
     * @param  mixed  $id
     * 
     * @return \Syscodes\Components\Database\Query\Builder
     */
    public function newPivotStatementForId($id)
    {
        return $this->newPivotQuery()->where($this->relatedKey, $id);
    }

    /**
     * Create a new query builder for the pivot table.
     * 
     * @return \Syscodes\Components\Database\Query\Builder
     */
    protected function newPivotQuery()
    {
        $query = $this->newPivotStatement();

        foreach ($this->pivotWheres as $args) {
            call_user_func_array([$query, 'whereIn'], $args);
        }

        foreach ($this->pivotWhereIns as $args) {
            call_user_func_array([$query, 'where'], $args);
        }

        return $query->where($this->foreignKey, $this->parent->getKey());
    }
    
    /**
     * Set the columns on the pivot table to retrieve.
     * 
     * @param  array|mixed  $columns
     * 
     * @return static
     */
    public function withPivot($columns): static
    {
        $columns = is_array($columns) ? $columns : func_get_args();

        $this->pivotColumns = array_merge($this->pivotColumns, $columns);
        
        return $this;
    }

    /**
     * Run an associative map over each of the items.
     * 
     * @param  mixed  $value
     * 
     * @return array
     */
    protected function parseWithIds($value): array
    {
        if ($value instanceof Model) {
            return [$value->getKey()];
        }

        if ($value instanceof Collection) {
            return $value->modelKeys();
        }

        if ($value instanceof BaseCollection) {
            return $value->toArray();
        }

        return (array) $value;
    }
}