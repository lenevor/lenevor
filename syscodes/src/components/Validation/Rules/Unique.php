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

namespace Syscodes\Components\Validation\Rules;

use Syscodes\Components\Database\Erostrine\Model;
use Syscodes\Components\Support\Traits\Conditionable;
use Stringable;

/**
 * Get the Unique rule. 
 */
class Unique implements Stringable
{
    use Conditionable, DatabaseRule;

    /**
     * The ID that should be ignored.
     *
     * @var mixed
     */
    protected $ignore;

    /**
     * The name of the ID column.
     *
     * @var string
     */
    protected $idColumn = 'id';

    /**
     * Ignore the given ID during the unique check.
     *
     * @param  mixed  $id
     * @param  string|null  $idColumn
     * @return static
     */
    public function ignore($id, $idColumn = null): static
    {
        if ($id instanceof Model) {
            return $this->ignoreModel($id, $idColumn);
        }

        $this->ignore = $id;
        $this->idColumn = $idColumn ?? 'id';

        return $this;
    }

    /**
     * Ignore the given model during the unique check.
     *
     * @param  \Syscodes\Components\Database\Erostrine\Model  $model
     * @param  string|null  $idColumn
     * @return static
     */
    public function ignoreModel($model, $idColumn = null): static
    {
        $this->idColumn = $idColumn ?? $model->getKeyName();
        $this->ignore = $model->{$this->idColumn};

        return $this;
    }

    /**
     * Magic method.
     * 
     * Convert the rule to a validation string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return rtrim(sprintf('unique:%s,%s,%s,%s,%s',
            $this->table,
            $this->column,
            $this->ignore ? '"'.addslashes($this->ignore).'"' : 'NULL',
            $this->idColumn,
            $this->formatWheres()
        ), ',');
    }
}