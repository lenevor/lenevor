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

namespace Syscodes\Components\Core\Exceptions\Debugging;

use Syscodes\Components\Debug\Handlers\PleasingPageHandler;

use function take;

/**
 * Creates a new Debug PleasingPagehandler instance.
 */
class DebugHandler
{
    /**
     * Create a new Debug handler for debug mode.
     * 
     * @return \Syscodes\Components\Debug\Handlers\PleasingPageHandler
     */
    public function initDebug()
    {
        return take(new PleasingPageHandler, fn ($handler) => $this->registerEditor($handler));
    }

    /**
     * Register the editor with the handler.
     *
     * @param  \Syscodes\Components\Debug\Handlers\PleasingPageHandler $handler
     * 
     * @return static
     */
    protected function registerEditor($handler): static
    {
        if (config('gdebug.editor', false)) {
            $handler->setEditor(config('gdebug.editor'));
        }

        return $this;
    }
}