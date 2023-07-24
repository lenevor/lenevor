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

namespace Syscodes\Components\Debug\Util;

use Syscodes\Components\Contracts\Debug\Table;

/**
 * Gets an associated label with its respective data array.
 */
class ArrayTable implements Table
{
    use TableLabel;

    /**
     * Gets data as associative array.
     * 
     * @var array $data
     */
    protected $data = [];

    /**
     * Contructor. Get an associated label with its respective data array.
     * 
     * @param  array   $data
     * @param  string  $label
     * 
     * @return void
     */
    public function __construct(array $data = [], string $label = '')
    {
        $this->data  = $data;
        $this->label = $label;
    }

    /**
     * Returns data as associative array.
     * 
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }
}