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

namespace Syscodes\Components\Console\View\Components;

use ReflectionClass;
use Syscodes\Components\Console\OutputStyle;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Helper\SymfonyQuestionHelper;

use function Termwind\render;
use function Termwind\renderUsing;

/**
 * Renders the given view.
 */
abstract class Component
{
    /**
     * The output interface implementation.
     * 
     * @var \Syscodes\Components\Console\OutputStyle
     */
    protected $output;

    /**
     * Constructor. Create a new Component class instance.
     * 
     * @param  \Syscodes\Components\Console\OutputStyle  $output
     * 
     * @return void
     */
    public function __construct($output)
    {
        $this->output = $output;
    }

    /**
     * Renders the given view.
     *
     * @param  string  $view
     * @param  \Syscodes\Components\Contracts\Support\Arrayable|array  $data
     * @param  int  $verbosity
     * 
     * @return void
     */
    protected function renderView($view, $data, $verbosity)
    {
        renderUsing($this->output);

        render((string) $this->compile($view, $data), $verbosity);
    }

    /**
     * Compile the given view contents.
     *
     * @param  string  $view
     * @param  array  $data
     * 
     * @return string
     */
    protected function compile($view, $data)
    {
        extract($data);

        ob_start();

        include __DIR__."/../../resources/views/components/$view.php";

        return take(ob_get_contents(), function () {
            ob_end_clean();
        });
    }

    /**
     * Eventually performs a question using the component's question helper.
     *
     * @param  callable  $callable
     * 
     * @return mixed
     */
    protected function usingQuestionHelper($callable)
    {
        $property = (new ReflectionClass(OutputStyle::class))
            ->getParentClass()
            ->getProperty('questionHelper');

        $currentHelper = $property->isInitialized($this->output)
            ? $property->getValue($this->output)
            : new SymfonyQuestionHelper();

        $property->setValue($this->output, new QuestionHelper);

        try {
            return $callable();
        } finally {
            $property->setValue($this->output, $currentHelper);
        }
    }
}