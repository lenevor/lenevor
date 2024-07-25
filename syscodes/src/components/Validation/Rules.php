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

/**
 * Get the types of rules.
 */
abstract class Rules
{
    /**
     * Get the attribute.
     * 
     * @var Attribute|null $attribute
     */
    protected $attribute;
    
    /**
     * The fillable params.
     * 
     * @var array $fillableParams
     */
    protected $fillableParams = [];
    
    /** 
     * The implicit implementation.
     * 
     * @var bool $implicit
     */
    protected $implicit = false;
    
    /**
     * The key as rule selected.
     * 
     * @var string $key
     */
    protected $key;
    
    /**
     * The message depends of attribute.
     * 
     * @var string $message
     */
    protected $message = "The :attribute is invalid";
    
    /**
     * The parameters according to the type of rule.
     * 
     * @var array $params
     */
    protected $params = [];

    /**
     * The parameters texts according to the type of rule
     * 
     * @var array $paramsTexts
     */
    protected $paramsTexts = [];
    
    /**
     * The Validation implementation.
     * 
     * @var Validation|null $validation
     */
    protected $validation;

    
}