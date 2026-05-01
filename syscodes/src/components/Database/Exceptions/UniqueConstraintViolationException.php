<?php

/**
 * Lenevor PHP Framework
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

namespace Syscodes\Components\Database\Exceptions;

/**
 * Unique contraint violation exception.
 */
class UniqueConstraintViolationException extends QueryException
{
    /**
     * The unique index which prevented the query.
     *
     * @var string|null
     */
    public ?string $index = null;

    /**
     * The columns which caused the violation.
     *
     * @var array
     */
    public array $columns = [];

    /**
     * Set the unique index which caused the violation.
     *
     * @param  string|null  $index
     * 
     * @return self
     */
    public function setIndex(?string $index): self
    {
        $this->index = $index;

        return $this;
    }

    /**
     * Set the columns that caused the violation.
     *
     * @param  array  $columns
     * 
     * @return self
     */
    public function setColumns(array $columns): self
    {
        $this->columns = $columns;

        return $this;
    }
}