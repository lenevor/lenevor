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
 * @copyright   Copyright (c) 2019 - 2021 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Encryption;

use RuntimeException;
use Syscodes\Components\Encryption\Exceptions\DecryptException;
use Syscodes\Components\Encryption\Exceptions\EncryptException;
use Syscodes\Components\Contracts\Encryption\Encrypter as EncrypterContract;

/**
 * Lenevor Encryption Manager.
 * 
 * This class determines the cipher and mode to use of encryption.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class Encrypter implements EncrypterContract
{
    /**
     * The algoritm used encryption.
     * 
     * @var string $cipher
     */
    protected $cipher;

    /**
     * The key / seed being used.
     * 
     * @var string $key
     */
    protected $key;

    /**
     * Constructor. Create a new Encrypter instance.
     * 
     * @param  string  $key
     * @param  string  $cipher
     * 
     * @return void
     * 
     * @throws \RuntimeException
     */
    public function __construct($key, $cipher = 'AES-128-CBC')
    {
        $this->key = (string) $key;
        
        if (static::supported($key, $cipher)) {
            $this->key    = $key;
            $this->cipher = $cipher;
        } else   {
            throw new RuntimeException('The only supported ciphers are AES-128-CBC and AES-256-CBC with the correct key lengths');
        }        
    }

    /**
     * {@inheritdoc}
     */
    public static function supported($key, $cipher): bool
    {
        $length = mb_strlen($key, '8bit');

        return ($cipher === 'AES-128-CBC' && $length === 16) ||
               ($cipher === 'AES-256-CBC' && $length === 32);
    }

    /**
     * {@inheritdoc}
     */
    public static function generateRandomKey($cipher): string
    {
        return random_bytes($cipher === 'AES-128-CBC' ? 16 : 32);
    }

    /**
     * {@inheritdoc}
     */
    public function encrypt($value, $serialize = true): string
    {
        $iv = random_bytes(openssl_cipher_iv_length($this->cipher));
        
        // Encrypt the given value
        $value = openssl_encrypt(
            $serialize ? serialize($value) : $value,
            $this->cipher, $this->key, 0, $iv
        );

        if (false === $value) {
            throw new EncryptException('Could not encrypt the data');
        }

        $iv   = base64_encode($iv);
        $hmac = $this->hash($iv, $value);
        $json = json_encode(compact('iv', 'value', 'hmac'));

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new EncryptException('Could not encrypt the data');
        }

        return base64_encode($json);
    }

    /**
     * Create a keyed has for the given value.
     * 
     * @param  string  $iv
     * @param  mixed  $value
     * 
     * @return string
     */
    protected function hash($iv, $value): string
    {
        return hash_hmac('sha256', $iv.$value, $this->key);
    }

    /**
     * {@inheritdoc}
     */
    public function encryptString($value): string
    {
        return $this->encrypt($value, false);
    }

    /**
     * {@inheritdoc}
     */
    public function decrypt($value, $unserialize = true)
    {
        $payload   = $this->getJsonPayload($value);
        $iv        = base64_decode($payload['iv']);
        $decrypted = openssl_decrypt(
                $payload['value'], $this->cipher, $this->key, 0, $iv
        );

        if (false === $decrypted) {
            throw new DecryptException('Could not decrypt the data');
        }

        return $unserialize ? unserialize($decrypted) : $decrypted;
    }

    /**
     * {@inheritdoc}
     */
    public function getJsonPayload($value)
    {
        $payload = json_decode(base64_decode($value), true);

        if ( ! $this->validPayload($payload)) {
            throw new DecryptException('The payload is invalid');
        }

        if ( ! $this->validHmac($payload)) {
            throw new DecryptException('The Hmac is invalid');
        }

        return $payload;
    }

    /**
     * Verify that the encryption payload is valid.
     * 
     * @param  mixed  $payload
     * 
     * @return bool
     */
    protected function validPayload($payload): bool
    {
        return is_array($payload) && isset($payload['iv'], $payload['value'], $payload['hmac']) && 
               strlen(base64_decode($payload['iv'], true)) === openssl_cipher_iv_length($this->cipher);
    }

    /**
     * Determine if the Hmac for the given payload is valid.
     * 
     * @param  array  $payload
     * 
     * @return bool
     */
    protected function validHmac(array $payload): bool
    {
        $calc = $this->calcHmac($payload, $bytes = random_bytes(16));

        return hash_equals(
                hash_hmac('sha256', $payload['hmac'], $bytes, true),
                $calc
        );
    }

    /**
     * Calculate the hash of the given payload.
     * 
     * @param  array  $payload
     * @param  string  $bytes
     * 
     * @return string
     */
    protected function calcHmac($payload, $bytes): string
    {
        return hash_hmac('sha256', $this->hash($payload['iv'], $payload['value']), $bytes, true);
    }

    /**
     * {@inheritdoc}
     */
    public function decryptString($value)
    {
        return $this->decrypt($value, false);
    }

    /**
     * {@inheritdoc}
     */
    public function getKey(): string
    {
        return $this->key;
    }
}