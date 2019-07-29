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
 * @author      Javier Alexander Campo M. <jalexcam@gmail.com>
 * @link        https://lenevor.com 
 * @copyright   Copyright (c) 2019 Lenevor Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.1.1
 */

namespace Syscode\Http;

use ArrayObject;
use InvalidArgumentException;

/**
 * Response represents an HTTP response in JSON format.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
class JsonResponse extends Response
{
    /**
     * The JSON encoding options.
     * 
     * @var int $jsonEncodingOptions
     */
    protected $jsonEncodingOptions;

    /**
     * Constructor. The JsonReponse classs instance.
     * 
     * @param  mixed|null  $data     (null by default)
     * @param  int         $status   (200 by default)
     * @param  array       $headers  
     * @param  int         $options  (0 by default)
     * @param  bool        $json     (false by default)
     * 
     * @return void
     */
    public function __construct($data = null, int $status = 200, array $headers = [], int $options = 0, bool $json = false)
    {
        $this->jsonEncodingOptions = $options;

        parent::__construct($data, $status, $headers);

        if (null === $data)
        {
            $data = new ArrayObject;
        }
    }
}