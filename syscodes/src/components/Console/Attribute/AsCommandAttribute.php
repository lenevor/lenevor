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

namespace Syscodes\Components\Console\Attribute;

/**
 * Service tag to autoconfigure commands.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class AsCommandAttribute
{
    /**
     * @param  string  $name  The name of the command
     * @param  string|null  $description  The description of the command
     * @param  string[]  $aliases  The list of aliases of the command
     * @param  bool  $hidden  If true, the command won't be shown when listing all the available commands
     * @param  string|null  $help  The help content of the command
     * @param  string[]  $usages  The list of usage examples
     * 
     * @return void
     */
    public function __construct(
        public string $name,
        public ?string $description = null,
        array $aliases = [],
        bool $hidden = false,
        public ?string $help = null,
        public array $usages = [],
    ) {
        if ( ! $hidden && ! $aliases) {
            return;
        }
        
        $name = explode('|', $name);
        $name = array_merge($name, $aliases);
        
        if ($hidden && '' !== $name[0]) {
            array_unshift($name, '');
        }
        
        $this->name = implode('|', $name);
    }
}