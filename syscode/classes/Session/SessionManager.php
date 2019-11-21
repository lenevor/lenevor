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
 * @since       0.4.0
 */

namespace Syscode\Session;

use Syscode\Support\Manager;
use Syscode\Session\Handlers\FileSession;

/**
 * Lenevor session storage.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
class SessionManager extends Manager
{
    /**
     * Create an instance of the file session driver.
     * 
     * @return \Illuminate\Session\Store
     */
    protected function createFileDriver()
    {
        $lifetime = $this->config->get('session.lifetime');
        $path     = $this->config->get('session.files');

        return $this->buildSession(new FileSession(
                $this->app->make('files'), $path, $lifetime
        ));
    }

    /**
     * Build the session instance.
     * 
     * @param  \SessionHandlerInterface  $handler
     * 
     * @return \Syscode\Session\Store
     */
    protected function buildSession($handler)
    {
        return new Store($this->config->get('session.cookie'), $handler);
    }

    /**
     * Get the default session driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->config->get('session.driver');
    }
}