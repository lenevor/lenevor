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

namespace Syscodes\Components\Core\Console\Commands;

use App\Http\Middleware\PreventRequestsMaintenance as AppPreventRequestsMaintenance;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;
use Syscodes\Components\Console\Command;
use Syscodes\Components\Core\Events\MaintenanceModeEnabled;
use Syscodes\Components\Core\Exceptions\RegisterErrorViewPaths;
use Syscodes\Components\Core\Http\Middleware\PreventRequestsMaintenance;
use Syscodes\Components\Support\Chronos;
use Syscodes\Components\Support\Str;
use Throwable;

/**
 * Allows down the application with status 503 default.
 */
#[AsCommand(name: 'down')]
class DownCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'down'; 
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Put the application into maintenance / demo mode';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $wasAlreadyDown = $this->lenevor->maintenanceMode()->active();

            $downFilePayload = $this->getDownFilePayload();

            $this->lenevor->maintenanceMode()->activate($downFilePayload);

            file_put_contents(
                storage_path('framework/maintenance.php'),
                file_get_contents(__DIR__.'/templates/maintenance-mode.tpl')
            );

            $this->lenevor->get('events')->dispatch(new MaintenanceModeEnabled());

            $this->components->info($wasAlreadyDown
                ? 'Maintenance mode options updated.'
                : 'Application is now in maintenance mode.'
            );

            if ($downFilePayload['secret'] !== null) {
                $this->components->info('You may bypass maintenance mode via ['.config('app.url')."/{$downFilePayload['secret']}].");
            }
        } catch (Exception $e) {
            report($e);

            $this->components->error(sprintf(
                'Failed to enter maintenance mode: %s.',
                $e->getMessage(),
            ));

            return self::FAILURE;
        }
    }

    /**
     * Get the payload to be placed in the "down" file.
     *
     * @return array
     */
    protected function getDownFilePayload()
    {
        return [
            'except' => $this->excludedPaths(),
            'redirect' => $this->redirectPath(),
            'retry' => $this->getRetryTime(),
            'refresh' => $this->option('refresh'),
            'secret' => $this->getSecret(),
            'status' => (int) ($this->option('status') ?? 503),
            'template' => $this->option('render') ? $this->prerenderView() : null,
        ];
    }

    /**
     * Get the paths that should be excluded from maintenance mode.
     *
     * @return array
     */
    protected function excludedPaths()
    {
        try {
            return $this->lenevor->make(AppPreventRequestsMaintenance::class)->getExcludedPaths();
        } catch (Throwable) {
            try {
                return $this->lenevor->make(PreventRequestsMaintenance::class)->getExcludedPaths();
            } catch (Throwable) {
                return [];
            }
        }
    }

    /**
     * Get the path that users should be redirected to.
     *
     * @return string
     */
    protected function redirectPath()
    {
        if ($this->option('redirect') && $this->option('redirect') !== '/') {
            return '/'.trim($this->option('redirect'), '/');
        }

        return $this->option('redirect');
    }

    /**
     * Prerender the specified view so that it can be rendered even before loading Composer.
     *
     * @return string
     */
    protected function prerenderView()
    {
        (new RegisterErrorViewPaths)();

        return view($this->option('render'), [
            'retryAfter' => $this->option('retry'),
        ])->render();
    }

    /**
     * Get the number of seconds or date / time the client should wait before retrying their request.
     *
     * @return int|string|null
     */
    protected function getRetryTime()
    {
        $retry = $this->option('retry');

        if (is_numeric($retry) && $retry > 0) {
            return (int) $retry;
        }

        if (is_string($retry) && ! empty($retry)) {
            try {
                return Chronos::parse($retry)->toRfc7231String();
            } catch (Exception) {
                return null;
            }
        }

        return null;
    }

    /**
     * Get the secret phrase that may be used to bypass maintenance mode.
     *
     * @return string|null
     */
    protected function getSecret()
    {
        return match (true) {
            ! is_null($this->option('secret')) => $this->option('secret'),
            $this->option('with-secret') => Str::random(),
            default => null,
        };
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions(): array
    {
        return [
            ['redirect', null, InputOption::VALUE_OPTIONAL, 'The path that users should be redirected to'],
            ['render', null, InputOption::VALUE_OPTIONAL, 'The view that should be prerendered for display during maintenance mode'],
            ['retry', null, InputOption::VALUE_OPTIONAL, 'The number of seconds or the datetime after which the request may be retried'],
            ['refresh', null, InputOption::VALUE_OPTIONAL, 'The number of seconds after which the browser may refresh'],
            ['secret', null, InputOption::VALUE_OPTIONAL, 'The secret phrase that may be used to bypass maintenance mode'],
            ['with-secret', null, InputOption::VALUE_OPTIONAL, 'Generate a random secret phrase that may be used to bypass maintenance mode'],
            ['status', null, InputOption::VALUE_OPTIONAL, ' The status code that should be used when returning the maintenance mode response', 503],
        ];
    }
}