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
 * @copyright   Copyright (c) 2019 - 2025 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Database\Erostrine\Exceptions;

use RuntimeException;

/**
 * RelationNotFoundException.
 */
class RelationNotFoundException extends RuntimeException
{
    /**
     * The name of the Erostrine model.
     * 
     * @var string $model
     */
    public $model;

    /**
     * The name of the relation.
     * 
     * @var string $relation
     */
    public $relation;

    /**
     * Create a new exception instance.
     * 
     * @param  object  $model
     * @param  string  $relation
     * @param  string|null  $type
     * 
     * @return static
     */
    public static function make($model, $relation, $type = null): static
    {
        $class = get_class($model);

        $instance = new static(
            is_null($type) 
                ? "Call to defined relationship [{$relation}] on model [{$class}]"
                : "Call to defined relationship [{$relation}] on model [{$class}] of the [{$type}]"
        );

        $instance->model = $model;
        $instance->relation = $relation;

        return $instance;
    }
}