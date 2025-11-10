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

namespace Syscodes\Components\Core\Console\Commands;

use Closure;
use Exception;
use ReflectionFunction;
use Syscodes\Components\Console\Command;
use Syscodes\Components\Contracts\Console\Input\Input as InputInterface;
use Syscodes\Components\Contracts\Console\Output\Output as OutputInterface;

/**
 * Executes the command with closure.
 */
class ClosureCommand extends Command
{
    /**
     * The command callback.
     *
     * @var \Closure $callback
     */
    protected $callback;

    /**
     * Constructor. Create a new command instance.
     *
     * @param  string  $signature
     * @param  \Closure  $callback
     * 
     * @return void
     */
    public function __construct($signature, Closure $callback)
    {
        $this->callback = $callback;
        $this->signature = $signature;

        parent::__construct();
    }

    /** Executes the current command.
     * 
     * @param  \Syscodes\Components\Contracts\Console\Input\Input  $input
     * @param  \Syscodes\Components\Contracts\Console\Output\Output  $output
     * 
     * @return int
     * 
     * @throws \LogicException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int 
    {
        $inputs = array_merge($input->getArguments(), $input->getOptions());

        $parameters = [];

        foreach ((new ReflectionFunction($this->callback))->getParameters() as $parameter) {
            if (isset($inputs[$parameter->name])) {
                $parameters[$parameter->name] = $inputs[$parameter->name];
            }
        }

        try {
            return $this->lenevor->call(
                $this->callback->bindTo($this, $this), $parameters
            );
        } catch (Exception $e) {
            $this->error($e->getMessage());

            return 0;
        }
    }

    /**
     * Set the description for the command.
     *
     * @param  string  $description
     * 
     * @return static
     */
    public function describe($description): static
    {
        $this->setDescription($description);

        return $this;
    }
}