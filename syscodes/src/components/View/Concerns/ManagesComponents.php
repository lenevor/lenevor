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

use Syscodes\Components\Support\WebString;
use Syscodes\Components\View\Exceptions\ViewException;

/**
 * Trait ManagesComponents.
 */
trait ManagesComponents
{
    /**
     * Component data.
     * 
     * @var array $components
     */
    protected $components = [];

    /**
     * Begin a components for rendered view.
     * 
     * @param  string  $view
     * @param  array  $data
     * 
     * @return array
     */
    public function beginComponent($view, array $data = [])
    {
        if (ob_start()) {
            $this->components[] = [
                'view' => $view,
                'data' => $data,
                'slots' => $data
            ];
        }
    }

    /**
     * Close and render component.
     * 
     * @return string
     */
    public function renderComponent()
    {
        $component = array_pop($this->components);

        if ( ! $component) {
            throw new ViewException('No active component in this block. Make sure you have open component using \'component\' method.');
        }

        return $this->make($component['view'], $this->getComponentData($component))->render();
    }

    /**
     * Get the data for the given component.
     * 
     * @return array
     */
    protected function getComponentData($component)
    {
        return array_merge(
            $component['data'],
            ['slot' => new WebString(trim(ob_get_clean()))], 
            $component['slots']
        );
    }

    /**
     * Begin the slot rendering.
     * 
     * @param  string  $name
     * @param  string|null  $content  
     * 
     * @return void
     */
    public function slot($name, $content = null)
    {
        if (func_num_args() > 2) {
            throw new ViewException("You passed too many arguments to the [$name] slot.");
        } elseif (func_num_args() === 2) {
            $this->components[$this->currentComponent()]['slots'][$name] = $content;
        } elseif (ob_start()) {
            $this->components[$this->currentComponent()]['slots'][] = $name;
        }
    }

    /**
     * Close slot and save the slot content for rendering.
     * 
     * @return void
     */
    public function endSlot()
    {
        last($this->components);

        $currentSlot = array_pop(
            $this->components[$this->currentComponent()]['slots']
        );

        if ( ! $currentSlot) {
            throw new ViewException('No active slot in this block. Make sure you have open slot using \'slot\' method.');
        }

        $this->components[$this->currentComponent()]['data'][$currentSlot] = new WebString(trim(ob_get_clean()));
    }

    /**
     * Get the index for the current component.
     * 
     * @return int
     */
    protected function currentComponent(): int
    {
        return count($this->components) - 1;
    }
}