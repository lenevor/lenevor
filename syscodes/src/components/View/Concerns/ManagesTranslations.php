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
 * @copyright   Copyright (c) 2019 - 2025 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\View\Concerns;

/**
 * Trait ManagesTranslations.
 */
trait ManagesTranslations
{
    /**
     * Gets the translation replacements.
     * 
     * @var array $replacements
     */
    protected $replacements = [];

    /**
     * Begin a translation block.
     * 
     * @param  array  $replacements
     * 
     * @return void
     */
    public function beginTranslation($replacements = []): void
    {
        ob_start();

        $this->replacements = $replacements;
    }

    /**
     * Render the translation.
     * 
     * @return string
     */
    public function renderTranslation(): string
    {
        return $this->container->make('translator')->getLine(
            trim(ob_get_clean()), $this->replacements
        );
    }
}