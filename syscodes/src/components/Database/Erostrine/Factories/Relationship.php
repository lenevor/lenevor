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

use Syscodes\Components\Database\Erostrine\Model;
use Syscodes\Components\Database\Erostrine\Relations\BelongsToMany;
use Syscodes\Components\Database\Erostrine\Relations\HasOneOrMany;

/**
 * Allows create the child relationship.
 */
class Relationship
{
    /**
     * The related factory instance.
     *
     * @var \Syscodes\Components\Database\Erostrine\Factories\Factory
     */
    protected $factory;

    /**
     * The relationship name.
     *
     * @var string
     */
    protected $relationship;

    /**
     * Constructor. Create a new child relationship instance.
     *
     * @param  \Syscodes\Components\Database\Erostrine\Factories\Factory  $factory
     * @param  string  $relationship 
     * @return void
     */
    public function __construct(Factory $factory, $relationship)
    {
        $this->factory = $factory;
        $this->relationship = $relationship;
    }

    /**
     * Create the child relationship for the given parent model.
     *
     * @param  \Syscodes\Components\Database\Erostrine\Model  $parent 
     * @return void
     */
    public function createFor(Model $parent)
    {
        $relationship = $parent->{$this->relationship}();

        if ($relationship instanceof HasOneOrMany) {
            $this->factory->state([
                $relationship->getForeignKeyName() => $relationship->getParentKey(),
            ])->prependState($relationship->getQuery()->pendingAttributes)->create([], $parent);
        } elseif ($relationship instanceof BelongsToMany) {
            $relationship->attach(
                $this->factory->prependState($relationship->getQuery()->pendingAttributes)->create([], $parent)
            );
        }
    }

    /**
     * Specify the model instances to always use when creating relationships.
     *
     * @param  \Syscodes\Components\Support\Collection  $recycle 
     * @return static
     */
    public function recycle($recycle): static
    {
        $this->factory = $this->factory->recycle($recycle);

        return $this;
    }
}