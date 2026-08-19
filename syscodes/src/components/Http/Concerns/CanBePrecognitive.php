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

namespace Syscodes\Components\Http\Concerns;

use Syscodes\Components\Support\Collection;

/**
 * Lets know if it is precognitive.
 */
trait CanBePrecognitive
{
    /**
     * Filter the given array of rules into an array of rules that are included in precognitive headers.
     *
     * @param  array  $rules
     * @return array
     */
    public function filterPrecognitiveRules($rules)
    {
        if ( ! $this->headers->has('Precognition-Validate-Only')) {
            return $rules;
        }

        $validateOnly = explode(',', $this->header('Precognition-Validate-Only'));

        return (new Collection($rules))
            ->filter(fn ($rule, $attribute) => $this->shouldValidatePrecognitiveAttribute($attribute, $validateOnly))
            ->all();
    }

    /**
     * Determine if the given attribute should be validated.
     *
     * @param  string  $attribute
     * @param  array  $validateOnly
     * @return bool
     */
    protected function shouldValidatePrecognitiveAttribute($attribute, $validateOnly): bool
    {
        foreach ($validateOnly as $pattern) {
            $regex = '/^'.str_replace('\*', '[^.]+', preg_quote($pattern, '/')).'$/';

            if (preg_match($regex, $attribute)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if the request is attempting to be precognitive.
     * 
     * @return bool
     */
    public function isAttemptingPrecognition(): bool
    {
        return $this->header('Precognition') === 'true';
    }
    
    /**
     * Determine if the request is precognitive.
     * 
     * @return bool
     */
    public function isPrecognitive(): bool
    {
        return $this->attributes->get('precognitive', false);
    }
}