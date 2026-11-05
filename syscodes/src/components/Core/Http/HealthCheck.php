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

namespace Syscodes\Components\Core\Http;

use Syscodes\Components\Core\Events\DiagnosingHealth;
use Syscodes\Components\Http\Request;
use Syscodes\Components\Support\Facades\Event;
use Syscodes\Components\Support\Facades\View;

/**
 * Allows for monitoring the performance and internal health of 
 * a Lenevor web application in real time.
 */
class HealthCheck
{
    /**
     * Handle the incoming request.
     *
     * @param  \Syscodes\Components\Http\Request  $request
     * @return \Syscodes\Components\Http\Response|\Syscodes\Components\Http\JsonResponse
     */
    public function __invoke(Request $request)
    {
        $exception = null;

        try {
            Event::dispatch(new DiagnosingHealth);
        } catch (\Throwable $e) {
            if (app()->hasDebugModeEnabled()) {
                throw $e;
            }

            report($e);

            $exception = $e->getMessage();
        }

        $status = $exception ? 500 : 200;

        if ($request->expectsJson()) {
            return response()->json([
                'status' => $exception ? 'down' : 'up',
            ], $status);
        }

        return response(View::file(__DIR__.'/../resources/health-up.plaze.php', [
            'exception' => $exception,
        ]), status: $status);
    }
}