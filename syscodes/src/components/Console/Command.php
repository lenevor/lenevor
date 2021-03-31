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

namespace Syscodes\Console;

/**
 * Class Command.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class Command
{
    /**
     * The console command description.
     * 
     * @var string|null $description
     */
    protected $description;

    /**
     * The Lenevor application instance.
     * 
     * @var \Syscodes\Contracts\Core\Application $lenevor
     */
    protected $lenevor;

    /**
     * The console command name.
     * 
     * @var string $name
     */
    protected $name;

    /**
     * The name and signature of the console command.
     * 
     * @var string $signature
     */
    protected $signature;

    /**
     * Constructor. Create a new Command instance.
     * 
     * @return void
     */
    public function __construct()
    {
        
    }
}