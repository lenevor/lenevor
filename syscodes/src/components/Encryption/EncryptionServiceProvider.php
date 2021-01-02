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
 * @copyright   Copyright (c) 2019-2021 Lenevor Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.5.0
 */

namespace Syscodes\Encryption;

use RuntimeException;
use Syscodes\Support\Str;
use Syscodes\Support\ServiceProvider;

/**
 * For loading the encrypter class from the container of services.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
class EncryptionServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     * 
     * @return void
     */
    public function register()
    {
        $this->app->singleton('encrypter', function ($app) {
            
            $config = $app->make('config')->get('security');

            if (Str::startsWith($key = $this->key($config), 'base64:'))
            {
                $key = base64_decode(substr($key, 7));
            }
            
            return new Encrypter($key, $config['cipher']);

        });
    }

    /**
     * Extract the encryption key from the given configuration.
     * 
     * @param  array  $config
     * 
     * @return string
     * 
     * @return \RuntimeException
     */
    protected function key(array $config)
    {
        return take($config['key'], function ($key) {

            if (empty($key))
            {
                throw new RuntimeException('No application encryption key has been specified.');
            }
            
        });
    }
}