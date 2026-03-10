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

namespace Syscodes\Components\Database\Erostrine\Exceptions;

use BackedEnum;
use Syscodes\Components\Database\Exceptions\RecordNotFoundException;
use Syscodes\Components\Support\Arr;

/**
 * ModelNotFoundException.
 */
class ModelNotFoundException extends RecordNotFoundException
{
    /**
     * The affected model IDs.
     *
     * @var array
     */
    protected $ids;

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
     * @param  array  $ids
     * 
     * @return static
     */
    public function setModel($model, $ids = []): static
    {
        $this->model = $model;

        $this->ids = array_map(
            fn ($id) => $id instanceof BackedEnum ? $id->value : $id,
            Arr::wrap($ids)
        );

        $this->message = "No query results for model [{$model}]";

        if (count($this->ids) > 0) {
            $this->message .= ' '.implode(', ', $this->ids);
        } else {
            $this->message .= '.';
        }

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

    /**
     * Get the affected Erostrine model IDs.
     *
     * @return array
     */
    public function getIds(): array
    {
        return $this->ids;
    }
}