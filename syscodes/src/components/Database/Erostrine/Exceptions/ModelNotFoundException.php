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
 * @copyright   Copyright (c) 2019 - 2025 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Database\Erostrine\Exceptions;

use Syscodes\Components\Core\Http\Exceptions\LenevorException;

/**
 * ModelNotFoundException.
 */
class ModelNotFoundException extends LenevorException
{
    /**
     * Name of the affected Erostrine model.
     * 
     * @var string $model
     */
    protected $model;

    /**
     * Set the affected Erostrine model.
     * 
     * @param  string  $model
     * 
     * @return static
     */
    public function setModel($model): static
    {
        $this->model = $model;

        $this->message = "No query results for model [{$model}]";

        return $this;
    }

    /**
     * Get the affected Erostrine model.
     * 
     * @return string
     */
    public function getModel(): string
    {
        return $this->model;
    }
}