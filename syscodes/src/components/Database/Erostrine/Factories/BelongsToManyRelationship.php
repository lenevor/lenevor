<?php

namespace Syscodes\Components\Database\Erostrine\Factories;

use Syscodes\Components\Database\Erostrine\Model;
use Syscodes\Components\Support\Collection;

class BelongsToManyRelationship
{
    /**
     * The related factory instance.
     *
     * @var \Syscodes\Components\Database\Erostrine\Factories\Factory|\Syscodes\Components\Support\Collection|\Syscodes\Components\Database\Erostrine\Model|array
     */
    protected $factory;

    /**
     * The pivot attributes / attribute resolver.
     *
     * @var callable|array
     */
    protected $pivot;

    /**
     * The relationship name.
     *
     * @var string
     */
    protected $relationship;

    /**
     * Constructor. Create a new attached relationship definition.
     *
     * @param  \Syscodes\Components\Database\Erostrine\Factories\Factory|\Syscodes\Components\Support\Collection|\Syscodes\Components\Database\Erostrine\Model|array  $factory
     * @param  callable|array  $pivot
     * @param  string  $relationship
     * 
     * @return void
     */
    public function __construct($factory, $pivot, $relationship)
    {
        $this->factory = $factory;
        $this->pivot = $pivot;
        $this->relationship = $relationship;
    }

    /**
     * Create the attached relationship for the given model.
     *
     * @param  \Syscodes\Components\Database\Erostrine\Model  $model
     * 
     * @return void
     */
    public function createFor(Model $model)
    {
        $factoryInstance = $this->factory instanceof Factory;

        if ($factoryInstance) {
            $relationship = $model->{$this->relationship}();
        }

        Collection::wrap($factoryInstance ? $this->factory->prependState($relationship->getQuery()->pendingAttributes)->create([], $model) : $this->factory)->each(function ($attachable) use ($model) {
            $model->{$this->relationship}()->attach(
                $attachable,
                is_callable($this->pivot) ? call_user_func($this->pivot, $model) : $this->pivot
            );
        });
    }

    /**
     * Specify the model instances to always use when creating relationships.
     *
     * @param  \Syscodes\Components\Support\Collection  $recycle
     * 
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