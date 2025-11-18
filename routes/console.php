<?php

use Syscodes\Components\Core\Inspiring;
use Syscodes\Components\Support\Facades\Prime;

Prime::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->describe('Display an inspiring quote');