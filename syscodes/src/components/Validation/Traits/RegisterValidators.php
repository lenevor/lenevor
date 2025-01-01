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

namespace Syscodes\Components\Validation\Traits;

use Syscodes\Components\Validation\Rules;

/**
 * Get the register all the validators.
 */
trait RegisterValidators
{
    /**
     * Initialize base validators array.
     * 
     * @return void
     */
    protected function registerBaseValidators(): void
    {
        $baseValidator = [
            'required'       => new Rules\Required,
            'email'          => new Rules\Email,
            'alpha'          => new Rules\Alpha,
            'numeric'        => new Rules\Numeric,
            'alpha_num'      => new Rules\AlphaNum,
            'alpha_dash'     => new Rules\AlphaDash,
            'alpha_spaces'   => new Rules\AlphaSpaces,
            'min'            => new Rules\Min,
            'max'            => new Rules\Max,
            'between'        => new Rules\Between,
            'url'            => new Rules\Url,
            'integer'        => new Rules\Integer,
            'boolean'        => new Rules\Boolean,
            'ip'             => new Rules\Ip,
            'ipv4'           => new Rules\Ipv4,
            'ipv6'           => new Rules\Ipv6,
            'array'          => new Rules\TypeArray,
            'same'           => new Rules\Same,
            'regex'          => new Rules\Regex,
            'date'           => new Rules\Date,
            'accepted'       => new Rules\Accepted,
            'present'        => new Rules\Present,
            'different'      => new Rules\Different,
            'callback'       => new Rules\Callback,
            'before'         => new Rules\Before,
            'after'          => new Rules\After,
            'lowercase'      => new Rules\Lowercase,
            'uppercase'      => new Rules\Uppercase,
            'json'           => new Rules\Json,
            'digits'         => new Rules\Digits,
            'digits_between' => new Rules\DigitsBetween,
            'defaults'       => new Rules\Defaults,
            'default'        => new Rules\Defaults, // alias of defaults
            'nullable'       => new Rules\Nullable,
        ];
        
        foreach ($baseValidator as $key => $validator) {
            $this->setValidator($key, $validator);
        }
    }
}