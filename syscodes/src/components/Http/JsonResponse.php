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
 * @copyright   Copyright (c) 2019 - 2023 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Http;

use ArrayObject;
use JsonSerializable;
use InvalidArgumentException;
use Syscodes\Components\Contracts\Support\Jsonable;
use Syscodes\Components\Contracts\Support\Arrayable;

/**
 * Response represents an HTTP response in JSON format.
 */
class JsonResponse extends Response
{
    /**
     * The JSON response data.
     * 
     * @var string $data
     */
    protected $data;

    /**
     * The JSON encoding options.
     * 
     * @var int $jsonEncodingOptions
     */
    protected $jsonEncodingOptions = 15;

    /**
     * Constructor. The JsonReponse classs instance.
     * 
     * @param  mixed|null  $data  
     * @param  int  $status  
     * @param  array  $headers  
     * @param  int  $options  
     * @param  bool  $json  
     * 
     * @return void
     */
    public function __construct($data = null, int $status = 200, array $headers = [], int $options = 0, bool $json = false)
    {
        $this->jsonEncodingOptions = $options;

        parent::__construct('', $status, $headers);

        if (null === $data) {
            $data = new ArrayObject;
        }
        
        $json ? $this->setJson($data) : $this->setData($data);

        // Loaded the headers and status code
        $this->send(true);
        
        // Terminate the current script 
        exit;
    }

    /**
     * Creates an instance of the same response class for rendering 
     * the data, status code and headers. 
     * 
     * @param  mixed  $data  The JSON response data
     * @param  int  $status  The response status code
     * @param  array  $headers  An array of response headers
     * 
     * @return static
     */
    public static function render($data = null, $status = 200, $headers = []): static
    {
        return new static($data, $status, $headers);
    }

    /**
     * Allows have a string with Key : value de manera so you must write
     * the entire process in a manul way and without errors
     * 
     * @param  mixed|null  $data  
     * @param  int  $status  
     * @param  array  $headers  
     * 
     * @return static
     */
    public static function toJsonString($data = null, $status = 200, $headers = []): static
    {
        return new static($data, $status, $headers, true);
    }

    /**
     * Get the json_decoded() data from the response.
     * 
     * @param  bool  $options 
     * @param  int  $depth  
     * 
     * @return mixed
     */
    public function getData($options = false, $depth = 512)
    {
        return json_decode($this->data, $options, $depth);
    }

    /**
     * Sets the data to be sent as JSON.
     * 
     * @param  mixed  $data 
     * 
     * @return static
     * 
     * @throws \InvalidArgumentException
     */
    public function setData(mixed $data = []): static
    {
        $options = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;

        $this->jsonEncodingOptions = app()->environment() === 'production' ? $options : $options | JSON_PRETTY_PRINT;
        
        $this->data = match(true) {
            $data instanceof Jsonable => $data->toJson($this->jsonEncodingOptions),
            $data instanceof JsonSerializable => json_encode($data->jsonSerialize(), $this->jsonEncodingOptions),
            $data instanceof Arrayable => json_encode($data->toArray(), $this->jsonEncodingOptions),
            default => json_encode($data, $this->jsonEncodingOptions),
        };

        if ( ! $this->hasJsonValidOptions(json_last_error())) {
            throw new InvalidArgumentException(__('Http.invalidJson', [json_last_error_msg()]));
        }

        return $this->setJson($this->data);
    }

    /**
     * Determine if an error occurred during JSON encoding.
     * 
     * @param  int  $jsonError
     * 
     * @return bool
     */
    protected function hasJsonValidOptions($jsonError): bool
    {
        if ($jsonError === JSON_ERROR_NONE) {
            return true;
        }

        return $this->hasJsonEncondingOptions(JSON_PARTIAL_OUTPUT_ON_ERROR) &&
            in_array($jsonError, [
                JSON_ERROR_RECURSION,
                JSON_ERROR_INF_OR_NAN,
                JSON_ERROR_UNSUPPORTED_TYPE,
        ]);
    }

    /**
     * Determine if a JSON encoding option is set.
     * 
     * @param  int  $option
     * 
     * @return bool
     */
    public function hasJsonEncondingOptions($option): bool
    {
        return (bool) ($this->jsonEncodingOptions & $option);
    }

    /**
     * Sets a raw string containing a JSON document to be sent.
     * 
     * @param  string  $json
     * 
     * @return static
     */
    public function setJson($json): static
    {
        $this->data = $json;

        return $this->update();
    }

    /**
     * Set the JSON encoding options.
     * 
     * @param  int  $options
     * 
     * @return mixed  
     */
    public function setJsonEncodingOptions($options)
    {
        $this->jsonEncodingOptions = $options;

        return $this->setData($this->getData());
    }

    /**
     * Updates the content and headers according to the JSON data.
     *
     * @return static
     */
    protected function update(): static
    {
        if ( ! $this->headers->has('Content-Type') || 'text/javascript' === $this->headers->get('Content-Type')) {
            $this->headers->set('Content-Type', 'application/json');
        }

        return $this->setContent($this->data);
    }
}