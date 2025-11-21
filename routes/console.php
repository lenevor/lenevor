<?php

use Syscodes\Components\Core\Inspiration;
use Syscodes\Components\Support\Facades\Prime;

/*
|------------------------------------------------------------------------- 
| Console Routes
|-------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console 
| commands. Each Closure is bound to a command instance allowing a simple 
| approach to interacting with each command's IO methods.
|
*/

Prime::command('inspire', function () {
    $this->comment(Inspiration::quote());
})->describe('Display an inspiring quote');