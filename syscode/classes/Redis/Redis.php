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
 * @since       0.3.0
 */

namespace Syscode\Redis;

use Predis\Client;

/**
 * Redis cache handler.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
class Redis
{
    /**
     * The host address of the database.
     * 
     * @var array $clients
     */
    protected $clients;

    /**
     * Constructor. The new Redis connection instance.
     * 
     * @param  array  $servers
     * 
     * @return void
     */
    public function __construct(array $servers = [])
    {
        if (isset($servers['cluster']) && $servers['cluster'])
        {
            $this->clients = $this->createAggregateClient($servers);
        }
        else
        {
            $this->clients = $this->createSingleClient($servers);
        }
    }

    /**
     * Create a new aggregate client supporting sharding.
     * 
     * @param  array  $servers
     * 
     * @return array
     */
    protected function createAggregateclient(array $servers)
    {

    }

    /**
     * Create an array of single connection clients.
     * 
     * @param  array  $servers
     * 
     * @return array
     */
    protected function createSingleClient(array $servers)
    {
        
    }
}