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
 * This class allows identify words in plural.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
final class Pluralize
{
    /**
     * Get the words in plural.
     * 
     * @return array
     */
    public static function all(): array
    {
        return [
            ['/s?$/i', 's'],
            ['/([^aeiou]ese)$/i', '$1'],
            ['/(ax|test)is$/i', '$1es'],
            ['/(alias|[^aou]us|t[lm]as|gas|ris)$/i', '$1es'],
            ['/(e[mn]u)s?$/i', '$1s'],
            ['/([^l]ias|[aeiou]las|[ejzr]as|[iu]am)$/i', '$1'],
            ['/(alumn|syllab|vir|radi|nucle|fung|cact|stimul|termin|bacill|foc|uter|loc|strat)(?:us|i)$/i', '$1i'],
            ['/(alumn|alg|vertebr)(?:a|ae)$/i', '$1ae'],
            ['/(seraph|cherub)(?:im)?$/i', '$1im'],
            ['/(her|at|gr)o$/i', '$1oes'],
            ['/(agend|addend|millenni|dat|extrem|bacteri|desiderat|strat|candelabr|errat|ov|symposi|curricul|automat|quor)(?:a|um)$/i', '$1a'],
            ['/(apheli|hyperbat|periheli|asyndet|noumen|phenomen|criteri|organ|prolegomen|hedr|automat)(?:a|on)$/i', '$1a'],
            ['/sis$/i', 'ses'],
            ['/(?:(kni|wi|li)fe|(ar|l|ea|eo|oa|hoo)f)$/i', '$1$2ves'],
            ['/([^aeiouy]|qu)y$/i', '$1ies'],
            ['/([^ch][ieo][ln])ey$/i', '$1ies'],
            ['/(x|ch|ss|sh|zz)$/i', '$1es'],
            ['/(matr|cod|mur|sil|vert|ind|append)(?:ix|ex)$/i', '$1ices'],
            ['/\b((?:tit)?m|l)(?:ice|ouse)$/i', '$1ice'],
            ['/(pe)(?:rson|ople)$/i', '$1ople'],
            ['/(child)(?:ren)?$/i', '$1ren'],
            ['/eaux$/i', '$0'],
            ['/m[ae]n$/i', 'men'],
            ['/thou/i', 'you'],
        ];
    }
}