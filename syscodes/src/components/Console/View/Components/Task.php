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

namespace Syscodes\Components\Console\View\Components;

use Throwable;
use Symfony\Component\Console\Output\OutputInterface;
use Syscodes\Components\Console\View\Enums\TaskResult;
use Syscodes\Components\Support\InteractsWithTime;

use function Termwind\terminal;

/**
 * Renders the task component.
 */
class Task extends Component
{
    use InteractsWithTime;
    
    /**
     * Renders the component using the given arguments.
     * 
     * @param  string  $description
     * @param  (callable(): bool)|null  $task
     * @param  int  $verbosity
     * 
     * @return void
     */
    public function render($description, $task = null, $verbosity = OutputInterface::VERBOSITY_NORMAL)
    {
        $description = $this->mutate($description, [
            Mutators\EnsureDynamicContentHighlighted::class,
            Mutators\EnsureRelativePaths::class,
        ]);
        
        $descriptionWidth = mb_strlen(preg_replace("/\<[\w=#\/\;,:.&,%?]+\>|\\e\[\d+m/", '$1', $description) ?? '');
        
        $this->output->write("  $description ", false, $verbosity);
        
        $startTime = microtime(true);
        
        $result = TaskResult::Failure->value;
        
        try {
            $result = ($task ?: fn () => TaskResult::Success->value)();
        } catch (Throwable $e) {
            throw $e;
        } finally {
            $runTime = $task
                ? (' '.$this->runTimeForHumans($startTime))
                : '';
                
            $runTimeWidth = mb_strlen($runTime);
            $width = min(terminal()->width(), 150);
            $dots = max($width - $descriptionWidth - $runTimeWidth - 10, 0);

            $this->output->write(str_repeat('<fg=gray>.</>', $dots), false, $verbosity);

            $this->output->write("<fg=gray>$runTime</>", false, $verbosity);
            
            $this->output->writeln(
                match ($result) {
                    TaskResult::Failure->value => ' <fg=red;options=bold>FAIL</>',
                    TaskResult::Skipped->value => ' <fg=yellow;options=bold>SKIPPED</>',
                    default => ' <fg=green;options=bold>DONE</>'
                },
                $verbosity,
            );
        }
    }
}