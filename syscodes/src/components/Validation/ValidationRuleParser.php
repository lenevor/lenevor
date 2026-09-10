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

namespace Syscodes\Components\Validation;

use Closure;
use Syscodes\Components\Contracts\Validation\CompilableRules;
use Syscodes\Components\Contracts\Validation\InvokableRule;
use Syscodes\Components\Contracts\Validation\Rule as RuleContract;
use Syscodes\Components\Contracts\Validation\ValidationRule;
use Syscodes\Components\Support\Arr;
use Syscodes\Components\Support\Collection;
use Syscodes\Components\Support\Str;
use Syscodes\Components\Validation\Rules\Date;
use Syscodes\Components\Validation\Rules\Exists;
use Syscodes\Components\Validation\Rules\Numeric;
use Syscodes\Components\Validation\Rules\StringRule;
use Syscodes\Components\Validation\Rules\Unique;

/**
 * Allows the validation rule parser.
 */
class ValidationRuleParser
{
    /**
     * The data being validated.
     *
     * @var array
     */
    public $data;

    /**
     * The implicit attributes.
     *
     * @var array
     */
    public $implicitAttributes = [];

    /**
     * Constructor. Create a new validation rule parser.
     *
     * @param  array  $data
     * @return void
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Parse the human-friendly rules into a full rules array for the validator.
     *
     * @param  array  $rules
     * @return \stdClass
     */
    public function explode($rules)
    {
        $this->implicitAttributes = [];

        $rules = $this->explodeRules($rules);

        return (object) [
            'rules' => $rules,
            'implicitAttributes' => $this->implicitAttributes,
        ];
    }

    /**
     * Explode the rules into an array of explicit rules.
     *
     * @param  array  $rules
     * @return array
     */
    protected function explodeRules($rules)
    {
        foreach ($rules as $key => $rule) {
            if (Str::contains($key, '*')) {
                $rules = $this->explodeWildcardRules($rules, $key, [$rule]);

                unset($rules[$key]);
            } else {
                $rules[$key] = $this->explodeExplicitRule($rules, $key);
            }
        }

        return $rules;
    }

    /**
     * Explode the explicit rule into an array if necessary.
     *
     * @param  mixed  $rule
     * @param  string  $attribute
     * @return array
     */
    protected function explodeExplicitRule($rule, $attribute): array
    {
        if (is_string($rule)) {
            return explode('|', $rule);
        }

        if (is_object($rule)) {
            if ($rule instanceof Date || $rule instanceof Numeric || $rule instanceof StringRule) {
                return explode('|', (string) $rule);
            }

            return Arr::wrap($this->prepareRule($rule, $attribute));
        }

        $rules = [];

        foreach ($rule as $value) {
            if ($value instanceof Date || $value instanceof Numeric || $value instanceof StringRule) {
                $rules = array_merge($rules, explode('|', (string) $value));
            } else {
                $rules[] = $this->prepareRule($value, $attribute);
            }
        }

        return $rules;
    }

    /**
     * Prepare the given rule for the Validator.
     *
     * @param  mixed  $rule
     * @param  string  $attribute
     * @return mixed
     */
    protected function prepareRule($rule, $attribute)
    {
        if ($rule instanceof Closure) {
            $rule = new ClosureValidationRule($rule);
        }

        if ($rule instanceof InvokableRule || $rule instanceof ValidationRule) {
            $rule = InvokableValidationRule::make($rule);
        }

        if (! is_object($rule) ||
            $rule instanceof RuleContract ||
            ($rule instanceof Exists && $rule->queryCallbacks()) ||
            ($rule instanceof Unique && $rule->queryCallbacks())) {
            return $rule;
        }

        if ($rule instanceof CompilableRules) {
            return $rule->compile(
                $attribute, $this->data[$attribute] ?? null, Arr::dot($this->data), $this->data
            )->rules[$attribute];
        }

        return (string) $rule;
    }

    /**
     * Define a set of rules that apply to each element in an array attribute.
     *
     * @param  array  $results
     * @param  string  $attribute
     * @param  string|array  $rules
     * @return array
     */
    protected function explodeWildcardRules($results, $attribute, $rules): array
    {
        $pattern = str_replace('\*', '[^\.]*', preg_quote($attribute));

        $data = ValidationData::initializeAndGatherData($attribute, $this->data);

        foreach ($data as $key => $value) {
            if (Str::startsWith($key, $attribute) || (bool) preg_match('/^'.$pattern.'\z/', $key)) {
                foreach ((array) $rules as $rule) {
                    if ($rule instanceof CompilableRules) {
                        $context = Arr::get($this->data, Str::beforeLast($key, '.'));

                        $compiled = $rule->compile($key, $value, $data, $context);

                        $this->implicitAttributes = array_merge_recursive(
                            $compiled->implicitAttributes,
                            $this->implicitAttributes,
                            [$attribute => [$key]]
                        );

                        foreach ($compiled->rules as $compiledAttribute => $compiledRules) {
                            $this->mergeRulesForAttributeInto($results, $compiledAttribute, $compiledRules);
                        }
                    } else {
                        $this->implicitAttributes[$attribute][] = $key;

                        $results = $this->mergeRules($results, $key, $rule);
                    }
                }
            }
        }

        return $results;
    }

    /**
     * Merge additional rules into a given attribute(s).
     *
     * @param  array  $results
     * @param  string|array  $attribute
     * @param  string|array  $rules
     * @return array
     */
    public function mergeRules($results, $attribute, $rules = []): array
    {
        if (is_array($attribute)) {
            foreach ((array) $attribute as $innerAttribute => $innerRules) {
                $results = $this->mergeRulesForAttribute($results, $innerAttribute, $innerRules);
            }

            return $results;
        }

        return $this->mergeRulesForAttribute(
            $results, $attribute, $rules
        );
    }

    /**
     * Merge additional rules into a given attribute.
     *
     * @param  array  $results
     * @param  string  $attribute
     * @param  string|array  $rules
     * @return array
     */
    protected function mergeRulesForAttribute(&$results, $attribute, $rules): array
    {
        $merge = head($this->explodeRules([$rules]));

        $results[$attribute] = array_merge(
            isset($results[$attribute]) ? $this->explodeExplicitRule($results[$attribute], $attribute) : [], $merge
        );

        return $results;
    }

    /**
     * Merge additional rules into a given attribute by reference.
     *
     * @param  array  $results
     * @param  string  $attribute
     * @param  string|array  $rules
     * @return void
     */
    private function mergeRulesForAttributeInto(&$results, $attribute, $rules)
    {
        $merge = head($this->explodeRules([$rules]));

        $results[$attribute] = array_merge(
            isset($results[$attribute]) ? $this->explodeExplicitRule($results[$attribute], $attribute) : [], $merge
        );
    }

    /**
     * Extract the rule name and parameters from a rule.
     *
     * @param  array|string  $rule
     * @return array
     */
    public static function parse($rule): array
    {
        if ($rule instanceof RuleContract || $rule instanceof CompilableRules) {
            return [$rule, []];
        }

        if (is_array($rule)) {
            $rule = static::parseArrayRule($rule);
        } else {
            $rule = static::parseStringRule($rule);
        }

        $rule[0] = static::normalizeRule($rule[0]);

        return $rule;
    }

    /**
     * Parse an array based rule.
     *
     * @param  array  $rule
     * @return array
     */
    protected static function parseArrayRule(array $rule): array
    {
        return [Str::studlycaps(trim(Arr::get($rule, 0, ''))), array_slice($rule, 1)];
    }

    /**
     * Parse a string based rule.
     *
     * @param  string  $rule
     * @return array
     */
    protected static function parseStringRule($rule): array
    {
        $parameters = [];

        // The format for specifying validation rules and parameters follows an
        // easy {rule}:{parameters} formatting convention. For instance the
        // rule "Max:3" states that the value may only be three letters.
        if (Str::contains($rule, ':')) {
            [$rule, $parameter] = explode(':', $rule, 2);

            $parameters = static::parseParameters($rule, $parameter);
        }

        return [Str::studlycaps(trim($rule)), $parameters];
    }

    /**
     * Parse a parameter list.
     *
     * @param  string  $rule
     * @param  string  $parameter
     * @return array
     */
    protected static function parseParameters($rule, $parameter): array
    {
        return static::ruleIsRegex($rule) ? [$parameter] : str_getcsv($parameter, escape: '\\');
    }

    /**
     * Determine if the rule is a regular expression.
     *
     * @param  string  $rule
     * @return bool
     */
    protected static function ruleIsRegex($rule): bool
    {
        return in_array(strtolower($rule), ['regex', 'not_regex', 'notregex'], true);
    }

    /**
     * Normalizes a rule so that we can accept short types.
     *
     * @param  string  $rule
     * @return string
     */
    protected static function normalizeRule($rule): string
    {
        return match ($rule) {
            'Int' => 'Integer',
            'Bool' => 'Boolean',
            default => $rule
        };
    }

    /**
     * Expand and conditional rules in the given array of rules.
     *
     * @param  array  $rules
     * @param  array  $data
     * @return array
     */
    public static function filterConditionalRules($rules, array $data = []): array
    {
        return (new Collection($rules))->mapKeys(function ($attributeRules, $attribute) use ($data) {
            if ( ! is_array($attributeRules) &&
                ! $attributeRules instanceof ConditionalRules) {
                return [$attribute => $attributeRules];
            }

            if ($attributeRules instanceof ConditionalRules) {
                return [$attribute => $attributeRules->passes($data)
                    ? array_filter($attributeRules->rules())
                    : array_filter($attributeRules->defaultRules())
                ];
            }

            return [$attribute => (new Collection($attributeRules))->map(function ($rule) use ($data) {
                if ( ! $rule instanceof ConditionalRules) {
                    return [$rule];
                }

                return $rule->passes($data) ? $rule->rules() : $rule->defaultRules();
            })->filter()->flatten(1)->values()->all()];
        })->all();
    }
}