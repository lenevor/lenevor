<?php

use Syscodes\Components\Validation\Rules;
use Syscodes\Components\Validation\Rules\Traits\File;

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
     * Gets the message of the attribute.
     * 
     * @var string $message
     */
    protected $message = "The :attribute is required";

    /**
     * Check the $value is valid
     *
     * @param mixed $value
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
     * Set attribute is required if $this->attribute is set
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