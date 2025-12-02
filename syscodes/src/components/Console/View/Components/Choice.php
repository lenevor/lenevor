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

use Symfony\Component\Console\Question\ChoiceQuestion;

/**
 * Renders the choice component.
 */
class Choice extends Component
{
    /**
     * Renders the component using the given arguments.
     *
     * @param  string  $question
     * @param  array<array-key, string>  $choices
     * @param  mixed  $default
     * @param  int|null  $attempts
     * @param  bool  $multiple
     * 
     * @return mixed
     */
    public function render($question, $choices, $default = null, $attempts = null, $multiple = false)
    {
        return $this->usingQuestionHelper(
            fn () => $this->output->askQuestion(
                $this->getChoiceQuestion($question, $choices, $default)
                    ->setMaxAttempts($attempts)
                    ->setMultiselect($multiple)
            ),
        );
    }

    /**
     * Get a ChoiceQuestion instance that handles array keys like Prompts.
     *
     * @param  string  $question
     * @param  array  $choices
     * @param  mixed  $default
     * 
     * @return \Symfony\Component\Console\Question\ChoiceQuestion
     */
    protected function getChoiceQuestion($question, $choices, $default)
    {
        return new class($question, $choices, $default) extends ChoiceQuestion
        {
            protected function isAssoc(array $array): bool
            {
                return ! array_is_list($array);
            }
        };
    }
}