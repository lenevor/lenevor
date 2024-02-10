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
 * @copyright   Copyright (c) 2019 - 2024 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Mail\Headers;

/**
 * Allow the base header for send of a message.
 */
abstract class BaseHeader
{
    /**
     * Get the name.
     * 
     * @var string $name
     */
    protected string $name;

    /**
     * Get the line lenght when send a message.
     * 
     * @var int $lineLenght
     */
    protected int $lineLength = 76;

    /**
     * Constructor. Create a new BaseHeader class instance.
     * 
     * @param  string  $name
     * 
     * @return void
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * Gets the name.
     * 
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set the max line lenght.
     * 
     * @param  int  $lineLenght
     * 
     * @return void
     */
    public function setMaxLineLength(int $lineLength): void
    {
        $this->lineLength = $lineLength;
    }
    
    /**
     * Get the max line lenght.
     * 
     * @return int
     */
    public function getMaxLineLength(): int
    {
        return $this->lineLength;
    }
    
    /**
     * Get the string.
     * 
     * @return string
     */
    public function toString(): string
    {
        return $this->tokensToString($this->toTokens());
    }

    /**
     * Generates tokens from the given string which include CRLF as individual tokens.
     * 
     * @param  string  $token
     *
     * @return string[]
     */
    protected function generateTokenLines(string $token): array
    {
        return preg_split('~(\r\n)~', $token, -1, PREG_SPLIT_DELIM_CAPTURE);
    }

    /**
     * Generate a list of all tokens in the final header.
     * 
     * @param  string|null  $string
     * 
     * @return array
     */
    protected function toTokens(?string $string = null): array
    {
        $string = $tring ?? $this->getBodyAsString();

        $tokens = [];

        foreach (preg_split('~(?=[ \t])~', $string) as $token) {
            $newTokens = $this->generateTokenLines($token);
            foreach ($newTokens as $newToken) {
                $tokens[] = $newToken;
            }
        }

        return $tokens;
    }

    /**
     * Takes an array of tokens which appear in the header.
     *
     * @param string[] $tokens
     * 
     * @return string
     */
    private function tokensToString(array $tokens): string
    {
        $lineCount = 0;
        $headerLines = [];
        $headerLines[] = $this->name.': ';
        $currentLine = &$headerLines[$lineCount++];

        foreach ($tokens as $i => $token) {
            if (("\r\n" === $token)
                || ($i > 0 && strlen($currentLine.$token) > $this->lineLength)
                && '' !== $currentLine) {
                $headerLines[] = '';
                $currentLine = &$headerLines[$lineCount++];
            }

            if ("\r\n" !== $token) {
                $currentLine .= $token;
            }
        }

        return implode("\r\n", $headerLines);
    }
}