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

namespace Syscodes\Components\Container\Attributes;

use Attribute;
use BackedEnum;
use InvalidArgumentException;
use UnitEnum;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class Bind
{
    /**
     * The id class to bind to.
     *
     * @var string
     */
    public string $id;

    /**
     * The environments the binding should apply for.
     *
     * @var array
     */
    public array $environments = [];

    /**
     * Constructor. Create a new attribute instance.
     *
     * @param  string  $id
     * @param  string|array|UnitEnum  $environments
     * 
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(string $id, string|array|UnitEnum $environments = ['*'])
    {
        $environments = array_filter(is_array($environments) ? $environments : [$environments]);

        if ($environments === []) {
            throw new InvalidArgumentException('The environment property must be set and cannot be empty.');
        }

        $this->id = $id;

        $this->environments = array_map(fn ($environment) => match (true) {
            $environment instanceof BackedEnum => $environment->value,
            $environment instanceof UnitEnum => $environment->name,
            default => $environment,
        }, $environments);
    }
}