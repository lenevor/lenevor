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

namespace Syscodes\Components\Encryption;

use RuntimeException;
use Syscodes\Components\Contracts\Encryption\Encrypter as EncrypterContract;
use Syscodes\Components\Contracts\Encryption\StringEncrypter;
use Syscodes\Components\Encryption\Exceptions\DecryptException;
use Syscodes\Components\Encryption\Exceptions\EncryptException;

/**
 * Lenevor Encryption Manager.
 * 
 * This class determines the cipher and mode to use of encryption.
 */
class Encrypter implements EncrypterContract, StringEncrypter
{
    /**
     * The algoritm used encryption.
     * 
     * @var string
     */
    protected $cipher;

    /**
     * The key / seed being used.
     * 
     * @var string
     */
    protected $key;

    /**
     * The previous / legacy encryption keys.
     *
     * @var array
     */
    protected $previousKeys = [];

    /**
     * The supported cipher algorithms and their properties.
     *
     * @var array
     */
    private static $supportedCiphers = [
        'aes-128-cbc' => ['size' => 16, 'aead' => false],
        'aes-256-cbc' => ['size' => 32, 'aead' => false],
        'aes-128-gcm' => ['size' => 16, 'aead' => true],
        'aes-256-gcm' => ['size' => 32, 'aead' => true],
    ];

    /**
     * Constructor. Create a new Encrypter instance.
     * 
     * @param  string  $key
     * @param  string  $cipher 
     * @return void
     * 
     * @throws \RuntimeException
     */
    public function __construct($key, $cipher = 'AES-128-CBC')
    {
        $key = (string) $key;
        
        if ( ! static::supported($key, $cipher)) {
            $ciphers = implode(', ', array_keys(self::$supportedCiphers));

            throw new RuntimeException("Unsupported cipher or incorrect key length. Supported ciphers are: {$ciphers}.");
        }

        $this->key = $key;
        $this->cipher = $cipher;
    }

    /**
     * Determine if the given key and cipher combination is valid.
     * 
     * @param  string  $key
     * @param  string  $cipher 
     * @return bool
     */
    public static function supported($key, $cipher): bool
    {
        if (! isset(self::$supportedCiphers[strtolower($cipher)])) {
            return false;
        }

        return mb_strlen($key, '8bit') === self::$supportedCiphers[strtolower($cipher)]['size'];
    }

    /**
     * Generate the IV size for the cipher.
     * 
     * @param  string  $cipher 
     * @return string
     */
    public static function generateRandomKey($cipher): string
    {
        return random_bytes(self::$supportedCiphers[strtolower($cipher)]['size'] ?? 32);
    }

    /**
     * Encrypt the given value.
     * 
     * @param  mixed  $value
     * @param  bool  $serialize 
     * @return string
     * 
     * @throws \Syscodes\Components\Encryption\Exceptions\EncryptException
     */
    public function encrypt(#[\SensitiveParameter] $value, $serialize = true): string
    {
        $iv = random_bytes(openssl_cipher_iv_length($this->cipher));
        
        // Encrypt the given value
        $value = openssl_encrypt(
            $serialize ? serialize($value) : $value,
            strtolower($this->cipher), $this->key, 0, $iv, $tag
        );

        if (false === $value) {
            throw new EncryptException('Could not encrypt the data');
        }

        $iv = base64_encode($iv);
        $tag = base64_encode($tag ?? '');

        $mac = self::$supportedCiphers[strtolower($this->cipher)]['aead']
            ? '' // For AEAD-algorithms, the tag / MAC is returned by openssl_encrypt...
            : $this->hash($iv, $value, $this->key);

        $json = json_encode(['iv' => $iv, 'value' => $value, 'mac' => $mac, 'tag' => $tag], JSON_UNESCAPED_SLASHES);

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
     * @param  string  $key 
     * @return string
     */
    protected function hash(#[\SensitiveParameter] $iv, #[\SensitiveParameter] $value, #[\SensitiveParameter] $key): string
    {
        return hash_hmac('sha256', $iv.$value, $key);
    }

    /**
     * Encrypt the given string without serialization.
     * 
     * @param  string  $value 
     * @return string
     * 
     * @throws \Syscodes\Components\Encryption\Exceptions\EncryptException
     */
    public function encryptString(#[\SensitiveParameter] $value): string
    {
        return $this->encrypt($value, false);
    }

    /**
     * Encrypt the given value.
     * 
     * @param  string  $payload
     * @param  bool  $unserialize 
     * @return mixed
     * 
     * @throws \Syscodes\Components\Encryption\Exceptions\DecryptException
     */
    public function decrypt($payload, $unserialize = true)
    {
        $decrypted = '';

        $payload = $this->getJsonPayload($payload);

        $iv = base64_decode($payload['iv']);

        $this->ensureTagIsValid(
            $tag = empty($payload['tag']) ? null : base64_decode($payload['tag'])
        );

        [$keys, $validKey] = [$this->getAllKeys(), null];

        // Here we will decrypt the value. If we are able to successfully decrypt it
        // we will then unserialize it and return it out to the caller.
        foreach ($keys as $key) {
            if ($this->shouldValidateMac()) {
                $validMac = $this->validMacForKey($payload, $key);

                if ($validMac && $validKey === null) {
                    $validKey = $key;
                }

                continue;
            }

            $decrypted = \openssl_decrypt(
                $payload['value'], strtolower($this->cipher), $key, 0, $iv, $tag ?? ''
            );

            if ($decrypted !== false) {
                break;
            }
        }

        if ($this->shouldValidateMac() && $validKey === null) {
            throw new DecryptException('The MAC is invalid.');
        }

        if ($this->shouldValidateMac()) {
            $decrypted = \openssl_decrypt(
                $payload['value'], strtolower($this->cipher), $validKey, 0, $iv, $tag ?? ''
            );
        }

        if (($decrypted ?? false) === false) {
            throw new DecryptException('Could not decrypt the data.');
        }

        return $unserialize ? unserialize($decrypted) : $decrypted;
    }

    /**
     * Decrypt the given string without unserialization.
     * 
     * @param  string  $payload 
     * @return string
     * 
     * @throws \Syscodes\Components\Encryption\Exceptions\DecryptException
     */
    public function decryptString($payload)
    {
        return $this->decrypt($payload, false);
    }

    /**
     * Gets the JSON array from the given payload.
     * 
     * @param  string  $payload 
     * @return array
     * 
     * @throws \Syscodes\Components\Encryption\Exceptions\DecryptException
     */
    public function getJsonPayload($payload)
    {
        if ( ! is_string($payload)) {
            throw new DecryptException('The payload is invalid.');
        }

        $payload = json_decode(base64_decode($payload), true);

        if ( ! $this->validPayload($payload)) {
            throw new DecryptException('The payload is invalid.');
        }
        
        return $payload;
        }
        
        /**
     * Verify that the encryption payload is valid.
     * 
     * @param  mixed  $payload 
     * @return bool
     */
    protected function validPayload($payload): bool
    {
        if ( ! is_array($payload)) {
            return false;
        }

        foreach (['iv', 'value', 'mac'] as $item) {
            if ( ! isset($payload[$item]) || ! is_string($payload[$item])) {
                return false;
            }
        }

        if (isset($payload['tag']) && ! is_string($payload['tag'])) {
            return false;
        }

        return strlen(base64_decode($payload['iv'], true)) === openssl_cipher_iv_length(strtolower($this->cipher));
    }

    /**
     * Determine if the MAC for the given payload is valid for the primary key.
     * 
     * @param  array  $payload 
     * @return bool
     */
    protected function validMac(array $payload): bool
    {
        return $this->validMacForKey($payload, $this->key);
    }

    /**
     * Determine if the MAC is valid for the given payload and key.
     *
     * @param  array  $payload
     * @param  string  $key 
     * @return bool
     */
    protected function validMacForKey(#[\SensitiveParameter] $payload, $key): bool
    {
        return hash_equals(
            $this->hash($payload['iv'], $payload['value'], $key), $payload['mac']
        );
    }

     /**
     * Ensure the given tag is a valid tag given the selected cipher.
     *
     * @param  string  $tag 
     * @return void
     *
     * @throws \Syscodes\Components\Encryption\Exceptions\DecryptException
     */
    protected function ensureTagIsValid($tag): void
    {
        if (self::$supportedCiphers[strtolower($this->cipher)]['aead'] && strlen($tag) !== 16) {
            throw new DecryptException('Could not decrypt the data.');
        }

        if ( ! self::$supportedCiphers[strtolower($this->cipher)]['aead'] && is_string($tag)) {
            throw new DecryptException('Unable to use tag because the cipher algorithm does not support AEAD.');
        }
    }

    /**
     * Determine if we should validate the MAC while decrypting.
     *
     * @return bool
     */
    protected function shouldValidateMac(): bool
    {
        return ! self::$supportedCiphers[strtolower($this->cipher)]['aead'];
    }

    /**
     * Gets the encryption key.
     * 
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Get the current encryption key and all previous encryption keys.
     *
     * @return array
     */
    public function getAllKeys(): array
    {
        return [$this->key, ...$this->previousKeys];
    }

    /**
     * Get the previous encryption keys.
     *
     * @return array
     */
    public function getPreviousKeys(): array
    {
        return $this->previousKeys;
    }

    /**
     * Set the previous / legacy encryption keys that should be utilized if decryption fails.
     *
     * @param  array  $keys 
     * @return static
     *
     * @throws \RuntimeException
     */
    public function previousKeys(array $keys): static
    {
        foreach ($keys as $key) {
            if ( ! static::supported($key, $this->cipher)) {
                $ciphers = implode(', ', array_keys(self::$supportedCiphers));

                throw new RuntimeException("Unsupported cipher or incorrect key length. Supported ciphers are: {$ciphers}.");
            }
        }

        $this->previousKeys = $keys;

        return $this;
    }
}