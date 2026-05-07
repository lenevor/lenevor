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

namespace Syscodes\Components\Database\Schema;

use Syscodes\Components\Support\Flowing;

/**
 * @method ForeignKeyDefinition deferrable(bool $value = true) Set the foreign key as deferrable (PostgreSQL)
 * @method ForeignKeyDefinition initiallyImmediate(bool $value = true) Set the default time to check the constraint (PostgreSQL)
 * @method ForeignKeyDefinition lock(('none'|'shared'|'default'|'exclusive') $value) Specify the DDL lock mode for the foreign key operation (MySQL)
 * @method ForeignKeyDefinition on(string $table) Specify the referenced table
 * @method ForeignKeyDefinition onDelete(string $action) Add an ON DELETE action
 * @method ForeignKeyDefinition onUpdate(string $action) Add an ON UPDATE action
 * @method ForeignKeyDefinition references(string|string[] $columns) Specify the referenced column(s)
 */
class ForeignKeyDefinition extends Flowing
{
    /**
     * Indicate that updates should cascade.
     *
     * @return static
     */
    public function cascadeOnUpdate()
    {
        return $this->onUpdate('cascade');
    }

    /**
     * Indicate that updates should be restricted.
     *
     * @return static
     */
    public function restrictOnUpdate()
    {
        return $this->onUpdate('restrict');
    }

    /**
     * Indicate that updates should set the foreign key value to null.
     *
     * @return static
     */
    public function nullOnUpdate()
    {
        return $this->onUpdate('set null');
    }

    /**
     * Indicate that updates should have "no action".
     *
     * @return static
     */
    public function noActionOnUpdate()
    {
        return $this->onUpdate('no action');
    }

    /**
     * Indicate that deletes should cascade.
     *
     * @return static
     */
    public function cascadeOnDelete()
    {
        return $this->onDelete('cascade');
    }

    /**
     * Indicate that deletes should be restricted.
     *
     * @return static
     */
    public function restrictOnDelete()
    {
        return $this->onDelete('restrict');
    }

    /**
     * Indicate that deletes should set the foreign key value to null.
     *
     * @return static
     */
    public function nullOnDelete()
    {
        return $this->onDelete('set null');
    }

    /**
     * Indicate that deletes should have "no action".
     *
     * @return static
     */
    public function noActionOnDelete()
    {
        return $this->onDelete('no action');
    }
}