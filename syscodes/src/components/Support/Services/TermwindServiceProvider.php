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

declare(strict_types=1);

namespace Syscodes\Components\Support\Services;

use Syscodes\Components\Console\OutputStyle;
use Syscodes\Components\Support\ServiceProvider;
use Termwind\Termwind;

/**
 * The service provider that generates message custom in console
 * for the Lenevor Framework.
 */
final class TermwindServiceProvider extends ServiceProvider
{
    /**
     * Sets the correct renderer to be used.
     * 
     * @return void
     */
    public function register()
    {
        $this->app->resolving(OutputStyle::class, function ($style) {
            Termwind::renderUsing($style->getOutput());
        });
    }
}