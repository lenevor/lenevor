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

namespace Syscodes\Components\Database\Erostrine\Factories;

use Closure;
use Syscodes\Components\Database\Erostrine\Model;

/**
 * Gets the parent model attributes.
 */
class BelongsToRelationship
{
    /**
     * The related factory instance.
     *
     * @var \Syscodes\Components\Database\Erostrine\Factories\Factory|\Syscodes\Components\Database\Erostrine\Model
     */
    protected $factory;

    /**
     * The relationship name.
     *
     * @var string
     */
    protected $relationship;

    /**
     * The cached, resolved parent instance ID.
     *
     * @var mixed
     */
    protected $resolved;

    /**
     * Constructor. Create a new "belongs to" relationship definition.
     *
     * @param  \Syscodes\Components\Database\Erostrine\Factories\Factory|\Syscodes\Components\Database\Erostrine\Model  $factory
     * @param  string  $relationship 
     * @return void
     */
    public function __construct($factory, $relationship)
    {
        $this->factory = $factory;
        $this->relationship = $relationship;
    }

    /**
     * Get the parent model attributes and resolvers for the given child model.
     *
     * @param  \Syscodes\Components\Database\Erostrine\Model  $model 
     * @return array
     */
    public function attributesFor(Model $model): array
    {
        $relationship = $model->{$this->relationship}();

        return [
            $relationship->getForeignKeyName() => $this->resolver($relationship->getOwnerKeyName()),
        ];
    }

    /**
     * Get the deferred resolver for this relationship's parent ID.
     *
     * @param  string|null  $key 
     * @return Closure
     */
    protected function resolver($key): Closure
    {
        return function () use ($key) {
            if ( ! $this->resolved) {
                $instance = $this->factory instanceof Factory ? $this->factory->create() : $this->factory;

                return $this->resolved = $key ? $instance->{$key} : $instance->getKey();
            }

            return $this->resolved;
        };
    }

    /**
     * Specify the model instances to always use when creating relationships.
     *
     * @param  \Syscodes\Components\Support\Collection  $recycle 
     * @return static
     */
    public function recycle($recycle): static
    {
        if ($this->factory instanceof Factory) {
            $this->factory = $this->factory->recycle($recycle);
        }

        return $this;
    }
}