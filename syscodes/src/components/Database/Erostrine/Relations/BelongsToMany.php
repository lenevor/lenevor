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
 * @copyright   Copyright (c) 2019 - 2022 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Database\Erostrine\Relations;

use Syscodes\Components\Database\Erostrine\Model;
use Syscodes\Components\Database\Erostrine\Builder;
use Syscodes\Components\Database\Erostrine\Collection;

/**
 * Relation belongToMany given on the parent model.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class BelongsToMany extends Relation
{
    /**
     * The intermediate table for the relation.
     * 
     * @var string $table
     */
    protected $table;

    /**
     * {@inheritdoc}
     */
    public function getResults()
    {

    }

    /**
     * {@inheritdoc}
     */
    public function addConstraints(): void
    {

    }

    /**
     * {@inheritdoc}
     */
    public function addEagerConstraints(array $models): void
    {

    }

    /**
     * {@inheritdoc}
     */
    public function initRelation(array $models, $relation): array
    {
        return $models;
    }

    /**
     * {@inheritdoc}
     */
    public function match(array $models, Collection $results, $relation): array
    {
        return $models;
    }
}