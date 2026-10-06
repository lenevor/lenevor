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

namespace Syscodes\Components\Database\Erostrine\Attributes;

use Attribute;

/**
 * Gets the table attribute.
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Table
{
    /**
     * Constructor. Create a new attribute instance.
     *
     * @param  string|null  $name
     * @param  string|null  $key
     * @param  string|null  $keyType
     * @param  bool|null  $incrementing
     * @param  bool|null  $timestamps
     * @param  string|null  $dateFormat
     * @return void
     */
    public function __construct(
        public ?string $name = null,
        public ?string $key = null,
        public ?string $keyType = null,
        public ?bool $incrementing = null,
        public ?bool $timestamps = null,
        public ?string $dateFormat = null,
    ) {
    }
}