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

namespace Syscodes\Components\View\Transpilers\Concerns;

/**
 * Trait TranspilesAuthorizations.
 */
trait TranspilesAuthorizations
{
    /**
     * Transpile the can statements into valid PHP.
     *
     * @param  string  $expression
     * @return string
     */
    protected function transpileCan($expression): string
    {
        return "<?php if (app(\Syscodes\\Components\\Contracts\\Auth\\Access\\Gate::class)->check{$expression}): ?>";
    }

    /**
     * Transpile the cannot statements into valid PHP.
     *
     * @param  string  $expression
     * @return string
     */
    protected function transpileCannot($expression): string
    {
        return "<?php if (app(\Syscodes\\Components\\Contracts\\Auth\\Access\\Gate::class)->denies{$expression}): ?>";
    }

    /**
     * Transpile the canany statements into valid PHP.
     *
     * @param  string  $expression
     * @return string
     */
    protected function transpileCanany($expression): string
    {
        return "<?php if (app(\Syscodes\\Components\\Contracts\\Auth\\Access\\Gate::class)->any{$expression}): ?>";
    }

    /**
     * Transpile the else-can statements into valid PHP.
     *
     * @param  string  $expression
     * @return string
     */
    protected function transpileElsecan($expression): string
    {
        return "<?php elseif (app(\Syscodes\\Components\\Contracts\\Auth\\Access\\Gate::class)->check{$expression}): ?>";
    }

    /**
     * Transpile the else-cannot statements into valid PHP.
     *
     * @param  string  $expression
     * @return string
     */
    protected function transpileElsecannot($expression): string
    {
        return "<?php elseif (app(\Syscodes\\Components\\Contracts\\Auth\\Access\\Gate::class)->denies{$expression}): ?>";
    }

    /**
     * Transpile the else-canany statements into valid PHP.
     *
     * @param  string  $expression
     * @return string
     */
    protected function transpileElsecanany($expression): string
    {
        return "<?php elseif (app(\Syscodes\\Components\\Contracts\\Auth\\Access\\Gate::class)->any{$expression}): ?>";
    }

    /**
     * Transpile the end-can statements into valid PHP.
     *
     * @return string
     */
    protected function transpileEndcan(): string
    {
        return '<?php endif; ?>';
    }

    /**
     * Transpile the end-cannot statements into valid PHP.
     *
     * @return string
     */
    protected function transpileEndcannot(): string
    {
        return '<?php endif; ?>';
    }

    /**
     * Transpile the end-canany statements into valid PHP.
     *
     * @return string
     */
    protected function transpileEndcanany(): string
    {
        return '<?php endif; ?>';
    }
}