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

namespace Syscodes\Components\View\Transpilers\Concerns;

/**
 * Trait TranspilesJson.
 */
trait TranspilesJson
{
    /**
     * The default JSON encoding options.
     * 
     * @var int $encodingOptionsJson
     */
    private $encodingOptionsJson = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT;

    /**
     * Traspile the JSON statement into valid PHP.
     * 
     * @param  string  $expression
     * 
     * @return string
     */
    protected function transpileJson($expression): string
    {
        $sections = explode(',', $this->stripParentheses($expression));

        $options = isset($sections[1]) ? $sections[1] : $this->encodingOptionsJson;

        $depth = isset($sections[2]) ? $sections[2] : 512;

        return "<?php echo json_encode($sections[0], $options, $depth); ?>";
    }
}