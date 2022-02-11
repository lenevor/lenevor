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
 * @copyright   Copyright (c) 2019 - 2022 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Database\Erostrine\Concerns;

use Syscodes\Components\Support\Str;
use Syscodes\Components\Database\Erostrine\Model;
use Syscodes\Components\Database\Erostrine\Builder;
use Syscodes\Components\Database\Erostrine\Relations\BelongsTo;

/**
 * HasRelations.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
trait HasRelations
{
    /**
     * Define an inverse one-to-one or many relationship.
     * 
     * @param  string  $related
     * @param  string|null  $foreignKey
     * @param  string|null  $ownerKey
     * @param  string|null  $relation
     * 
     * @return \Syscodes\Components\Database\Erostrine\Relations\BelongsTo
     */
    public function belongsTo($related, $foreignKey = null, $ownerKey = null, $relation = null)
    {
        if (is_null($relation)) {
            $relation = $this->callBelongsToRelation();
        }

        $instance = $this->newRelatedInstance($related);

        if (is_null($foreignKey)) {
            $foreignKey = Str::snake($relation).'_'.$instance->getKeyName();
        }

        $ownerKey = $ownerKey ?: $instance->getKeyName();

        return $this->newBelongsTo(
            $instance->newQuery(), $this, $foreignKey, $ownerKey, $relation
        );
    }
    
    /**
     * Instantiate a new BelongsTo relationship.
     * 
     * @param  \Syscodes\Components\Database\Erostrine\Builder  $builder
     * @param  \Syscodes\Components\Database\Erostrine\Model  $child
     * @param  string  $foreignKey
     * @param  string  $ownerKey
     * @param  string  $relation
     * 
     * @return \Syscodes\Components\Database\Erostrine\Relations\BelongsTo
     */
    protected function newBelongsTo(Builder $builder, Model $child, $foreignKey, $ownerKey, $relation)
    {
        return new BelongsTo($builder, $child, $foreignKey, $ownerKey, $relation);
    }
    
    /**
     * Calls the "belongs to" relationship name.
     * 
     * @return string
     */
    protected function callBelongsToRelation(): string
    {
        [$one, $two, $caller] = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
        
        return $caller['function'];
    }
    
    /**
     * Create a new model instance for a related model.
     * 
     * @param  string  $class
     * 
     * @return mixed
     */
    protected function newRelatedInstance($class)
    {
        return take(new $class, function ($instance) {
            if ( ! $instance->getConnectionName()) {
                $instance->setConnection($this->connection);
            }
        });
    }
}