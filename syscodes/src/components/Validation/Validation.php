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
 * @copyright   Copyright (c) 2019 - 2024 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Validation;

use Syscodes\Components\Support\Arr;

/**
 * Get validation based on message.
 */
final class Validation
{
    /**
     * Gets array of the aliases.
     * 
     * @var array $aliases
     */
    protected $aliases = [];

    /**
     * Gets the atributtes.
     * 
     * @var array $atributtes
     */
    protected $atributtes = [];

    /**
     * Get the errors.
     * 
     * @var ErroBag $errors
     */
    public $errors;

    /**
     * Gets the input.
     * 
     * @var array $inputs
     */
    protected $inputs = [];

    /**
     * Gets the invalid data.
     * 
     * @var array invalidData
     */
    protected $invalidData = [];

    /**
     * Get the message separator.
     * 
     * @var string msgSeparator
     */
    protected $msgSeparator = ':';

    /**
     * The validator implementation.
     * 
     * @var mixed $validator
     */
    protected $validator;

    /**
     * Gets the valid data.
     * 
     * @var array $validData
     */
    protected $validData = [];

    /**
     * Get value given the key.
     * 
     * @param  string  $key
     * 
     * @return mixed
     */
    public function getValue($key): mixed
    {
        return Arr::get([], $key);
    }

}