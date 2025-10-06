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

use Syscodes\Components\Console\Command;
use Syscodes\Components\Encryption\Encrypter;
use Syscodes\Components\Console\Input\InputOption;
use Syscodes\Components\Console\Concerns\ConfirmProcess;
use Syscodes\Components\Console\Attribute\AsCommandAttribute;
use Syscodes\Components\Contracts\Console\Input\InputOption as InputOptionInterface;

/**
 * This class displays the key generate for a given command.
 */
#[AsCommandAttribute(name: 'key:generate')]
class KeyGenerateCommand extends Command
{
    use ConfirmProcess;

    /**
     * The console command description.
     * 
     * @var string $description
     */
    protected string $description = 'Set the application key';

    /**
     * Gets input definition for command.
     * 
     * @return void
     */
    protected function define()
    {
        $this->setDefinition([
                    new InputOption('show', null, InputOptionInterface::VALUE_REQUIRED, 'Display the key instead of modifying files'),
                    new InputOption('force', null, InputOptionInterface::VALUE_OPTIONAL, 'Force the operation to run when in production'),
        ]);
    }

    /**
     * Executes the current command.
     * 
     * @return int
     * 
     * @throws \LogicException
     */
    public function handle()
    {
        $key = $this->generateRandomKey();

        if ($this->option('show')) {
            return $this->note($key);
        }

        if ( ! $this->setKeyInEnvironmentFile($key)) {
            return;
        }
        
        $this->lenevor['config']['security.key'] = $key;

        $this->commandline('<bg=blue;fg=white> INFO </> Application key set successfully.');
    }
    
    /**
     * Generate a random key for the application.
     * 
     * @return string
     */
    protected function generateRandomKey(): string
    {
        return 'base64:'.base64_encode(
            Encrypter::generateRandomKey($this->lenevor['config']['security.cipher'])
        );
    }

    /**
     * Set the application key in the environment file.
     *
     * @param  string  $key
     * @return bool
     */
    protected function setKeyInEnvironmentFile($key): bool
    {
        $currentKey = $this->lenevor['config']['security.key'];

        if (strlen($currentKey) !== 0 && ( ! $this->confirmToProceed())) {
            return false;
        }

        if ( ! $this->writeNewEnvironmentFileWith($key)) {
            return false;
        }

        return true;
    }

    /**
     * Write a new environment file with the given key.
     *
     * @param  string  $key
     * @return bool
     */
    protected function writeNewEnvironmentFileWith($key): bool
    {
        $replaced = preg_replace(
            $this->keyReplacementPattern(),
            'APP_KEY = '.$key,
            $input = file_get_contents($this->lenevor->environmentFilePath())
        );
        
        if ($replaced === $input || $replaced === null) {
            $this->error('Unable to set application key. No APP_KEY variable was found in the .env file.');

            return false;
        }

        file_put_contents($this->lenevor->environmentFilePath(), $replaced);

        return true;
    }

    /**
     * Get a regex pattern that will match env APP_KEY with any random key.
     *
     * @return string
     */
    protected function keyReplacementPattern(): string
    {
        $escaped = preg_quote(' = '.$this->lenevor['config']['security.key'], '/');

        return "/^APP_KEY{$escaped}/m";
    }
}