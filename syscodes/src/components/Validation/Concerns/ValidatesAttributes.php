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

namespace Syscodes\Components\Validation\Concerns;

use Brick\Math\BigNumber;
use Brick\Math\Exception\MathException;
use Brick\Math\Exception\MathException as BrickMathException;
use DateTime;
use DateTimeInterface;
use DateTimeZone;
use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\DNSCheckValidation;
use Egulias\EmailValidator\Validation\Extra\SpoofCheckValidation;
use Egulias\EmailValidator\Validation\MultipleValidationWithAnd;
use Egulias\EmailValidator\Validation\NoRFCWarningsValidation;
use Egulias\EmailValidator\Validation\RFCValidation;
use Exception;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Syscodes\Components\Container\Container;
use Syscodes\Components\Database\Erostrine\Model;
use Syscodes\Components\Support\Arr;
use Syscodes\Components\Support\Str;
use Syscodes\Components\Support\Collection;
use Syscodes\Components\Validation\Concerns\FilterEmailValidation;
use Syscodes\Components\Validation\FibberDnsGetRecordWrapper;
use Syscodes\Components\Validation\Rules\Unique;
use Syscodes\Components\Validation\Validator;
use ValueError;

/**
 * Allows validate all type of rules.
 */
trait ValidatesAttributes
{
    /**
     * Validate that an attribute was "accepted".
     *
     * This validation rule implies the attribute is "required".
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function validateAccepted($attribute, $value): bool
    {
        $acceptable = ['yes', 'on', '1', 1, true, 'true'];

        return $this->validateRequired($attribute, $value) && in_array($value, $acceptable, true);
    }

    /**
     * Validate that an attribute exists even if not filled.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function validatePresent($attribute, $value): bool
    {
        return Arr::has($this->data, $attribute);
    }

    /**
     * Validate that an attribute is a valid Base64 string.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function validateBase64($attribute, $value): bool
    {
        if ( ! is_string($value) || $value === '') {
            return false;
        }

        $decoded = base64_decode($value, true);

        return $decoded !== false && base64_encode($decoded) === $value;
    }

    /**
     * "Break" on first validation fail.
     *
     * Always returns true, just lets us put "bail" in rules.
     *
     * @return bool
     */
    public function validateBail(): bool
    {
        return true;
    }

    /**
     * Validate that an attribute is an array.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  array<int, int|string>  $parameters
     * @return bool
     */
    public function validateArray($attribute, $value, $parameters = []): bool
    {
        if ( ! is_array($value)) {
            return false;
        }

        if (empty($parameters)) {
            return true;
        }

        return empty(array_diff_key($value, array_fill_keys($parameters, '')));
    }

    /**
     * Validate that an array does not contain any keys other than the given keys.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  array<int, int|string>  $parameters
     * @return bool
     */
    public function validateArrayKeys($attribute, $value, $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'array_keys');

        if ( ! is_array($value)) {
            return false;
        }

        return empty(array_diff_key($value, array_fill_keys($parameters, '')));
    }

    /**
     * Validate that an attribute is a list.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function validateList($attribute, $value): bool
    {
        return is_array($value) && array_is_list($value);
    }

    /**
     * Validate that an attribute is a boolean.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  array{0?: 'strict'}  $parameters
     * @return bool
     */
    public function validateBoolean($attribute, $value, $parameters): bool
    {
        $acceptable = [true, false, 0, 1, '0', '1'];

        if (($parameters[0] ?? null) === 'strict') {
            $acceptable = [true, false];
        }

        return in_array($value, $acceptable, true);
    }

    /**
     * Validate that an attribute has a matching confirmation.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  array{0?: string}  $parameters
     * @return bool
     */
    public function validateConfirmed($attribute, $value, $parameters): bool
    {
        return $this->validateSame($attribute, $value, [$parameters[0] ?? $attribute.'_confirmation']);
    }

    /**
     * Validate an attribute contains a list of values.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  array<int, int|string>  $parameters
     * @return bool
     */
    public function validateContains($attribute, $value, $parameters): bool
    {
        if ( ! is_array($value)) {
            return false;
        }

        return array_all($parameters, fn ($parameter) => in_array($parameter, $value));
    }

    /**
     * Validate that the password of the currently authenticated user matches the given value.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  array<int, int|string>  $parameters
     * @return bool
     */
    protected function validateCurrentPassword($attribute, $value, $parameters): bool
    {
        $auth = $this->container->make('auth');
        $hasher = $this->container->make('hash');

        $guard = $auth->guard(Arr::first($parameters));

        if ($guard->guest()) {
            return false;
        }

        return $hasher->check($value, $guard->user()->getAuthPassword());
    }

    /**
     * Validate an attribute does not contain a list of values.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  array<int, int|string>  $parameters
     * @return bool
     */
    public function validateDoesntContain($attribute, $value, $parameters): bool
    {
        if ( ! is_array($value)) {
            return false;
        }

        return array_all($parameters, fn ($parameter) => ! in_array($parameter, $value, true));
    }
    
    /**
     * Validate that an attribute has a given number of decimal places.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  array<int, int|string>  $parameters
     * @return bool
     */
    public function validateDecimal($attribute, $value, $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'decimal');

        if (! $this->validateNumeric($attribute, $value, [])) {
            return false;
        }

        $matches = [];

        if (preg_match('/^[+-]?\d*\.?(\d*)$/', (string) $value, $matches) !== 1) {
            return false;
        }

        $decimals = strlen(end($matches));

        if (! isset($parameters[1])) {
            return $decimals == $parameters[0];
        }

        return $decimals >= $parameters[0] &&
               $decimals <= $parameters[1];
    }

    /**
     * Validate that an attribute is a valid date.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function validateDate($attribute, $value)
    {
        if ($value instanceof DateTimeInterface) {
            return true;
        }

        try {
            if ((! is_string($value) && ! is_numeric($value)) || strtotime($value) === false) {
                return false;
            }
        } catch (Exception) {
            return false;
        }

        $date = date_parse($value);

        return checkdate($date['month'], $date['day'], $date['year']);
    }

    /**
     * Validate that an attribute matches a date format.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  array<int, int|string>  $parameters
     * @return bool
     */
    public function validateDateFormat($attribute, $value, $parameters)
    {
        $this->requireParameterCount(1, $parameters, 'date_format');

        if (! is_string($value) && ! is_numeric($value)) {
            return false;
        }

        foreach ($parameters as $format) {
            try {
                $date = DateTime::createFromFormat('!'.$format, $value, new DateTimeZone('UTC'));

                if ($date && $date->format($format) == $value) {
                    return true;
                }
            } catch (ValueError) {
                return false;
            }
        }

        return false;
    }

    /**
     * Validate that an attribute was "declined".
     *
     * This validation rule implies the attribute is "required".
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function validateDeclined($attribute, $value)
    {
        $acceptable = ['no', 'off', '0', 0, false, 'false'];

        return $this->validateRequired($attribute, $value) && in_array($value, $acceptable, true);
    }


    /**
     * Validate that an attribute is lowercase.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function validateLowercase($attribute, $value): bool
    {
        return is_string($value) && Str::lower($value) === $value;
    }

    /**
     * Validate that an attribute is an integer.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  array{0?: 'strict'}  $parameters
     * @return bool
     */
    public function validateInteger($attribute, $value, array $parameters = []): bool
    {
        if (($parameters[0] ?? null) === 'strict') {
            return is_int($value);
        }

        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    /**
     * Validate that an attribute is a valid IP.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function validateIp($attribute, $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * Validate that an attribute is a valid IPv4.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function validateIpv4($attribute, $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;
    }

    /**
     * Validate that an attribute is a valid IPv6.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function validateIpv6($attribute, $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
    }

    /**
     * Validate that an attribute is a valid MAC address.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function validateMacAddress($attribute, $value)
    {
        return filter_var($value, FILTER_VALIDATE_MAC) !== false;
    }

    /**
     * Validate the attribute is a valid JSON string.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function validateJson($attribute, $value): bool
    {
        if (is_array($value) || is_null($value)) {
            return false;
        }

        if (! is_scalar($value) && ! method_exists($value, '__toString')) {
            return false;
        }

        return json_validate($value);
    }

    /**
     * "Indicate" validation should pass if value is null.
     *
     * Always returns true, just lets us put "nullable" in rules.
     *
     * @return bool
     */
    public function validateNullable(): bool
    {
        return true;
    }

    /**
     * Validate the size of an attribute is less than or equal to a maximum value.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  array<int, int|string>  $parameters
     * @return bool
     */
    public function validateMax($attribute, $value, $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'max');

        if ($value instanceof UploadedFile && ! $value->isValid()) {
            return false;
        }

        try {
            return BigNumber::of($this->getSize($attribute, $value))->isLessThanOrEqualTo($this->trim($parameters[0]));
        } catch (MathException|BrickMathException) {
            return false;
        }
    }

    /**
     * Validate the size of an attribute is greater than or equal to a minimum value.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  array<int, int|string>  $parameters
     * @return bool
     */
    public function validateMin($attribute, $value, $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'min');

        try {
            return BigNumber::of($this->getSize($attribute, $value))->isGreaterThanOrEqualTo($this->trim($parameters[0]));
        } catch (MathException|BrickMathException) {
            return false;
        }
    }

    /**
     * Validate that an attribute is numeric.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  array{0?: 'strict'}  $parameters
     * @return bool
     */
    public function validateNumeric($attribute, $value, array $parameters): bool
    {
        if (($parameters[0] ?? null) === 'strict' && is_string($value)) {
            return false;
        }

        return is_numeric($value);
    }

    /**
     * Validate that an attribute passes a regular expression check.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  array<int, int|string>  $parameters
     * @return bool
     */
    public function validateRegex($attribute, $value, $parameters): bool
    {
        if ( ! is_string($value) && ! is_numeric($value)) {
            return false;
        }

        $this->requireParameterCount(1, $parameters, 'regex');

        return preg_match($parameters[0], $value) > 0;
    }

     /**
     * Validate that an attribute is a valid e-mail address.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  array<int, int|string>  $parameters
     * @return bool
     */
    public function validateEmail($attribute, $value, $parameters): bool
    {
        if ( ! is_string($value) && ! (is_object($value) && method_exists($value, '__toString'))) {
            return false;
        }

        if (preg_match('/[\r\n]/', (string) $value) > 0) {
            return false;
        }

        $validations = (new Collection($parameters))
            ->unique()
            ->map(fn ($validation) => match (true) {
                $validation === 'strict' => new NoRFCWarningsValidation(),
                $validation === 'dns' => new DNSCheckValidation(Validator::$fibberDnsLookups ? new FibberDnsGetRecordWrapper : null),
                $validation === 'spoof' => new SpoofCheckValidation(),
                $validation === 'filter' => new FilterEmailValidation(),
                $validation === 'filter_unicode' => FilterEmailValidation::unicode(),
                is_string($validation) && class_exists($validation) => $this->container->make($validation),
                default => new RFCValidation(),
            })
            ->all() ?: [new RFCValidation];

        $emailValidator = Container::getInstance()->make(EmailValidator::class);

        return $emailValidator->isValid($value, new MultipleValidationWithAnd($validations));
    }

    /**
     * Validate that an attribute does not pass a regular expression check.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  array<int, int|string>  $parameters
     * @return bool
     */
    public function validateNotRegex($attribute, $value, $parameters): bool
    {
        if ( ! is_string($value) && ! is_numeric($value)) {
            return false;
        }

        $this->requireParameterCount(1, $parameters, 'not_regex');

        return preg_match($parameters[0], $value) < 1;
    }

    /**
     * Validate that a required attribute exists.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function validateRequired($attribute, $value): bool
    {
        if (is_null($value)) {
            return false;
        } elseif (is_string($value) && trim($value) === '') {
            return false;
        } elseif (is_countable($value) && count($value) < 1) {
            return false;
        } elseif ($value instanceof File) {
            return (string) $value->getPath() !== '';
        }

        return true;
    }

    /**
     * Validate that an attribute exists when another attribute has a given value.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  mixed  $parameters
     * @return bool
     */
    public function validateRequiredIf($attribute, $value, $parameters): bool
    {
        $this->requireParameterCount(2, $parameters, 'required_if');

        if (! Arr::has($this->data, $parameters[0])) {
            return true;
        }

        [$values, $other] = $this->parseDependentRuleParameters($parameters);

        if (in_array($other, $values, is_bool($other) || is_null($other))) {
            return $this->validateRequired($attribute, $value);
        }

        return true;
    }

    /**
     * Validate that an attribute exists when another attribute was "accepted".
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  mixed  $parameters
     * @return bool
     */
    public function validateRequiredIfAccepted($attribute, $value, $parameters)
    {
        $this->requireParameterCount(1, $parameters, 'required_if_accepted');

        if ($this->validateAccepted($parameters[0], $this->getValue($parameters[0]))) {
            return $this->validateRequired($attribute, $value);
        }

        return true;
    }

    /**
     * Validate that an attribute exists when another attribute was "declined".
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  mixed  $parameters
     * @return bool
     */
    public function validateRequiredIfDeclined($attribute, $value, $parameters)
    {
        $this->requireParameterCount(1, $parameters, 'required_if_declined');

        if ($this->validateDeclined($parameters[0], $this->getValue($parameters[0]))) {
            return $this->validateRequired($attribute, $value);
        }

        return true;
    }

    /**
     * Validate that an attribute does not exist or is an empty string.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function validateProhibited($attribute, $value)
    {
        return ! $this->validateRequired($attribute, $value);
    }

    /**
     * Validate that an attribute does not exist when another attribute has a given value.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  mixed  $parameters
     * @return bool
     */
    public function validateProhibitedIf($attribute, $value, $parameters)
    {
        $this->requireParameterCount(2, $parameters, 'prohibited_if');

        [$values, $other] = $this->parseDependentRuleParameters($parameters);

        if (in_array($other, $values, is_bool($other) || is_null($other))) {
            return ! $this->validateRequired($attribute, $value);
        }

        return true;
    }

    /**
     * Validate that an attribute does not exist when another attribute was "accepted".
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  mixed  $parameters
     * @return bool
     */
    public function validateProhibitedIfAccepted($attribute, $value, $parameters)
    {
        $this->requireParameterCount(1, $parameters, 'prohibited_if_accepted');

        if ($this->validateAccepted($parameters[0], $this->getValue($parameters[0]))) {
            return $this->validateProhibited($attribute, $value);
        }

        return true;
    }

    /**
     * Validate that an attribute does not exist when another attribute was "declined".
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  mixed  $parameters
     * @return bool
     */
    public function validateProhibitedIfDeclined($attribute, $value, $parameters)
    {
        $this->requireParameterCount(1, $parameters, 'prohibited_if_declined');

        if ($this->validateDeclined($parameters[0], $this->getValue($parameters[0]))) {
            return $this->validateProhibited($attribute, $value);
        }

        return true;
    }

    /**
     * Validate that an attribute does not exist unless another attribute has a given value.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  mixed  $parameters
     * @return bool
     */
    public function validateProhibitedUnless($attribute, $value, $parameters)
    {
        $this->requireParameterCount(2, $parameters, 'prohibited_unless');

        [$values, $other] = $this->parseDependentRuleParameters($parameters);

        if (! in_array($other, $values, is_bool($other) || is_null($other))) {
            return ! $this->validateRequired($attribute, $value);
        }

        return true;
    }

    /**
     * Validate that other attributes do not exist when this attribute exists.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  mixed  $parameters
     * @return bool
     */
    public function validateProhibits($attribute, $value, $parameters)
    {
        if ($this->validateRequired($attribute, $value)) {
            foreach ($parameters as $parameter) {
                if ($this->validateRequired($parameter, Arr::get($this->data, $parameter))) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Indicate that an attribute is excluded.
     *
     * @return bool
     */
    public function validateExclude()
    {
        return false;
    }

    /**
     * Indicate that an attribute should be excluded when another attribute has a given value.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  mixed  $parameters
     * @return bool
     */
    public function validateExcludeIf($attribute, $value, $parameters)
    {
        $this->requireParameterCount(2, $parameters, 'exclude_if');

        if (! Arr::has($this->data, $parameters[0])) {
            return true;
        }

        [$values, $other] = $this->parseDependentRuleParameters($parameters);

        return ! in_array($other, $values, is_bool($other) || is_null($other));
    }

    /**
     * Indicate that an attribute should be excluded when another attribute does not have a given value.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  mixed  $parameters
     * @return bool
     */
    public function validateExcludeUnless($attribute, $value, $parameters)
    {
        $this->requireParameterCount(2, $parameters, 'exclude_unless');

        [$values, $other] = $this->parseDependentRuleParameters($parameters);

        return in_array($other, $values, is_bool($other) || is_null($other));
    }

    /**
     * Validate that an attribute exists when another attribute does not have a given value.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  mixed  $parameters
     * @return bool
     */
    public function validateRequiredUnless($attribute, $value, $parameters)
    {
        $this->requireParameterCount(2, $parameters, 'required_unless');

        [$values, $other] = $this->parseDependentRuleParameters($parameters);

        if (! in_array($other, $values, is_bool($other) || is_null($other))) {
            return $this->validateRequired($attribute, $value);
        }

        return true;
    }

    /**
     * Indicate that an attribute should be excluded when another attribute presents.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  mixed  $parameters
     * @return bool
     */
    public function validateExcludeWith($attribute, $value, $parameters)
    {
        $this->requireParameterCount(1, $parameters, 'exclude_with');

        if (! Arr::has($this->data, $parameters[0])) {
            return true;
        }

        return false;
    }

    /**
     * Indicate that an attribute should be excluded when another attribute is missing.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  mixed  $parameters
     * @return bool
     */
    public function validateExcludeWithout($attribute, $value, $parameters)
    {
        $this->requireParameterCount(1, $parameters, 'exclude_without');

        if ($this->anyFailingRequired($parameters)) {
            return false;
        }

        return true;
    }

    /**
     * Prepare the values and the other value for validation.
     *
     * @param  array<int, int|string>  $parameters
     * @return array
     */
    public function parseDependentRuleParameters($parameters): array
    {
        $other = Arr::get($this->data, $parameters[0]);

        $values = array_slice($parameters, 1);

        if ($this->shouldConvertToBoolean($parameters[0]) || is_bool($other)) {
            $values = $this->convertValuesToBoolean($values);
        }

        if (is_null($other)) {
            $values = $this->convertValuesToNull($values);
        }

        return [$values, $other];
    }

    /**
     * Validate that an attribute exists when any other attribute exists.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  mixed  $parameters
     * @return bool
     */
    public function validateRequiredWith($attribute, $value, $parameters): bool
    {
        if ( ! $this->allFailingRequired($parameters)) {
            return $this->validateRequired($attribute, $value);
        }

        return true;
    }

    /**
     * Validate that an attribute exists when all other attributes exist.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  mixed  $parameters
     * @return bool
     */
    public function validateRequiredWithAll($attribute, $value, $parameters): bool
    {
        if ( ! $this->anyFailingRequired($parameters)) {
            return $this->validateRequired($attribute, $value);
        }

        return true;
    }

    /**
     * Validate that an attribute exists when another attribute does not.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  mixed  $parameters
     * @return bool
     */
    public function validateRequiredWithout($attribute, $value, $parameters): bool
    {
        if ($this->anyFailingRequired($parameters)) {
            return $this->validateRequired($attribute, $value);
        }

        return true;
    }

    /**
     * Validate that an attribute exists when all other attributes do not.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  mixed  $parameters
     * @return bool
     */
    public function validateRequiredWithoutAll($attribute, $value, $parameters): bool
    {
        if ($this->allFailingRequired($parameters)) {
            return $this->validateRequired($attribute, $value);
        }

        return true;
    }

    /**
     * Determine if any of the given attributes fail the required test.
     *
     * @param  array  $attributes
     * @return bool
     */
    protected function anyFailingRequired(array $attributes): bool
    {
        return array_any($attributes, fn ($key) => ! $this->validateRequired($key, $this->getValue($key)));
    }

    /**
     * Determine if all of the given attributes fail the required test.
     *
     * @param  array  $attributes
     * @return bool
     */
    protected function allFailingRequired(array $attributes): bool
    {
        return array_all($attributes, fn ($key) => ! $this->validateRequired($key, $this->getValue($key)));
    }

    /**
     * Validate that two attributes match.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  array<int, int|string>  $parameters
     * @return bool
     */
    public function validateSame($attribute, $value, $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'same');

        $other = Arr::get($this->data, $parameters[0]);

        return $value === $other;
    }

    /**
     * Validate the size of an attribute.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  array<int, int|string>  $parameters
     * @return bool
     */
    public function validateSize($attribute, $value, $parameters)
    {
        $this->requireParameterCount(1, $parameters, 'size');

        try {
            return BigNumber::of($this->getSize($attribute, $value))->isEqualTo($this->trim($parameters[0]));
        } catch (MathException|BrickMathException) {
            return false;
        }
    }

    /**
     * "Validate" optional attributes.
     *
     * Always returns true, just lets us put sometimes in rules.
     *
     * @return bool
     */
    public function validateSometimes(): bool
    {
        return true;
    }

    /**
     * Validate that an attribute is a string.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function validateString($attribute, $value): bool
    {
        return is_string($value);
    }

    /**
     * Validate that an attribute is a valid timezone.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  array<string, null|string>  $parameters
     * @return bool
     */
    public function validateTimezone($attribute, $value, $parameters = [])
    {
        return in_array($value, timezone_identifiers_list(
            constant(DateTimeZone::class.'::'.Str::upper($parameters[0] ?? 'ALL')),
            isset($parameters[1]) ? Str::upper($parameters[1]) : null,
        ), true);
    }

    /**
     * Validate that an attribute is a valid URL.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  array<int, string>  $parameters
     * @return bool
     */
    public function validateUrl($attribute, $value, $parameters = [])
    {
        return Str::isUrl($value, $parameters);
    }

    /**
     * Validate that an attribute is a valid ULID.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function validateUlid($attribute, $value)
    {
        return Str::isUlid($value);
    }

    /**
     * Validate that an attribute is a valid UUID.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  array<int, int<0, 8>|'max'>  $parameters
     * @return bool
     */
    public function validateUuid($attribute, $value, $parameters)
    {
        $version = null;

        if ($parameters !== null && count($parameters) === 1) {
            $version = $parameters[0];

            if ($version !== 'max') {
                $version = (int) $parameters[0];
            }
        }

        return Str::isUuid($value, $version);
    }

    /**
     * Validate the uniqueness of an attribute value on a given database table.
     *
     * If a database column is not specified, the attribute will be used.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  array<int, int|string>  $parameters
     * @return bool
     */
    public function validateUnique($attribute, $value, $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'unique');

        [$connection, $table, $idColumn] = $this->parseTable($parameters[0]);

        // The second parameter position holds the name of the column that needs to
        // be verified as unique. If this parameter isn't specified we will just
        // assume that this column to be verified shares the attribute's name.
        $column = $this->getQueryColumn($parameters, $attribute);

        $id = null;

        if (isset($parameters[2])) {
            [$idColumn, $id] = $this->getUniqueIds($idColumn, $parameters);

            if (! is_null($id)) {
                $id = stripslashes($id);
            }
        }

        // The presence verifier is responsible for counting rows within this store
        // mechanism which might be a relational database or any other permanent
        // data store like Redis, etc. We will use it to determine uniqueness.
        $verifier = $this->getPresenceVerifier($connection);

        $extra = $this->getUniqueExtra($parameters);

        if ($this->currentRule instanceof Unique) {
            $extra = array_merge($extra, $this->currentRule->queryCallbacks());
        }

        return $verifier->getCount(
            $table, $column, $value, $id, $idColumn, $extra
        ) === 0;
    }

    /**
     * Get the excluded ID column and value for the unique rule.
     *
     * @param  string|null  $idColumn
     * @param  array<int, int|string>  $parameters
     * @return array
     */
    protected function getUniqueIds($idColumn, $parameters): array
    {
        $idColumn ??= $parameters[3] ?? 'id';

        return [$idColumn, $this->prepareUniqueId($parameters[2])];
    }

    /**
     * Prepare the given ID for querying.
     *
     * @param  mixed  $id
     * @return int
     */
    protected function prepareUniqueId($id)
    {
        if (preg_match('/\[(.*)\]/', $id, $matches)) {
            $id = $this->getValue($matches[1]);
        }

        if (strtolower($id) === 'null') {
            $id = null;
        }

        if (filter_var($id, FILTER_VALIDATE_INT) !== false) {
            $id = (int) $id;
        }

        return $id;
    }

    /**
     * Get the extra conditions for a unique rule.
     *
     * @param  array<int, int|string>  $parameters
     * @return array
     */
    protected function getUniqueExtra($parameters)
    {
        if (isset($parameters[4])) {
            return $this->getExtraConditions(array_slice($parameters, 4));
        }

        return [];
    }

    /**
     * Parse the connection / table for the unique / exists rules.
     *
     * @param  string  $table
     * @return array
     */
    public function parseTable($table)
    {
        [$connection, $table] = str_contains($table, '.') ? explode('.', $table, 2) : [null, $table];

        if (str_contains($table, '\\') && class_exists($table) && is_a($table, Model::class, true)) {
            $model = new $table;

            $table = $model->getTable();
            $connection ??= $model->getConnectionName();

            if (str_contains($table, '.') && Str::startsWith($table, $connection)) {
                $connection = null;
            }

            $idColumn = $model->getKeyName();
        }

        return [$connection, $table, $idColumn ?? null];
    }

    /**
     * Get the column name for an exists / unique query.
     *
     * @param  array<int, int|string>  $parameters
     * @param  string  $attribute
     * @return int|string
     */
    public function getQueryColumn($parameters, $attribute)
    {
        return isset($parameters[1]) && $parameters[1] !== 'NULL'
            ? $parameters[1]
            : $this->guessColumnForQuery($attribute);
    }

    /**
     * Guess the database column from the given attribute name.
     *
     * @param  string  $attribute
     * @return string
     */
    public function guessColumnForQuery($attribute)
    {
        if (in_array($attribute, Arr::collapse($this->implicitAttributes))
                && ! is_numeric($last = last(explode('.', $attribute)))) {
            return $last;
        }

        return $attribute;
    }

    /**
     * Get the extra conditions for a unique / exists rule.
     *
     * @param  array  $segments
     * @return array
     */
    protected function getExtraConditions(array $segments)
    {
        $extra = [];

        $count = count($segments);

        for ($i = 0; $i < $count; $i += 2) {
            $extra[$segments[$i]] = $segments[$i + 1];
        }

        return $extra;
    }

    /**
     * Check if parameter should be converted to boolean.
     *
     * @param  string  $parameter
     * @return bool
     */
    protected function shouldConvertToBoolean($parameter): bool
    {
        return in_array('boolean', $this->rules[$parameter] ?? []);
    }

    /**
     * Convert the given values to boolean if they are string "true" / "false".
     *
     * @param  array  $values
     * @return array
     */
    protected function convertValuesToBoolean($values): array
    {
        return array_map(fn ($value) => match ($value) {
            'true' => true,
            'false' => false,
            default => $value,
        }, $values);
    }

    /**
     * Convert the given values to null if they are string "null".
     *
     * @param  array  $values
     * @return array
     */
    protected function convertValuesToNull($values): array
    {
        return array_map(function ($value) {
            return Str::lower($value) === 'null' ? null : $value;
        }, $values);
    }
    
    /**
     * Get the size of an attribute.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return mixed
     */
    protected function getSize($attribute, $value)
    {
        $hasNumeric = $this->hasRule($attribute, $this->numericRules);

        // This method will determine if the attribute is a number, string, or file and
        // return the proper size accordingly. If it is a number, then number itself
        // is the size. If it is a file, we take kilobytes, and for a string the
        // entire length of the string will be considered the attribute size.
        if (is_numeric($value) && $hasNumeric) {
            return $value;
        } elseif (is_array($value)) {
            return count($value);
        } elseif ($value instanceof File) {
            return $value->getSize() / 1024;
        }

        return mb_strlen($value);
    }

    /**
     * Parse named parameters to $key => $value items.
     *
     * @param  array  $parameters
     * @return array
     */
    protected function parseNamedParameters($parameters): array
    {
        return array_reduce($parameters, function ($result, $item) {
            [$key, $value] = array_pad(explode('=', $item, 2), 2, null);

            $result[$key] = $value;

            return $result;
        });
    }

    /**
     * Require a certain number of parameters to be present.
     *
     * @param  int  $count
     * @param  array<int, int|string>  $parameters
     * @param  string  $rule
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function requireParameterCount($count, $parameters, $rule)
    {
        if (count($parameters) < $count) {
            throw new InvalidArgumentException("Validation rule $rule requires at least $count parameters.");
        }
    }

    /**
     * Check if the parameters are of the same type.
     *
     * @param  mixed  $first
     * @param  mixed  $second
     * @return bool
     */
    protected function isSameType($first, $second): bool
    {
        return gettype($first) == gettype($second);
    }

    /**
     * Adds the existing rule to the numericRules array if the attribute's value is numeric.
     *
     * @param  string  $attribute
     * @param  string  $rule
     * @return void
     */
    protected function shouldBeNumeric($attribute, $rule): void
    {
        if (is_numeric($this->getValue($attribute))) {
            $this->numericRules[] = $rule;
        }
    }

    /**
     * Trim the value if it is a string.
     *
     * @param  mixed  $value
     * @return string
     */
    protected function trim($value): string
    {
        return is_string($value) ? trim($value) : (string) $value;
    }
}