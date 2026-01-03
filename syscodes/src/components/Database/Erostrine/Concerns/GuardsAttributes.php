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

namespace Syscodes\Components\Database\Erostrine\Concerns;

use Syscodes\Components\Support\Str;

/**
 * Trait GuardsAttributes.
 */
trait GuardsAttributes
{
    /**
     * The attributes that are mass assignable.
     * 
     * @var string[] $fillable
     */
    protected $fillable = [];

    /**
     * The attributes that aren't mass assignable.
     * 
     * @var string[]|null $guarded
     */
    protected $guarded = ['*'];

    /**
     * Indicates if all mass assignment is enabled.
     * 
     * @var bool $unguarded
     */
    protected static $unguarded = false;

    /**
     * Get the fillable attributes for the model.
     * 
     * @return array
     */
    public function getFillable(): array
    {
        return $this->fillable;
    }

    /**
     * Set the fillable attributes for the model.
     * 
     * @param  array  $fillable
     * 
     * @return static
     */
    public function setFillable(array $fillable): static
    {
        $this->fillable = $fillable;

        return $this;
    }

    /**
     * Merge new fillable attributes with existing fillable attributes on the model.
     * 
     * @param  array  $fillable
     * 
     * @return static
     */
    public function mergeFillable(array $fillable): static
    {
        $this->fillable = array_merge($this->fillable, $fillable);

        return $this;
    }

    /**
     * Get the guarded attributes for the model.
     * 
     * @return array
     */
    public function getGuarded(): array
    {
        return $this->guarded === false
                    ? []
                    : $this->guarded;
    }

    /**
     * Set the guarded attributes for the model.
     * 
     * @param  array  $guarded
     * 
     * @return static
     */
    public function setGuarded(array $guarded): static
    {
        $this->guarded = $guarded;

        return $this;
    }

    /**
     * Merge new guarded attributes with existing guarded attributes on the model.
     * 
     * @param  array  $guarded
     * 
     * @return static
     */
    public function mergeGuarded(array $guarded): static
    {
        $this->guarded = array_merge($this->guarded, $guarded);

        return $this;
    }
    
    /**
     * Disable all mass assignable restrictions.
     * 
     * @param  bool  $state
     * 
     * @return void
     */
    public static function unguard($state = true): void
    {
        static::$unguarded = $state;
    }
    
    /**
     * Enable the mass assignment restrictions.
     * 
     * @return void
     */
    public static function reguard(): void
    {
        static::$unguarded = false;
    }
    
    /**
     * Determine if the current state is "unguarded".
     * 
     * @return bool
     */
    public static function isUnguarded(): bool
    {
        return static::$unguarded;
    }
    
    /**
     * Run the given callable while being unguarded.
     * 
     * @param  callable  $callback
     * 
     * @return mixed
     */
    public static function unguarded(callable $callback)
    {
        if (static::$unguarded) {
            return $callback();
        }
        
        static::unguard();
        
        try {
            return $callback();
        } finally {
            static::reguard();
        }
    }

    /**
     * Determine if the given attribute may be mass assigned.
     * 
     * @param  string  $key
     * 
     * @return bool
     */
    public function isFillable($key): bool
    {
        if (static::$unguarded) {
            return true;
        }
        
        if (in_array($key, $this->getFillable())) {
            return true;
        }

        if ($this->isGuarded($key)) {
            return false;
        }
        
        return empty($this->fillable) && ! Str::startsWith($key, '_');
    }

    /**
     * Determine if the given key is guarded.
     * 
     * @param  string  $key
     * 
     * @return bool
     */
    public function isGuarded($key): bool
    {
        return in_array($key, $this->guarded) || ($this->guarded == ['*']);
    }

    /**
     * Determine if the model is totally guarded.
     * 
     * @return bool
     */
    public function totallyGuarded(): bool
    {
        return (count($this->fillable) === 0) && ($this->guarded == ['*']);
    }
    
    /**
     * Get the fillable attributes of a given array.
     * 
     * @param  array  $attributes
     * 
     * @return array
     */
    protected function fillableFromArray(array $attributes): array
    {
        if (count($this->fillable) > 0 && ! static::$unguarded) {
            return array_intersect_key($attributes, array_flip($this->getFillable()));
        }
        
        return $attributes;
    }
}