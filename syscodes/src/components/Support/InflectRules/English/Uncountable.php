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
 * This class allows identify words in singular.
 */
final class Uncountable
{
    /**
     * Get the words uncontable.
     * 
     * @return array
     */
    public static function all(): array
    {
        return [
            'adulthood',
            'advice',
            'agenda',
            'aid',
            'aircraft',
            'alcohol',
            'ammo',
            'analytics',
            'anime',
            'athletics',
            'audio',
            'bison',
            'blood',
            'bream',
            'buffalo',
            'butter',
            'carp',
            'cash',
            'chassis',
            'chess',
            'clothing',
            'cod',
            'commerce',
            'cooperation',
            'corps',
            'debris',
            'diabetes',
            'digestion',
            'elk',
            'energy',
            'equipment',
            'excretion',
            'expertise',
            'firmware',
            'flounder',
            'fun',
            'gallows',
            'garbage',
            'graffiti',
            'hardware',
            'headquarters',
            'health',
            'herpes',
            'highjinks',
            'homework',
            'housework',
            'information',
            'jeans',
            'justice',
            'kudos',
            'labour',
            'literature',
            'machinery',
            'mackerel',
            'mail',
            'media',
            'mews',
            'moose',
            'music',
            'mud',
            'manga',
            'news',
            'only',
            'personnel',
            'pike',
            'plankton',
            'pliers',
            'police',
            'pollution',
            'premises',
            'rain',
            'research',
            'rice',
            'salmon',
            'scissors',
            'series',
            'sewage',
            'shambles',
            'shrimp',
            'software',
            'species',
            'staff',
            'swine',
            'tennis',
            'thanks',
            'traffic',
            'transportation',
            'trout',
            'tuna',
            'wealth',
            'welfare',
            'whiting',
            'wildebeest',
            'wildlife',
            'you',
            '/pok[eé]mon$/i',
            '/[^aeiou]ese$/i', // "chinese", "japanese"
            '/deer$/i', // "deer", "reindeer"
            '/fish$/i', // "fish", "blowfish", "angelfish"
            '/measles$/i',
            '/o[iu]s$/i', // "carnivorous"
            '/pox$/i', // "chickpox", "smallpox"
            '/sheep$/i'
        ];
    }
}