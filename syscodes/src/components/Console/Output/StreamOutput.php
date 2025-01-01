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

namespace Syscodes\Components\Console\Output;

use InvalidArgumentException;
use Syscodes\Components\Contracts\Console\Output\OutputFormatter;

/**
 * Allows StreamOutput writes the output to a given stream.
 */
class StreamOutput extends Output
{
    /**
     * Gets the ouput to a given stream.
     * 
     * @var resource $stream
     */
    protected $stream;

    /**
     * Constructor. Create a new StreamOutput instance.
     * 
     * @param  resource  $stream  The stream resource
     * @param  int  $verbosity  The verbosity level
     * @param  bool|null  $decorated  Whether to decorated messages
     * @param  \Syscodes\Components\Contracts\Console\Output\OutputFormatter|null  $formatter  The output formatter instance
     * 
     * @return void
     * 
     * @throws \InvalidArgumentException
     */
    public function __construct($stream, int $verbosity = self::VERBOSITY_NORMAL, ?bool $decorated = null, ?OutputFormatter $formatter = null)
    {
        if ( ! \is_resource($stream) || 'stream' !== \get_resource_type($stream)) {
            throw new InvalidArgumentException('The StreamOutput class needs a stream as its first argument');
        }

        $this->stream = $stream;

        if (null === $decorated) {
            $decorated = $this->hasColorActivated();
        }

        parent::__construct($verbosity, $decorated, $formatter);
    }

    /**
     * Writes a message to the output.
     * 
     * @param  string  $message  The text to output
     * @param  bool  $newline  Add a newline command
     * 
     * @return void
     */
    protected function toWrite(string $message, bool $newline): void
    {
        if ($newline) {
            $message .= \PHP_EOL;
        }
        
        @fwrite($this->stream, $message);

        fflush($this->stream);
    }

    /**
     * Returns true if the stream supports colorization.
     * 
     * @return bool
     */
    protected function hasColorActivated(): bool
    {
        // Follow https://no-color.org/
        if (isset($_SERVER['NO_COLOR']) || false !== \getenv('NO_COLOR')) {
            return false;
        }
        
        if (\DIRECTORY_SEPARATOR === '\\') {
            return (\function_exists('sapi_windows_vt100_support')
                && @sapi_windows_vt100_support($this->stream))
                || false !== getenv('ANSICON')
                || 'ON' === getenv('ConEmuANSI')
                || 'xterm' === getenv('TERM');
        }
        
        return \stream_isatty($this->stream);
    }
}