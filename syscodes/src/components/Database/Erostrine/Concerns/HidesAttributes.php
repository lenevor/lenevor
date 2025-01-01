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

namespace Syscodes\Components\Database\Erostrine\Concerns;

/**
 * Trait HidesAttributes.
 */
trait HidesAttributes
{
    /**
     * The attributes that should be hidden for arrays.
     * 
     * @var array $hidden
     */
    protected $hidden = [];

    /**
     * The attributes that should be visible for arrays.
     * 
     * @var array $visible
     */
    protected $visible = [];

    /**
     * Get the hidden attributes for the model.
     * 
     * @return array
     */
    public function getHidden(): array
    {
        return $this->hidden;
    }

    /**
     * Set the hidden attributes for the model.
     * 
     * @param  array  $hidden
     * 
     * @return static
     */
    public function setHidden(array $hidden): static
    {
        $this->hidden = $hidden;

        return $this;
    }

    /**
     * Get the visible attributes for the model.
     * 
     * @return array
     */
    public function getVisible(): array
    {
        return $this->visible;
    }

    /**
     * Set the visible attributes for the model.
     * 
     * @param  array  $visible
     * 
     * @return static
     */
    public function setVisible(array $visible): static
    {
        $this->visible = $visible;

        return $this;
    }
}