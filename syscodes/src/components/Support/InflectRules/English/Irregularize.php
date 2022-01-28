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
 * @copyright   Copyright (c) 2019 - 2022 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Support\InflectRules\English;

/**
 * This class allows identify words in irregular plural.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
final class Irregularize
{
    /**
     * Get the words irregular plural.
     * 
     * @return array
     */
    public static function all(): array
    {
        return [
            ['I', 'we'],
            ['me', 'us'],
            ['he', 'they'],
            ['she', 'they'],
            ['them', 'them'],
            ['myself', 'ourselves'],
            ['yourself', 'yourselves'],
            ['itself', 'themselves'],
            ['herself', 'themselves'],
            ['himself', 'themselves'],
            ['themself', 'themselves'],
            ['is', 'are'],
            ['was', 'were'],
            ['has', 'have'],
            ['this', 'these'],
            ['that', 'those'],
            ['echo', 'echoes'],
            ['dingo', 'dingoes'],
            ['volcano', 'volcanoes'],
            ['tornado', 'tornadoes'],
            ['torpedo', 'torpedoes'],
            ['genus', 'genera'],
            ['viscus', 'viscera'],
            ['stigma', 'stigmata'],
            ['stoma', 'stomata'],
            ['dogma', 'dogmata'],
            ['lemma', 'lemmata'],
            ['schema', 'schemata'],
            ['anathema', 'anathemata'],
            ['ox', 'oxen'],
            ['axe', 'axes'],
            ['die', 'dice'],
            ['yes', 'yeses'],
            ['foot', 'feet'],
            ['eave', 'eaves'],
            ['goose', 'geese'],
            ['tooth', 'teeth'],
            ['quiz', 'quizzes'],
            ['human', 'humans'],
            ['proof', 'proofs'],
            ['carve', 'carves'],
            ['valve', 'valves'],
            ['looey', 'looies'],
            ['thief', 'thieves'],
            ['groove', 'grooves'],
            ['pickaxe', 'pickaxes'],
            ['passerby', 'passersby'],
            ['cookie', 'cookies'],
        ];
    }
}