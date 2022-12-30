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

namespace Syscodes\Components\Encryption;

use RuntimeException;
use Syscodes\Components\Support\Str;
use Syscodes\Components\Support\ServiceProvider;

/**
 * For loading the encrypter class from the container of services.
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
            
            return new Encrypter($this->parseKey($config), $config['cipher']);

        });
    }

    /**
     * Get parse the encryption key.
     * 
     * @param  array  $config
     * 
     * @return string
     */
    protected function parseKey(array $config): string
    {
        if (Str::startsWith($key = $this->key($config), $prefix = 'base64:')) {
            $key = base64_decode(Str::after($key, $prefix));
        }

        return $key;
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
            if (empty($key)) {
                throw new RuntimeException('No application encryption key has been specified.');
            }            
        });
    }
}