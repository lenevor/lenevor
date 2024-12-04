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
 * Entry point for the Validation component.
 */
class Validator
{
    use Traits\Messages,
        Traits\RegisterValidators;
    
    /**
     * Allows the rules override.
     * 
     * @var bool $allowRuleOverride
     */
    protected $allowRuleOverride = false;
    
    /**
     * Allows use humanize keys.
     * 
     * @var bool $useHumanizeKeys
     */
    protected $useHumanizedKeys = true;
    
    /**
     * Gets the validators.
     * 
     * @var array $validators
     */
    protected $validators = [];
        
    /**
     * Constructor. Create new Validator class instance.
     * 
     * @param  array  $messages
     * 
     * @return void
     */
    public function __construct(array $messages = [])
    {
        $this->messages = $messages;
        
        $this->registerBaseValidators();
    }
}