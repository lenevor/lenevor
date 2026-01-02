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

namespace Syscodes\Components\Console\Concerns;

use Closure;

/**
 * The conformation in production.
 */
trait ConfirmProcess
{
    /**
     * Confirm before proceeding with the action.
     * 
     * This method only asks for confirmation in production.
     * 
     * @param  string  $warning
     * @param  \Closure|bool|null  $callback
     * 
     * @return bool
     */
    public function confirmToProceed($warning = 'Application In Production', $callback = null): bool
    {
        $callback = is_null($callback) ? $this->getDefaultConfirmCallback() : $callback;
        
        $shouldConfirm = value($callback);
        
        if ($shouldConfirm) {
            if ($this->hasOption('force') && $this->option('force')) {
                return true;
            }
            
            $this->lenevor->alert($warning);
            
            $confirmed = $this->components->confirm('Do you really wish to run this command?');
            
            if ( ! $confirmed) {
                $this->newLine();
                
                $this->components->warn('Command canceled.');
                
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Get the default confirmation callback.
     * 
     * @return Closure
     */
    protected function getDefaultConfirmCallback(): Closure
    {
        return fn () => $this->getLenevor()->environment() === 'production';
    }
}