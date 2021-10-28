<?php

/*
|------------------------------------------------------------------------
| Error Display                                                       
|------------------------------------------------------------------------
|
| In local, we want to show as many errors as possible to help make 
| sure they don't make it to production. And save us hours of painful 
| debugging.
|
*/

error_reporting(E_ALL & E_WARNING | E_NOTICE | E_STRICT | E_ERROR); 
ini_set('display_errors', 'on');