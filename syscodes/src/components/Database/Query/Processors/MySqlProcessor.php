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
 * @copyright   Copyright (c) 2019 - 2024 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */
 
namespace Syscodes\Components\Database\Query\Processors;

/**
 * Allows show the results of a column listing query for Mysql Database.
 */
class MySqlProcessor extends Processor
{
    /**
     * Process the results of a column listing query.
     * 
     * @param  array  $results
     * 
     * @return array
     */
    public function processColumnListing($results): array
    {
        return array_map(fn ($result) => ((object) $result)->column_name, $results);
    }
}