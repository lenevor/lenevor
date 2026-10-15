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

namespace Syscodes\Components\Validation\Rules;

use InvalidArgumentException;
use Stringable;

/**
 * Allows set IP version.
 */
class IpAddress implements Stringable
{
    /**
     * Get the allowed IP version.
     * 
     * @var array
     */
    private array $allowedVersions = [4, 6];

    /**
     * Get the version.
     * 
     * @var int
     */
    protected ?int $version = null;

    /**
     * Set the IP version.
     *
     * @template TVersion of int
     *
     * @param  TVersion  $version
     * @return $this
     *
     * @phpstan-return  ($version is 4|6 ? static : never)
     *
     * @throws InvalidArgumentException
     */
    public function version(int $version): static
    {
        if (! in_array($version, $this->allowedVersions)) {
            throw new InvalidArgumentException(sprintf(
                'The provided IP version %d is invalid. Please use one of these: %s',
                $version,
                implode(', ', $this->allowedVersions),
            ));
        }

        $this->version = $version;

        return $this;
    }

    /**
     * Magic method.
     * 
     * Convert the rule to a validation string.
     *
     * @return 'ip'|'ipv4'|'ipv6'
     */
    public function __toString(): string
    {
        return $this->version === null ? 'ip' : "ipv{$this->version}";
    }
}