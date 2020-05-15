<?php

/*
|------------------------------------------------------------------------
| Error Display                                                       
|------------------------------------------------------------------------
|
| Don't display any messages in a production environment. Instead, let the 
| system catch it and display a generic error message.
|
*/

ini_set('display_errors', 0);
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED & ~E_ERROR);