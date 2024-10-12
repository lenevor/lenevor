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

namespace Syscodes\Components\Validation\Rules;

use Syscodes\Components\Validation\Rules;
use Syscodes\Components\Validation\Rules\Traits\File;

/**
 * Gets the attribute must be required.
 */
Final class Required extends Rules
{
    use File;

    /**
     * The attribute if is implicit.
     * 
     * @var bool $implicit
     */
    protected $implicit = true;

    /** 
     * The message depends of attribute.
     * 
     * @var string $message
     */
    protected $message = "The :attribute is required";
    
    /**
     * Check the value is valid.
     * 
     * @param  mixed  $value
     * 
     * @return bool
     */
    public function check($value): bool
    {
        $this->setAttributeAsRequired();
        
        if ($this->attribute and $this->attribute->hasRule('uploaded_file')) {
            return $this->isValueFromUploadedFiles($value) and $value['error'] != UPLOAD_ERR_NO_FILE;
        }
        
        if (is_string($value)) {
            return mb_strlen(trim($value), 'UTF-8') > 0;
        }
        
        if (is_array($value)) {
            return count($value) > 0;
        }
        
        return ! is_null($value);
    }
    
    /**
     * Set attribute is required if $this->attribute is set.
     * 
     * @return void
     */
    protected function setAttributeAsRequired(): void
    {
        if ($this->attribute) {
            $this->attribute->setRequired(true);
        }
    }
}