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

namespace Syscodes\Components\Auth\Access;

use Exception;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionFunction;
use Syscodes\Components\Auth\Access\Concerns\HandlesAuthorization;
use Syscodes\Components\Auth\Access\Exceptions\AuthorizationException;
use Syscodes\Components\Auth\Access\Events\GateEvaluated;
use Syscodes\Components\Contracts\Auth\Access\Gate as GateContract;
use Syscodes\Components\Contracts\Container\Container;
use Syscodes\Components\Contracts\Events\Dispatcher;
use Syscodes\Components\Database\Erostrine\Attributes\UsePolicy;
use Syscodes\Components\Support\Arr;
use Syscodes\Components\Support\Collection;
use Syscodes\Components\Support\Str;

use function Syscodes\Components\Support\enum_value;

/**
 * Allows the registered of authorizations into given abilities.
 */
class Gate implements GateContract
{
    use HandlesAuthorization;

    /**
     * All of the defined abilities.
     * 
     * @var array
     */
    protected $abilities = [];
    
    /**
     * All of the registered after callbacks.
     * 
     * @var array
     */
    protected $afterCallbacks = [];
    
    /**
     * All of the registered before callbacks.
     * 
     * @var array
     */
    protected $beforeCallbacks = [];
    
    /**
     * The container instance.
     * 
     * @var \Syscodes\Components\Contracts\Container\Container
     */
    protected $container;

    /**
     * The default denial response for gates and policies.
     *
     * @var \Syscodes\Components\Auth\Access\Response|null
     */
    protected $defaultDenialResponse;

    /**
     * The callback to be used to guess policy names.
     *
     * @var callable|null
     */
    protected $guessPolicyNamesUsingCallback;
    
    /**
     * All of the defined policies.
     * 
     * @var array
     */
    protected $policies = [];

    /**
     * All of the defined abilities using class@method notation.
     *
     * @var array
     */
    protected $stringCallbacks = [];
    
    /**
     * The user resolver callable.
     * 
     * @var callable
     */
    protected $userResolver;

    /**
     * Constructor. Create a new Gate class instance.
     * 
     * @param  \Syscodes\Components\Contracts\Container\container  $container
     * @param  callable  $userResolver
     * @param  array  $abilities
     * @param  array  $policies
     * @param  array  $beforeCallbacks
     * @param  array  $afterCallbacks
     * @param  callable|null  $guessPolicyNamesUsingCallback
     * @return void
     */
    public function __construct(
        Container $container,
        callable $userResolver,
        array $abilities = [],
        array $policies = [],
        array $beforeCallbacks = [],
        array $afterCallbacks = [],
        ?callable $guessPolicyNamesUsingCallback = null,
        
    ) {
        $this->policies = $policies;
        $this->container = $container;
        $this->abilities = $abilities;
        $this->userResolver = $userResolver;
        $this->afterCallbacks = $afterCallbacks;
        $this->beforeCallbacks = $beforeCallbacks;
        $this->guessPolicyNamesUsingCallback = $guessPolicyNamesUsingCallback;
    }    

    /**
     * Determine if a given ability has been defined.
     * 
     * @param  string[]  $ability 
     * @return bool
     */
    public function has($ability): bool
    {
        $abilities = is_array($ability) ? $ability : func_get_args();
        
        foreach ($abilities as $ability) {
            if ( ! isset($this->abilities[enum_value($ability)])) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Define a new ability.
     * 
     * @param  string  $ability
     * @param  callable|string  $callback 
     * @return $this
     */
    public function define(string $ability, callable|string $callback): static
    {
        $ability = enum_value($ability);

        if (is_array($callback) && isset($callback[0]) && is_string($callback[0])) {
            $callback = $callback[0].'@'.$callback[1];
        }
        
        if (is_callable($callback)) {
            $this->abilities[$ability] = $callback;
        } elseif (is_string($callback)) {
            $this->stringCallbacks[$ability] = $callback;

            $this->abilities[$ability] = $this->buildAbilityCallback($ability, $callback);
        } else {
            throw new InvalidArgumentException("Callback must be a callable, callback array, or a 'Class@method' string.");
        }
        
        return $this;
    }
    
    /**
     * Define abilities for a resource.
     * 
     * @param  string  $name
     * @param  string  $class
     * @param  array|null  $abilities 
     * @return static
     */
    public function resource(string $name, string $class, ?array $abilities = null): static
    {
        $abilities = $abilities ?: [
            'viewAny' => 'viewAny',
            'view' => 'view',
            'create' => 'create',
            'update' => 'update',
            'delete' => 'delete',
        ];
        
        foreach ($abilities as $ability => $method) {
            $this->define($name.'.'.$ability, $class.'@'.$method);
        }
        
        return $this;
    }
    
    /**
     * Create the ability callback for a callback string.
     * 
     * @param  string  $ability
     * @param  string  $callback
     * @return \Closure
     */
    protected function buildAbilityCallback(string $ability, string $callback)
    {
        return function () use ($ability, $callback) {
            if (Str::contains($callback, '@')) {
                [$class, $method] = Str::parseCallback($callback);
            } else {
                $class = $callback;
            }
            
            $policy = $this->resolvePolicy($class);

            $arguments = func_get_args();
            
            return isset($method)
                ? $policy->{$method}(...$arguments)
                : $policy(...$arguments);
        };
    }
    
    /**
     * Define a policy class for a given class type.
     * 
     * @param  string  $class
     * @param  string  $policy 
     * @return static
     */
    public function policy(string $class, string $policy): static
    {
        $this->policies[$class] = $policy;
        
        return $this;
    }
    
    /**
     * Register a callback to run before all Gate checks.
     * 
     * @param  callable  $callback 
     * @return static
     */
    public function before(callable $callback): static
    {
        $this->beforeCallbacks[] = $callback;
        
        return $this;
    }
    
    /**
     * Register a callback to run after all Gate checks.
     * 
     * @param  callable  $callback 
     * @return static
     */
    public function after(callable $callback): static
    {
        $this->afterCallbacks[] = $callback;
        
        return $this;
    }
    
    /**
     * Determine if the given ability should be granted for the current user.
     * 
     * @param  string  $ability
     * @param  array  $arguments 
     * @return bool
     */
    public function allows(string $ability, array $arguments = []): bool
    {
        return $this->check($ability, $arguments);
    }
    
    /**
     * Determine if the given ability should be denied for the current user.
     * 
     * @param  string  $ability
     * @param  array  $arguments 
     * @return bool
     */
    public function denies(string $ability, array $arguments = []): bool
    {
        return ! $this->check($ability, $arguments);
    }
    
    /**
     * Determine if the given ability should be granted.
     * 
     * @param  iterable|\UnitEnum|string  $abilities
     * @param  array  $arguments 
     * @return bool
     */
    public function check($abilities, array $arguments = []): bool
    {
        return (new Collection($abilities))->every(
            fn ($ability) => $this->inspect($ability, $arguments)->allowed()
        );
    }
    
    /**
     * Determine if any one of the given abilities should be granted for the current user.
     * 
     * @param  iterable|\UnitEnum|string  $abilities
     * @param  array  $arguments 
     * @return bool
     */
    public function any($abilities, array $arguments = []): bool
    {
        return (new collection($abilities))->contains(fn ($ability) => $this->check($ability, $arguments));
    }

    /**
     * Determine if all of the given abilities should be denied for the current user.
     *
     * @param  iterable|\UnitEnum|string  $abilities
     * @param  mixed  $arguments
     * @return bool
     */
    public function none($abilities, $arguments = []): bool
    {
        return ! $this->any($abilities, $arguments);
    }
    
    /**
     * Determine if the given ability should be granted for the current user.
     * 
     * @param  \UnitEnum|string  $ability
     * @param  array  $arguments 
     * @return \Syscodes\Components\Auth\Access\Response
     * 
     * @throws \Syscodes\Components\Auth\Access\Exceptions\AuthorizationException
     */
    public function authorize($ability, array $arguments = [])
    {
        return $this->inspect($ability, $arguments)->authorize();
    }
    
    /**
     * Inspect the user for the given ability.
     * 
     * @param  \UnitEnum|string  $ability
     * @param  array  $arguments 
     * @return \Syscodes\Components\Auth\Access\Response
     */
    public function inspect($ability, array $arguments = [])
    {
        try {
            $result = $this->raw(enum_value($ability), $arguments);
            
            if ($result instanceof Response) {
                return $result;
            }
            
            return $result 
                ? Response::allow() 
                : ($this->defaultDenialResponse ?? Response::deny());
        } catch (AuthorizationException $e) {
            return $e->toResponse();
        }
    }
    
    /**
     * Get the raw result from the authorization callback.
     * 
     * @param  string  $ability
     * @param  array  $arguments 
     * @return mixed
     * 
     * @throws \Syscodes\Components\Auth\Access\Exceptions\AuthorizationException
     */
    public function raw($ability, array $arguments): mixed
    {
        $arguments = Arr::wrap($arguments);
        
        $user = $this->resolveUser();
        
        // First we will call the "before" callbacks for the Gate. If any of these give
        // back a non-null response, we will immediately return that result in order
        // to let the developers override all checks for some authorization cases.
        $result = $this->callBeforeCallbacks(
            $user, $ability, $arguments
        );
        
        if (is_null($result)) {
            $result = $this->callAuthCallback($user, $ability, $arguments);
        }
        
        // After calling the authorization callback, we will call the "after" callbacks
        // that are registered with the Gate, which allows a developer to do logging
        // if that is required for this application.
         return take($this->callAfterCallbacks(
            $user, $ability, $arguments, $result
        ), function ($result) use ($user, $ability, $arguments) {
            $this->dispatchGateEvaluatedEvent($user, $ability, $arguments, $result);
        });
    }

    /**
     * Determine whether the callback/method can be called with the given user.
     *
     * @param  \Syscodes\Components\Contracts\Auth\Authenticatable|null  $user
     * @param  \Closure|string|array  $class
     * @param  string|null  $method
     * @return bool
     */
    protected function canBeCalledWithUser($user, $class, $method = null): bool
    {
        if ( ! is_null($user)) {
            return true;
        }

        if ( ! is_null($method)) {
            return $this->methodAllowsGuests($class, $method);
        }

        if (is_array($class)) {
            $className = is_string($class[0]) ? $class[0] : get_class($class[0]);

            return $this->methodAllowsGuests($className, $class[1]);
        }

        return $this->callbackAllowsGuests($class);
    }

    /**
     * Determine if the given class method allows guests.
     *
     * @param  string  $class
     * @param  string  $method
     * @return bool
     */
    protected function methodAllowsGuests($class, $method): bool
    {
        try {
            $reflection = new ReflectionClass($class);

            $method = $reflection->getMethod($method);
        } catch (Exception) {
            return false;
        }

        if ($method) {
            $parameters = $method->getParameters();

            return isset($parameters[0]) && $this->parameterAllowsGuests($parameters[0]);
        }

        return false;
    }

    /**
     * Determine if the callback allows guests.
     *
     * @param  callable  $callback
     * @return bool
     *
     * @throws \ReflectionException
     */
    protected function callbackAllowsGuests($callback): bool
    {
        $parameters = (new ReflectionFunction($callback))->getParameters();

        return isset($parameters[0]) && $this->parameterAllowsGuests($parameters[0]);
    }

    /**
     * Determine if the given parameter allows guests.
     *
     * @param  \ReflectionParameter  $parameter
     * @return bool
     */
    protected function parameterAllowsGuests($parameter): bool
    {
        return ($parameter->hasType() && $parameter->allowsNull())
            || ($parameter->isDefaultValueAvailable() && is_null($parameter->getDefaultValue()));
    }
    
    /**
     * Resolve and call the appropriate authorization callback.
     * 
     * @param  \Syscodes\Components\Contracts\Auth\Authenticatable|null  $user
     * @param  string  $ability
     * @param  array  $arguments
     * @return bool
     */
    protected function callAuthCallback($user, string $ability, array $arguments)
    {
        $callback = $this->resolveAuthCallback($user, $ability, $arguments);
        
        return $callback($user, ...$arguments);
    }
    
    /**
     * Call all of the before callbacks and return if a result is given.
     * 
     * @param  \Syscodes\Components\Contracts\Auth\Authenticatable|null  $user
     * @param  string  $ability
     * @param  array  $arguments 
     * @return bool|null
     */
    protected function callBeforeCallbacks($user, string $ability, array $arguments)
    {
        $arguments = array_merge([$user, $ability], $arguments);
        
        foreach ($this->beforeCallbacks as $callback) {
            if ( ! is_null($result = call_user_func_array($callback, $arguments))) {
                return $result;
            }
        }
    }
    
    /**
     * Call all of the after callbacks with check result.
     * 
     * @param  \Syscodes\Components\Contracts\Auth\Authenticatable|null  $user
     * @param  string  $ability
     * @param  array  $arguments
     * @param  bool  $result 
     * @return void
     */
    protected function callAfterCallbacks($user, string $ability, array $arguments, $result): void
    {
        $arguments = array_merge([$user, $ability, $result], $arguments);
        
        foreach ($this->afterCallbacks as $callback) {
            call_user_func_array($callback, $arguments);
        }
    }

    /**
     * Dispatch a gate evaluation event.
     *
     * @param  \Syscodes\Components\Contracts\Auth\Authenticatable|null  $user
     * @param  string  $ability
     * @param  array  $arguments
     * @param  bool|null  $result
     * @return void
     */
    protected function dispatchGateEvaluatedEvent($user, $ability, array $arguments, $result)
    {
        if ($this->container->bound(Dispatcher::class)) {
            $this->container->make(Dispatcher::class)->dispatch(
                new GateEvaluated($user, $ability, $result, $arguments)
            );
        }
    }
    
    /**
     * Resolve the callable for the given ability and arguments.
     * 
     * @param  \Syscodes\Components\Contracts\Auth\Authenticatable|null  $user
     * @param  string  $ability
     * @param  array  $arguments 
     * @return callable
     */
    protected function resolveAuthCallback($user, $ability, array $arguments)
    {
        if (isset($arguments[0]) &&
            ! is_null($policy = $this->getPolicyFor($arguments[0])) &&
            $callback = $this->resolvePolicyCallback($user, $ability, $arguments, $policy)) {
            return $callback;
        }

        if (isset($this->stringCallbacks[$ability])) {
            [$class, $method] = Str::parseCallback($this->stringCallbacks[$ability]);

            if ($this->canBeCalledWithUser($user, $class, $method ?: '__invoke')) {
                return $this->abilities[$ability];
            }
        }

        if (isset($this->abilities[$ability]) &&
            $this->canBeCalledWithUser($user, $this->abilities[$ability])) {
            return $this->abilities[$ability];
        }

        return function () {
            //
        };
    }
    
    /**
     * Resolve the callback for a policy check.
     * 
     * @param  \Syscodes\Components\Contracts\Auth\Authenticatable|null  $user
     * @param  string  $ability
     * @param  array  $arguments
     * @param  mixed  $policy
     * @return callable
     */
    protected function resolvePolicyCallback($user, string $ability, array $arguments, $policy)
    {
        if ( ! is_callable([$policy, $this->formatAbilityToMethod($ability)])) {
            return false;
        }

        return function () use ($user, $ability, $arguments, $policy) {
            // This callback will be responsible for calling the policy's before method and
            // running this policy method if necessary.
            $result = $this->callPolicyBefore(
                $policy, $user, $ability, $arguments
            );

            // When we receive a non-null result from this before method, we will return it
            // as the "final" results. 
            if ( ! is_null($result)) {
                return $result;
            }

            $method = $this->formatAbilityToMethod($ability);

            return $this->callPolicyMethod($policy, $method, $user, $arguments);
        };
    }

    /**
     * Call the "before" method on the given policy, if applicable.
     *
     * @param  mixed  $policy
     * @param  \Syscodes\Components\Contracts\Auth\Authenticatable  $user
     * @param  string  $ability
     * @param  array  $arguments
     * @return mixed
     */
    protected function callPolicyBefore($policy, $user, $ability, $arguments)
    {
        if ( ! method_exists($policy, 'before')) {
            return;
        }

        if ($this->canBeCalledWithUser($user, $policy, 'before')) {
            return $policy->before($user, $ability, ...$arguments);
        }
    }

    /**
     * Call the appropriate method on the given policy.
     *
     * @param  mixed  $policy
     * @param  string  $method
     * @param  \Syscodes\Components\Contracts\Auth\Authenticatable|null  $user
     * @param  array  $arguments
     * @return mixed
     */
    protected function callPolicyMethod($policy, $method, $user, array $arguments)
    {
        // If this first argument is a string, that means they are passing a class name
        // to the policy. We will remove the first argument from this argument array
        // because this policy already knows what type of models it can authorize.
        if (isset($arguments[0]) && is_string($arguments[0])) {
            array_shift($arguments);
        }

        if ( ! is_callable([$policy, $method])) {
            return;
        }

        if ($this->canBeCalledWithUser($user, $policy, $method)) {
            return $policy->{$method}($user, ...$arguments);
        }
    }

    /**
     * Format the policy ability into a method name.
     *
     * @param  string  $ability
     * @return string
     */
    protected function formatAbilityToMethod($ability)
    {
        return str_contains($ability, '-') ? Str::camelcase($ability) : $ability;
    }
    
    /**
     * Get a policy instance for a given class.
     * 
     * @param  object|string  $class 
     * @return mixed
     * 
     * @throws \InvalidArgumentException
     */
    public function getPolicyFor($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        if ( ! is_string($class)) {
            return;
        }
        
        if (isset($this->policies[$class])) {
            return $this->resolvePolicy($this->policies[$class]);
        }

        $policy = $this->getPolicyFromAttribute($class);
        
         if ( ! is_null($policy)) {
            return $this->resolvePolicy($policy);
        }

        foreach ($this->guessPolicyName($class) as $guessedPolicy) {
            if (class_exists($guessedPolicy)) {
                return $this->resolvePolicy($guessedPolicy);
            }
        }

        foreach ($this->policies as $expected => $policy) {
            if (is_subclass_of($class, $expected)) {
                return $this->resolvePolicy($policy);
            }
        }

        $policy = $this->getPolicyFromAttribute($class, includeParents: true);

        if ( ! is_null($policy)) {
            return $this->resolvePolicy($policy);
        }
    }
    
    /**
     * Build a policy class instance of the given type.
     * 
     * @param  object|string  $class 
     * @return mixed
     */
    public function resolvePolicy($class): mixed
    {
        return $this->container->make($class);
    }

    /**
     * Get the policy class from the class attribute.
     *
     * @param  class-string<*>  $class
     * @param  bool  $includeParents
     * @return class-string<*>|null
     */
    protected function getPolicyFromAttribute(string $class, bool $includeParents = false): ?string
    {
        if (! class_exists($class)) {
            return null;
        }

        $reflection = new ReflectionClass($class);

        do {
            $attributes = $reflection->getAttributes(UsePolicy::class);

            if ($attributes !== []) {
                return $attributes[0]->newInstance()->class;
            }
        } while ($includeParents && $reflection = $reflection->getParentClass());

        return null;
    }

    /**
     * Guess the policy name for the given class.
     *
     * @param  string  $class
     * @return array
     */
    protected function guessPolicyName($class)
    {
        if ($this->guessPolicyNamesUsingCallback) {
            return Arr::wrap(call_user_func($this->guessPolicyNamesUsingCallback, $class));
        }

        $classDirname = str_replace('/', '\\', dirname(str_replace('\\', '/', $class)));

        $classDirnameSegments = explode('\\', $classDirname);

        return Arr::wrap(Collection::times(count($classDirnameSegments), function ($index) use ($class, $classDirnameSegments) {
            $classDirname = implode('\\', array_slice($classDirnameSegments, 0, $index));

            return $classDirname.'\\Policies\\'.class_basename($class).'Policy';
        })->when(str_contains($classDirname, '\\Models\\'), function ($collection) use ($class, $classDirname) {
            return $collection->concat([str_replace('\\Models\\', '\\Policies\\', $classDirname).'\\'.class_basename($class).'Policy'])
                ->concat([str_replace('\\Models\\', '\\Models\\Policies\\', $classDirname).'\\'.class_basename($class).'Policy']);
        })->reverse()->values()->first(function ($class) {
            return class_exists($class);
        }) ?: [$classDirname.'\\Policies\\'.class_basename($class).'Policy']);
    }

    /**
     * Specify a callback to be used to guess policy names.
     *
     * @param  callable  $callback
     * @return $this
     */
    public function guessPolicyNamesUsing(callable $callback): static
    {
        $this->guessPolicyNamesUsingCallback = $callback;

        return $this;
    }
    
    /**
     * Get a guard instance for the given user.
     * 
     * @param  \Syscodes\Components\Contracts\Auth\Authenticatable|mixed  $user 
     * @return static
     */
    public function forUser($user): static
    {
        $callback = fn () => $user;
        
        $gate = new static(
            $this->container, 
            $callback, 
            $this->abilities,
            $this->policies,
            $this->beforeCallbacks,
            $this->afterCallbacks,
            $this->guessPolicyNamesUsingCallback
        );

        $gate->defaultDenialResponse = $this->defaultDenialResponse;

        return $gate;
    }
    
    /**
     * Resolve the user from the user resolver.
     * 
     * @return mixed
     */
    protected function resolveUser(): mixed
    {
        return call_user_func($this->userResolver);
    }
    
    /**
     * Get all of the defined abilities.
     * 
     * @return array
     */
    public function abilities(): array
    {
        return $this->abilities;
    }
    
    /**
     * Get all of the defined policies.
     * 
     * @return array
     */
    public function policies(): array
    {
        return $this->policies;
    }

    /**
     * Set the default denial response for gates and policies.
     *
     * @param  \Syscodes\Components\Auth\Access\Response  $response
     * @return $this
     */
    public function defaultDenialResponse(Response $response): static
    {
        $this->defaultDenialResponse = $response;

        return $this;
    }

    /**
     * Set the container instance used by the gate.
     * 
     * @param  \Syscodes\Components\Contracts\Container\Container  $container 
     * @return $this
     */
    public function setContainer(Container $container): static
    {
        $this->container = $container;
        
        return $this;
    }
}