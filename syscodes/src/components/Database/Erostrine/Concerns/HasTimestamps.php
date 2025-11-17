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

namespace Syscodes\Components\Database\Erostrine\Concerns;

use DateTime;
use Syscodes\Components\Support\Chronos;
use Syscodes\Components\Support\Facades\Date;

/**
 * HasTimestamps.
 */
trait HasTimestamps
{
    /**
	 * The name of the "created at" column.
	 */
	const CREATED_AT = 'created_at';

	/**
	 * The name of the "updated at" column.
	 */
	const UPDATED_AT = 'updated_at';

    /**
     * Indicates if the model should be timestamped.
     * 
     * @var bool $timestamps
     */
    public $timestamps = true;
    
    /**
     * Update the creation and update timestamps.
     * 
     * @return static
     */
    public function updateTimestamps(): static
    {
        $time = $this->freshTimestamp();
        
        $updatedAtColumn = $this->getUpdatedAtColumn();
        
        if ( ! is_null($updatedAtColumn) && ! $this->isDirty($updatedAtColumn)) {
            $this->setUpdatedAt($time);
        }
        
        $createdAtColumn = $this->getCreatedAtColumn();
        
        if ( ! $this->exists && ! is_null($createdAtColumn) && ! $this->isDirty($createdAtColumn)) {
            $this->setCreatedAt($time);
        }
        
        return $this;
    }
    
    /**
     * Set the value of the "created at" attribute.
     * 
     * @param  mixed  $value
     * 
     * @return static
     */
    public function setCreatedAt($value): static
    {
        $this->{$this->getCreatedAtColumn()} = $value;
        
        return $this;
    }
    
    /**
     * Set the value of the "updated at" attribute.
     * 
     * @param  mixed  $value
     * 
     * @return static
     */
    public function setUpdatedAt($value): static
    {
        $this->{$this->getUpdatedAtColumn()} = $value;
        
        return $this;
    }
    
    /**
     * Get a fresh timestamp for the model.
     * 
     * @return \Syscodes\Components\Support\Chronos
     */
    public function freshTimestamp()
    {
        return Date::now();
    }
    
    /**
     * Get a fresh timestamp for the model.
     * 
     * @return string
     */
    public function freshTimestampString(): string
    {
        return $this->fromDateTime($this->freshTimestamp());
    }
    
    /**
     * Determine if the model uses timestamps.
     * 
     * @return bool
     */
    public function usesTimestamps(): bool
    {
        return $this->timestamps;
    }
    
    /**
     * Get the name of the "created at" column.
     * 
     * @return string|null
     */
    public function getCreatedAtColumn()
    {
        return static::CREATED_AT;
    }
    
    /**
     * Get the name of the "updated at" column.
     * 
     * @return string|null
     */
    public function getUpdatedAtColumn()
    {
        return static::UPDATED_AT;
    }
    
    /**
     * Get the fully qualified "created at" column.
     * 
     * @return string|null
     */
    public function getQualifiedCreatedAtColumn()
    {
        return $this->qualifyColumn($this->getCreatedAtColumn());
    }
    
    /**
     * Convert a DateTime to a storable string.
     * 
     * @param  \DateTime|int  $value
     * 
     * @return string
     */
    public function fromDateTime($value): string
    {
        $format = Date::now();
        
        if ($value instanceof DateTime) {
            //
        } else if (is_numeric($value)) {
            $value = Chronos::createFromTimestamp($value);
        } else if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value)) {
            $value = Chronos::createFromFormat('Y-m-d', $value)->toDateString();
        } else {
            $value = Chronos::createFromFormat($format, $value);
        }
        
        return $value->format($format);
    }
}