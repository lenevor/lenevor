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

namespace Syscodes\Components\Debug\FatalExceptions;

use Throwable;
use ArrayObject;
use Syscodes\Components\Core\Http\Exceptions\HttpException;

/**
 * FlattenException wraps a PHP Error or Exception to be able to serialize it.
 * Basically, this class removes all objects from the trace.
 */
class FlattenException
{
    /**
     * Gets the class name.
     * 
     * @var string $class
     */
    protected $class;

    /**
     * Gets the code of error.
     * 
     * @var int $code
     */
    protected $code;

    /**
     * Gets the file path.
     * 
     * @var string $file
     */
    protected $file;

    /**
     * Gets the headers HTTP.
     * 
     * @var array $headers
     */
    protected $headers;

    /**
     * Gets the line where specifice the line number and code in happened an error.
     *  
     * @var int $line
     */
    protected $line;

    /**
     * Gets the message of exception.
     * 
     * @var string $message
     */
    protected $message;

    /**
     * Gets the previous exception.
     * 
     * @var string $previous
     */
    protected $previous;

    /**
     * Gets the status code response.
     * 
     * @var int $statusCode
     */
    protected $statusCode;

    /**
     * Gets the trace.
     * 
     * @var array $trace
     */
    protected $trace;

    /**
     * Load the exception with their respective status code and headers.
     * 
     * @param  \Throwable  $exception
     * @param  int|null    $statusCode
     * @param  array       $headers
     * 
     * @return void
     */
    public static function make(Throwable $exception, $statusCode = null, array $headers = [])
    {
        return static::makeFromThrowable($exception, $statusCode, $headers);
    }

    /**
     * An exception is loaded to change the initial value in set methods.
     * 
     * @param  \Throwable  $exception
     * @param  int|null  $statusCode
     * @param  array  $headers
     * 
     * @return new static
     */
    public static function makeFromThrowable(Throwable $exception, ?int $statusCode = null, array $headers = [])
    {
        $e = new static;
        $e->setMessage($exception->getMessage());
        $e->setCode($exception->getCode());

        if ($exception instanceof HttpException) {
            $statusCode = $exception->getStatusCode();
            $headers    = array_merge($headers, $exception->getHeaders());
        }

        if ($statusCode === null) {
            $statusCode = 500;
        }

        $e->setStatusCode($statusCode);
        $e->setHeaders($headers);
        $e->setClass($exception instanceof FatalThrowableError ? $exception->getOriginalClassName() : get_class($exception));
        $e->setFile($exception->getFile());
        $e->setLine($exception->getLine());
        $e->setTraceFromThrowable($exception);

        $previous = $exception->getPrevious();

        if ($previous instanceof Throwable) {
            $e->setPrevious(static::makeFromThrowable($previous));
        }

        return $e;
    }

    /*
    |-----------------------------------------------------------------
    | Getter And Setter Methods
    |-----------------------------------------------------------------
    */
    
    /**
     * Gets the class name.
     * 
     * @return string
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * Sets the class name.
     * 
     * @param  string  $class
     * 
     * @return static
     */
    public function setClass($class): static
    {
        $this->class = 'c' === $class[0] && strpos($class, "class@anonymous\0") === 0 ? get_parent_class($class).'@anonymous' : $class;

        return $this;
    }
    
    /**
     * Gets the code of error.
     * 
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * Sets the code of error.
     * 
     * @param  int  $code
     * 
     * @return static
     */
    public function setCode($code): static
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Gets the file path.
     * 
     * @return string
     */
    public function getFile(): string
    {
        return $this->file;
    }

     /**
     * Sets the file path.
     * 
     * @param  string  $file
     * 
     * @return static
     */
    public function setFile($file): static
    {
        $this->file = $file;

        return $this;
    }

    /**
     * Gets the headers HTTP.
     * 
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Sets the headers HTTP.
     * 
     * @param  array  $headers
     * 
     * @return static
     */
    public function setHeaders(array $headers): static
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * Gets the line where specifice the line number and code in happened an error.
     * 
     * @return int
     */
    public function getLine(): int
    {
        return $this->line;
    }

    /**
     * Sets the line where specifice the line number and code in happened an error.
     * 
     * @param  int  $line
     * 
     * @return static
     */
    public function setLine($line): static
    {
        $this->line = $line;

        return $this;
    }

    /**
     * Gets the message of exception.
     * 
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Sets the message of exception.
     * 
     * @param  string  $message
     * 
     * @return static
     */
    public function setMessage($message): static
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Gets the previous exception.
     * 
     * @return void
     */
    public function getPrevious()
    {
        return $this->previous;
    }

    /**
     * Sets the previous exception.
     * 
     * @param  mixed  $previous
     * 
     * @return static
     */
    public function setPrevious($previous): static
    {
        $this->previous = $previous;

        return $this;
    }

    /**
     * Gets all previous exceptions.
     * 
     * @return array
     */
    public function getAllPrevious(): array
    {
        $exceptions = [];
        $exception  = $this;

        while ($exception = $exception->getPrevious()) {
            $exceptions[] = $exception;
        }

        return $exceptions;
    }

    /**
     * Gets the status code response.
     * 
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Sets the status code response.
     * 
     * @param  int  $code
     * 
     * @return static
     */
    public function setStatusCode($code): static
    {
        $this->statusCode = $code;

        return $this;
    }

    /**
     * Gets the trace.
     * 
     * @return void
     */
    public function getTrace()
    {
        return $this->trace;
    }

    /**
     * Converts the collection to an array.
     * 
     * @return array
     */
    public function toArray(): array
    {
        $exceptions = [];

        foreach (array_merge([$this], $this->getAllPrevious()) as $exception) {
            $exceptions[] = [
                'message' => $exception->getMessage(),
                'class'   => $exception->getClass(),
                'trace'   => $exception->getTrace(),
            ];
        }

        return $exceptions;
    }
    
    /**
     * Sets the trace from throwable.
     * 
     * @param  \Throwable  $throwable 
     * 
     * @return void
     */
    public function setTraceFromThrowable(Throwable $throwable)
    {
        return $this->setTrace($throwable->getTrace(), $throwable->getFile(), $throwable->getLine());
    }

    /**
     * Sets the trace.
     * 
     * @param  array   $trace
     * @param  string  $file
     * @param  int     $line
     * 
     * @return static
     */
    public function setTrace($trace, $file, $line): static
    {
        $this->trace   = [];
        $this->trace[] = [
            'namespace'   => '',
            'short_class' => '',
            'class'       => '',
            'type'        => '',
            'function'    => '',
            'file'        => $file,
            'line'        => $line,
            'args'        => [],
        ];

        foreach ($trace as $item) {
            $class     = '';
            $namespace = '';

            if (isset($item['class'])) {
                $parts     = explode('\\', $item['class']);
                $class     = array_pop($parts);
                $namespace = implode('\\', $parts);
            }

            $this->trace[] = [
                'namespace'   => $namespace,
                'short_class' => $class,
                'class'       => $item['class'] ?? '',
                'type'        => $item['type'] ?? '',
                'function'    => $item['function'] ?? null,
                'file'        => $item['file'] ?? null,
                'line'        => $item['line'] ?? null,
                'args'        => isset($item['args']) ? $this->flattenArgs($item['args']) : [],
            ];
        }

        return $this;
    }

    /**
     * Flatten a multi-dimensional array into a many levels.
     * 
     * @param  array  $args
     * @param  int    $level  Default value is 0
     * @param  int    $count  Default value is 0
     * 
     * @return array
     */
    private function flattenArgs($args, $level = 0, &$count = 0): array
    {   
        $result = [];

        foreach ($args as $key => $value) {
            if (++$count > 1e4) {
                return ['array', '*SKIPPED over 10000 entries*'];
            }

            if ($value instanceof \__PHP_Incomplete_Class) {
                $result[$key] = ['incomplete-object', $this->getClassNameFromIncomplete($value)];
            } elseif (is_object($value)) {
                $result[$key] = ['object', get_class($value)];
            } elseif (is_array($value)) {
                if ($level > 10) {
                    $result[$key] = ['array', '*DEEP NESTED ARRAY*'];
                } else {
                    $result[$key] = ['array', $this->flattenArgs($value, $level + 1, $count)];
                }
            } elseif ($value === null) {
                $result[$key] = ['null', null];
            } elseif (is_bool($value)) {
                $result[$key] = ['boolean', $value];
            } elseif (is_int($value)) {
                $result[$key] = ['integer', $value];
            } elseif (is_float($value)) {
                $result[$key] = ['float', $value];
            } elseif (is_resource($value)) {
                $result[$key] = ['resource', get_resource_type($value)];
            } else {
                $result[$key] = ['string', (string) $value];
            }
        }

        return $result;
    }

    /**
     * Gets class name PHP incomplete.
     * 
     * @param  object  $value
     * 
     * @return array
     */
    private function getClassNameFromIncomplete(\__PHP_Incomplete_Class $value): array
    {
        $array = new ArrayObject;
        
        return $array['__PHP_Incomplete_Class_Name'];
    }
}