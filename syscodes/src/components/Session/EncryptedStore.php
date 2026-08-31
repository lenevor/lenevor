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

namespace Syscodes\Components\Session;

use SessionHandlerInterface;
use Syscodes\Components\Contracts\Encryption\Encrypter as EncrypterContract;
use Syscodes\Components\Encryption\Exceptions\DecryptException;

/**
 * Session handler using encrypted system for storage.
 */
class EncryptedStore extends Store
{
    /**
     * The encrypter instance.
     *
     * @var \Syscodes\Components\Contracts\Encryption\Encrypter
     */
    protected $encrypter;

    /**
     * Constructor. Create a new session instance.
     *
     * @param  string  $name
     * @param  \SessionHandlerInterface  $handler
     * @param  \Syscodes\Components\Contracts\Encryption\Encrypter  $encrypter
     * @param  string|null  $id
     * @param  string  $serialization
     * @return void
     */
    public function __construct($name, SessionHandlerInterface $handler, EncrypterContract $encrypter, $id = null, $serialization = 'php')
    {
        $this->encrypter = $encrypter;

        parent::__construct($name, $handler, $id, $serialization);
    }

    /**
     * Prepare the raw string data from the session for unserialization.
     *
     * @param  string  $data
     * @return string
     */
    protected function prepareForUnserialize($data): string
    {
        try {
            return $this->encrypter->decrypt($data);
        } catch (DecryptException) {
            return $this->serialization === 'json' ? json_encode([]) : serialize([]);
        }
    }

    /**
     * Prepare the serialized session data for storage.
     *
     * @param  string  $data
     * @return string
     */
    protected function prepareForStorage($data): string
    {
        return $this->encrypter->encrypt($data);
    }

    /**
     * Get the encrypter instance.
     *
     * @return \Syscodes\Components\Contracts\Encryption\Encrypter
     */
    public function getEncrypter()
    {
        return $this->encrypter;
    }
}