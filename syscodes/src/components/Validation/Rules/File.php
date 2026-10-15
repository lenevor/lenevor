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
namespace Syscodes\Components\Validation\Rules;

use InvalidArgumentException;
use Syscodes\Components\Contracts\Validation\DataAwareRule;
use Syscodes\Components\Contracts\Validation\Rule;
use Syscodes\Components\Contracts\Validation\ValidatorAwareRule;
use Syscodes\Components\Support\Arr;
use Syscodes\Components\Support\Facades\Validator;
use Syscodes\Components\Support\Str;
use Syscodes\Components\Support\Traits\Conditionable;
use Syscodes\Components\Support\Traits\Macroable;
use Syscodes\Components\Validation\Rules\ImageFile;

/**
 * Allows get a file more yours attributes.
 */
class File implements Rule, DataAwareRule, ValidatorAwareRule
{
    use Conditionable, Macroable;

    /**
     * The extensions that the given file should match.
     *
     * @var array
     */
    protected $allowedExtensions = [];

    /**
     * The MIME types that the given file should match. This array may also contain file extensions.
     *
     * @var array
     */
    protected $allowedMimetypes = [];

    /**
     * An array of custom rules that will be merged into the validation rules.
     *
     * @var array
    */
    protected $customRules = [];

    /**
     * The data under validation.
     *
     * @var array
     */
    protected $data;

    /**
     * The required file encoding.
     *
     * @var string|null
     */
    protected $encoding = null;

    /**
     * The maximum size in kilobytes that the file can be.
     *
     * @var null|int
     */
    protected $maximumFileSize = null;

    /**
     * The error message after validation, if any.
     *
     * @var array
     */
    protected $messages = [];

    /**
     * The minimum size in kilobytes that the file can be.
     * 
     * @var null|int
     */
    protected $minimumFileSize = null;

    /**
     * The validator performing the validation.
     *
     * @var \Syscodes\Components\Validation\Validator
     */
    protected $validator;

    /**
     * The callback that will generate the "default" version of the file rule.
     *
     * @var string|array|callable|null
     */
    public static $defaultCallback;

    /**
     * Set the default callback to be used for determining the file default rules.
     *
     * If no arguments are passed, the default file rule configuration will be returned.
     *
     * @param  static|callable|null  $callback
     * @return ($callback is null ? static : void)
     *
     * @throws \InvalidArgumentException
     */
    public static function defaults($callback = null)
    {
        if (is_null($callback)) {
            return static::default();
        }

        if ( ! is_callable($callback) && ! $callback instanceof static) {
            throw new InvalidArgumentException('The given callback should be callable or an instance of '.static::class);
        }

        static::$defaultCallback = $callback;
    }

    /**
     * Get the default configuration of the file rule.
     *
     * @return static
     */
    public static function default()
    {
        $file = is_callable(static::$defaultCallback)
            ? call_user_func(static::$defaultCallback)
            : static::$defaultCallback;

        return $file instanceof Rule ? $file : new self();
    }

    /**
     * Limit the uploaded file to only image types.
     *
     * @param  bool  $allowSvg
     * @return ImageFile
     */
    public static function image($allowSvg = false)
    {
        return new ImageFile($allowSvg);
    }

    /**
     * Limit the uploaded file to the given MIME types or file extensions.
     *
     * @param  string|array<int, string>  $mimetypes
     * @return static
     */
    public static function types($mimetypes)
    {
        return take(new static(), fn ($file) => $file->allowedMimetypes = (array) $mimetypes);
    }

    /**
     * Limit the uploaded file to the given file extensions.
     *
     * @param  string|array<int, string>  $extensions
     * @return $this
     */
    public function extensions($extensions): static
    {
        $this->allowedExtensions = (array) $extensions;

        return $this;
    }

    /**
     * Indicate that the uploaded file should be exactly a certain size in kilobytes.
     *
     * @param  string|int  $size
     * @return $this
     */
    public function size($size): static
    {
        $this->minimumFileSize = $this->toKilobytes($size);
        $this->maximumFileSize = $this->minimumFileSize;

        return $this;
    }

    /**
     * Indicate that the uploaded file should be between a minimum and maximum size in kilobytes.
     *
     * @param  string|int  $minSize
     * @param  string|int  $maxSize
     * @return $this
     */
    public function between($minSize, $maxSize): static
    {
        $this->minimumFileSize = $this->toKilobytes($minSize);
        $this->maximumFileSize = $this->toKilobytes($maxSize);

        return $this;
    }

    /**
     * Indicate that the uploaded file should be no less than the given number of kilobytes.
     *
     * @param  string|int  $size
     * @return $this
     */
    public function min($size): static
    {
        $this->minimumFileSize = $this->toKilobytes($size);

        return $this;
    }

    /**
     * Indicate that the uploaded file should be no more than the given number of kilobytes.
     *
     * @param  string|int  $size
     * @return $this
     */
    public function max($size): static
    {
        $this->maximumFileSize = $this->toKilobytes($size);

        return $this;
    }

    /**
     * Indicate that the uploaded file should be in the given encoding.
     *
     * @param  string  $encoding
     * @return $this
     */
    public function encoding($encoding): static
    {
        $this->encoding = $encoding;

        return $this;
    }

    /**
     * Convert a potentially human-friendly file size to kilobytes.
     *
     * @param  string|int  $size
     * @return ($size is int ? int : int|float)
     *
     * @throws \InvalidArgumentException
     */
    protected function toKilobytes($size)
    {
        if ( ! is_string($size)) {
            return $size;
        }

        $size = strtolower(trim($size));

        $value = (float) $size;

        return round(match (true) {
            Str::endsWith($size, 'kb') => $value * 1,
            Str::endsWith($size, 'mb') => $value * 1_000,
            Str::endsWith($size, 'gb') => $value * 1_000_000,
            Str::endsWith($size, 'tb') => $value * 1_000_000_000,
            default => throw new InvalidArgumentException('Invalid file size suffix.'),
        });
    }

    /**
     * Specify additional validation rules that should be merged with the default rules during validation.
     *
     * @param  string|array  $rules
     * @return $this
     */
    public function rules($rules): static
    {
        $this->customRules = array_merge($this->customRules, Arr::wrap($rules));

        return $this;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        $this->messages = [];

        $validator = Validator::make(
            $this->data,
            [$attribute => $this->buildValidationRules()],
            $this->validator->customMessages,
            $this->validator->customAttributes
        );

        if ($validator->fails()) {
            return $this->fail($validator->messages()->all());
        }

        return true;
    }

    /**
     * Build the array of underlying validation rules based on the current state.
     *
     * @return array
     */
    protected function buildValidationRules(): array
    {
        $rules = ['file'];

        $rules = array_merge($rules, $this->buildMimetypes());

        if (! empty($this->allowedExtensions)) {
            $rules[] = 'extensions:'.implode(',', array_map(strtolower(...), $this->allowedExtensions));
        }

        $rules[] = match (true) {
            is_null($this->minimumFileSize) && is_null($this->maximumFileSize) => null,
            is_null($this->maximumFileSize) => "min:{$this->minimumFileSize}",
            is_null($this->minimumFileSize) => "max:{$this->maximumFileSize}",
            $this->minimumFileSize !== $this->maximumFileSize => "between:{$this->minimumFileSize},{$this->maximumFileSize}",
            default => "size:{$this->minimumFileSize}",
        };

        if ($this->encoding) {
            $rules[] = 'encoding:'.$this->encoding;
        }

        return array_merge(array_filter($rules), $this->customRules);
    }

    /**
     * Separate the given MIME types from extensions and return an array of correct rules to validate against.
     *
     * @return array
     */
    protected function buildMimetypes(): array
    {
        if (count($this->allowedMimetypes) === 0) {
            return [];
        }

        $rules = [];

        $mimetypes = array_filter(
            $this->allowedMimetypes,
            fn ($type) => str_contains($type, '/')
        );

        $mimes = array_diff($this->allowedMimetypes, $mimetypes);

        if ($mimetypes !== []) {
            $rules[] = 'mimetypes:'.implode(',', $mimetypes);
        }

        if ($mimes !== []) {
            $rules[] = 'mimes:'.implode(',', $mimes);
        }

        return $rules;
    }

    /**
     * Adds the given failures, and return false.
     *
     * @param  array|string  $messages
     * @return bool
     */
    protected function fail($messages): bool
    {
        $this->messages = array_merge($this->messages, Arr::wrap($messages));

        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return array
     */
    public function message(): array
    {
        return $this->messages;
    }

    /**
     * Set the current validator.
     *
     * @param  \Syscodes\Components\Contracts\Validation\Validator  $validator
     * @return $this
     */
    public function setValidator($validator): static
    {
        $this->validator = $validator;

        return $this;
    }

    /**
     * Set the current data under validation.
     *
     * @param  array  $data
     * @return $this
     */
    public function setData($data): static
    {
        $this->data = $data;

        return $this;
    }
}