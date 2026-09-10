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

use InvalidArgumentException;
use Symfony\Component\HttpFoundation\File\File;
use Syscodes\Components\Support\Arr;
use Syscodes\Components\Support\Str;

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
     * Validate that an attribute does not exist or is an empty string.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function validateProhibited($attribute, $value): bool
    {
        return ! $this->validateRequired($attribute, $value);
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