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

namespace Syscodes\Components\Database\Migrations;

/**
 * Implements the functions that allow generate a migration to database.
 */
interface MigrationRepositoryInterface
{
    /**
     * Get the completed migrations.
     *
     * @return string[]
     */
    public function getRan(): array;

    /**
     * Get the list of migrations.
     *
     * @param  int  $limit
     * 
     * @return array
     */
    public function getMigrations($limit): array;

    /**
     * Get the list of the migrations by batch.
     *
     * @param  int  $batch
     * 
     * @return array
     */
    public function getMigrationsByBatch($batch): array;

    /**
     * Get the last migration batch.
     *
     * @return array
     */
    public function getLast(): array;

    /**
     * Get the completed migrations with their batch numbers.
     *
     * @return array
     */
    public function getMigrationBatches(): array;

    /**
     * Log that a migration was run.
     *
     * @param  string  $file
     * @param  int  $batch
     * 
     * @return void
     */
    public function log($file, $batch);

    /**
     * Remove a migration from the log.
     *
     * @param  object
     * 
     * @return void
     */
    public function delete($migration);

    /**
     * Get the next migration batch number.
     *
     * @return int
     */
    public function getNextBatchNumber();

    /**
     * Create the migration repository data store.
     *
     * @return void
     */
    public function createRepository();

    /**
     * Determine if the migration repository exists.
     *
     * @return bool
     */
    public function repositoryExists();

    /**
     * Delete the migration repository data store.
     *
     * @return void
     */
    public function deleteRepository();

    /**
     * Set the information source to gather data.
     *
     * @param  string  $name
     * 
     * @return void
     */
    public function setSource($name);
}