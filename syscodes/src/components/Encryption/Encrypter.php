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

namespace Syscodes\Encryption;

use RuntimeException;
use Syscodes\Encryption\Exceptions\DecryptException;
use Syscodes\Encryption\Exceptions\EncryptException;
use Syscodes\Contracts\Encryption\Encrypter as EncrypterContract;

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
            throw new RuntimeException('The only supported ciphers are AES-128-CBC and AES-256-CBC with the correct key lengths.');
        }
        
    }

    /**
     * Determine if the given key and cipher combination is valid.
     * 
     * @param  string  $key
     * @param  string  $cipher
     * 
     * @return bool
     */
    public static function supported($key, $cipher)
    {
        $length = mb_strlen($key, '8bit');

        return ($cipher === 'AES-128-CBC' && $length === 16) ||
               ($cipher === 'AES-256-CBC' && $length === 32);
    }

    /**
     * Generate the IV size for the cipher.
     * 
     * @param  string  $cipher
     * 
     * @return string
     */
    public static function generateRandomKey($cipher)
    {
        return random_bytes($cipher === 'AES-128-CBC' ? 16 : 32);
    }

    /**
     * Encrypt the given value.
     * 
     * @param  mixed  $value
     * @param  bool  $serialize  
     * 
     * @return string
     * 
     * @throws \Syscodes\Encryption\Exceptions\EncryptionException
     */
    public function encrypt($value, $serialize = true)
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
    protected function hash($iv, $value)
    {
        return hash_hmac('sha256', $iv.$value, $this->key);
    }

    /**
     * Encrypt the given string without serialization.
     * 
     * @param  string  $value
     * 
     * @return string
     */
    public function encryptString($value)
    {
        return $this->encrypt($value, false);
    }

    /**
     * Decrypt the given value.
     * 
     * @param  string  $value
     * @param  bool  $unserialize  
     * 
     * @return mixed
     * 
     * @throws \Syscodes\Encryption\Enxceptions\DecryptException
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
     * Gets the JSON array from the given payload.
     * 
     * @param  string  $value
     * 
     * @return array
     * 
     * @throws \Syscodes\Encryption\Enxceptions\DecryptException
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
    protected function validPayload($payload)
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
    protected function validHmac(array $payload)
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
    protected function calcHmac($payload, $bytes)
    {
        return hash_hmac('sha256', $this->hash($payload['iv'], $payload['value']), $bytes, true);
    }

    /**
     * Decrypt the given string without unserialization.
     * 
     * @param  string  $value
     * 
     * @return string
     * 
     * @throws \Syscodes\Encryption\Enxceptions\DecryptException
     */
    public function decryptString($value)
    {
        return $this->decrypt($value, false);
    }

    /**
     * Gets the encryption key.
     * 
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }
}