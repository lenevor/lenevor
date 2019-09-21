<?php 

/*
|------------------------------------------------------------------------
| Lenevor Config Class Map                                                      
|------------------------------------------------------------------------
|
| This file is automatically generated for build classmap. 
|
| Note: [!!] Do not modify it directly.
|
*/
return [
    
    'Syscode\\Cache\\Exceptions\\CacheDriverException' => SYS_PATH.'classes/Cache/Exceptions/CacheDriverException.php',
    'Syscode\\Cache\\Store\\ApcStore' => SYS_PATH.'classes/Cache/Store/ApcStore',
    'Syscode\\Cache\\Store\\ApcWrapper' => SYS_PATH.'classes/Cache/Store/ApcWrapper',
    'Syscode\\Cache\\Store\\ArrayStore' => SYS_PATH.'classes/Cache/Store/ArrayStore.php',
    'Syscode\\Cache\\Store\\DatabaseStore' => SYS_PATH.'classes/Cache/Store/DatabaseStore.php',
    'Syscode\\Cache\\Store\\FileStore' => SYS_PATH.'classes/Cache/Store/FileStore.php',
    'Syscode\\Cache\\Store\\MemcachedConnector' => SYS_PATH.'classes/Cache/Store/MemcachedConnector.php',
    'Syscode\\Cache\\Store\\MemcachedStore' => SYS_PATH.'classes/Cache/Store/MemcachedStore.php',
    'Syscode\\Cache\\Store\\NullStore' => SYS_PATH.'classes/Cache/Store/NullStore.php',
    'Syscode\\Cache\\Store\\RedisStore' => SYS_PATH.'classes/Cache/Store/RedisStore.php', 
    'Syscode\\Cache\\Types\\CacheKey' => SYS_PATH.'classes/Cache/Types/CacheKey.php',  
    'Syscode\\Cache\\Utils\\FileCacheRegister' => SYS_PATH.'classes/Cache/Utils/FileCacheRegister.php', 
    'Syscode\\Cache\\CacheManager' => SYS_PATH.'classes/Cache/CacheManager.php',
    'Syscode\\Cache\\CacheRepository' => SYS_PATH.'classes/Cache/CacheRepository.php',
    'Syscode\\Cache\\CacheServiceProvider' => SYS_PATH.'classes/Cache/CacheServiceProvider.php',
    'Syscode\\Config\\Configure' => SYS_PATH.'classes/Config/Configure.php', 
    'Syscode\\Config\\ParserEnv' => SYS_PATH.'classes/Config/ParserEnv.php', 
    'Syscode\\Console\\Application' => SYS_PATH.'classes/Console/Application.php',
    'Syscode\\Console\\Cli' => SYS_PATH.'classes/Console/Cli.php',
    'Syscode\\Console\\Command' => SYS_PATH.'classes/Console/Command.php',
    'Syscode\\Container\\Exceptions\\ContainerException' => SYS_PATH.'classes/Container/Exceptions/ContainerException.php',
    'Syscode\\Container\\Exceptions\\UnknownIdentifierException' => SYS_PATH.'classes/Container/Exceptions/UnknownIdentifierException.php',
    'Syscode\\Container\\Container' => SYS_PATH.'classes/Container/Container.php',
    'Syscode\\Contracts\\Cache\\Key' => SYS_PATH.'classes/Contracts/Cache/Key.php',
    'Syscode\\Contracts\\Cache\\Manager' => SYS_PATH.'classes/Contracts/Cache/Manager.php',
    'Syscode\\Contracts\\Cache\\Store' => SYS_PATH.'classes/Contracts/Cache/Store.php',
    'Syscode\\Contracts\\Config\\Configure' => SYS_PATH.'classes/Contracts/Config/Configure.php', 
    'Syscode\\Contracts\\Console\\Application' => SYS_PATH.'classes/Contracts/Console/Application.php',
    'Syscode\\Contracts\\Container\\BindingResolutionException' => SYS_PATH.'classes/Contracts/Container/BindingResolutionException.php',
    'Syscode\\Contracts\\Container\\Container' => SYS_PATH.'classes/Contracts/Container/Container.php',
    'Syscode\\Contracts\\Container\\ExpectedInvokableException' => SYS_PATH.'classes/Contracts/Container/ExpectedInvokableException.php',
    'Syscode\\Contracts\\Container\\NotFoundException' => SYS_PATH.'classes/Contracts/Container/NotFoundException.php',
    'Syscode\\Contracts\\Core\\Application' => SYS_PATH.'classes/Contracts/Core/Application.php',
    'Syscode\\Contracts\\Core\\Lenevor' => SYS_PATH.'classes/Contracts/Core/Lenevor.php',
    'Syscode\\Contracts\\Debug\\ExceptionHandler' => SYS_PATH.'classes/Contracts/Debug/ExceptionHandler.php',
    'Syscode\\Contracts\\Debug\\Handler' => SYS_PATH.'classes/Contracts/Debug/Handler.php',
    'Syscode\\Contracts\\Debug\\Table' => SYS_PATH.'classes/Contracts/Debug/Table.php',
    'Syscode\\Contracts\\Routing\\Routable' => SYS_PATH.'classes/Contracts/Routing/Routable.php',
    'Syscode\\Contracts\\View\\Factory' => SYS_PATH.'classes/Contracts/View/Factory.php',
    'Syscode\\Contracts\\View\\View' => SYS_PATH.'classes/Contracts/View/View.php', 
    'Syscode\\Controller\\Controller' => SYS_PATH.'classes/Controller/Controller.php',
    'Syscode\\Controller\\TemplateController' => SYS_PATH.'classes/Controller/TemplateController.php',
    'Syscode\\Core\\Bootstrap\\BootConfiguration' => SYS_PATH.'classes/Core/Bootstrap/BootConfiguration.php',
    'Syscode\\Core\\Bootstrap\\BootDetectEnvironment' => SYS_PATH.'classes/Core/Bootstrap/BootDetectEnvironment.php',
    'Syscode\\Core\\Bootstrap\\BootHandleExceptions' => SYS_PATH.'classes/Core/Bootstrap/BootHandleExceptions.php',
    'Syscode\\Core\\Bootstrap\\BootRegisterFacades' => SYS_PATH.'classes/Core/Bootstrap/BootRegisterFacades.php', 
    'Syscode\\Core\\Exceptions\\DebugHandler' => SYS_PATH.'classes/Core/Exceptions/DebugHandler.php',
    'Syscode\\Core\\Exceptions\\Handler' => SYS_PATH.'classes/Core/Exceptions/Handler.php',
    'Syscode\\Core\\Http\\Exceptions\\AccessDeniedHttpException' => SYS_PATH.'classes/Core/Http/Exceptions/AccessDeniedHttpException.php',
    'Syscode\\Core\\Http\\Exceptions\\BadRequestHttpException' => SYS_PATH.'classes/Core/Http/Exceptions/BadRequestHttpException.php',
    'Syscode\\Core\\Http\\Exceptions\\ConflictHttpException' => SYS_PATH.'classes/Core/Http/Exceptions/ConflictHttpException.php',
    'Syscode\\Core\\Http\\Exceptions\\GoneHttpException' => SYS_PATH.'classes/Core/Http/Exceptions/GoneHttpException.php',
    'Syscode\\Core\\Http\\Exceptions\\HttpException' => SYS_PATH.'classes/Core/Http/Exceptions/HttpException.php',
    'Syscode\\Core\\Http\\Exceptions\\LenevorException' => SYS_PATH.'classes/Core/Http/Exceptions/LenevorException.php',
    'Syscode\\Core\\Http\\Exceptions\\LengthRequiredException' => SYS_PATH.'classes/Core/Http/Exceptions/LengthRequiredException.php',
    'Syscode\\Core\\Http\\Exceptions\\MethodNotAllowedHttpException' => SYS_PATH.'classes/Core/Http/Exceptions/MethodNotAllowedHttpException.php',
    'Syscode\\Core\\Http\\Exceptions\\NotFoundHttpException' => SYS_PATH.'classes/Core/Http/Exceptions/NotFoundHttpException.php',
    'Syscode\\Core\\Http\\Exceptions\\PreconditionFailedHttpException' => SYS_PATH.'classes/Core/Http/Exceptions/PreconditionFailedHttpException.php',
    'Syscode\\Core\\Http\\Exceptions\\PreconditionRequiredHttpException' => SYS_PATH.'classes/Core/Http/Exceptions/PreconditionRequiredHttpException.php',
    'Syscode\\Core\\Http\\Exceptions\\ServerErrorHttpException' => SYS_PATH.'classes/Core/Http/Exceptions/ServerErrorHttpException.php',
    'Syscode\\Core\\Http\\Exceptions\\ServiceUnavailableHttpException' => SYS_PATH.'classes/Core/Http/Exceptions/ServiceUnavailableHttpException.php',
    'Syscode\\Core\\Http\\Exceptions\\TooManyRequestsHttpException' => SYS_PATH.'classes/Core/Http/Exceptions/TooManyRequestsHttpException.php',
    'Syscode\\Core\\Http\\Exceptions\\UnauthorizedHttpException' => SYS_PATH.'classes/Core/Http/Exceptions/UnauthorizedHttpException.php',
    'Syscode\\Core\\Http\\Exceptions\\UnprocessableEntityHttpException' => SYS_PATH.'classes/Core/Http/Exceptions/UnprocessableEntityHttpException.php',
    'Syscode\\Core\\Http\\Exceptions\\UnsupportedMediaTypeHttpException' => SYS_PATH.'classes/Core/Http/Exceptions/UnsupportedMediaTypeHttpException.php',
    'Syscode\\Core\\Http\Lenevor' => SYS_PATH.'classes/Core/Http/Lenevor.php',
    'Syscode\\Core\\AliasLoader' => SYS_PATH.'classes/Core/AliasLoader.php',  
    'Syscode\\Core\\Application' => SYS_PATH.'classes/Core/Application.php',    
    'Syscode\\Database\\Connectors\\MySqlConnector' => SYS_PATH.'classes/Database/Connectors/MySqlConnector.php',
    'Syscode\\Database\\Connectors\\PostgresConnector' => SYS_PATH.'classes/Database/Connectors/PostgresConnector.php',
    'Syscode\\Database\\Connectors\\SQLiteConnector' => SYS_PATH.'classes/Database/Connectors/SQLiteConnector.php',
    'Syscode\\Database\\Connectors\\SqlServerConnector' => SYS_PATH.'classes/Database/Connectors/SqlServerConnector.php',
    'Syscode\\Database\\Exceptions\\ModelNotFoundException' => SYS_PATH.'classes/Database/Exceptions/ModelNotFoundException.php',
    'Syscode\\Database\\Holisen\\Builder' => SYS_PATH.'classes/Database/Holisen/Builder.php',
    'Syscode\\Database\\Holisen\\Collection' => SYS_PATH.'classes/Database/Holisen/Collection.php',
    'Syscode\\Database\\Holisen\\Model' => SYS_PATH.'classes/Database/Holisen/Model.php',
    'Syscode\\Database\\Connection' => SYS_PATH.'classes/Database/Connection.php',
    'Syscode\\Database\\ConnectionFactory' => SYS_PATH.'classes/Database/ConnectionFactory.php',     
    'Syscode\\Database\\DatabaseServiceProvider' => SYS_PATH.'classes/Database/DatabaseServiceProvider.php',
    'Syscode\\Debug\\FatalExceptions\\FatalErrorException' => SYS_PATH.'classes/Debug/FatalExceptions/FatalErrorException.php',
    'Syscode\\Debug\\FatalExceptions\\FatalThrowableError' => SYS_PATH.'classes/Debug/FatalExceptions/FatalThrowableError.php',
    'Syscode\\Debug\\FatalExceptions\\FlattenException' => SYS_PATH.'classes/Debug/FatalExceptions/FlattenException.php',
    'Syscode\\Debug\\FatalExceptions\\OutOfMemoryException' => SYS_PATH.'classes/Debug/FatalExceptions/OutOfMemoryException.php',
    'Syscode\\Debug\\FrameHandler\\Collection' => SYS_PATH.'classes/Debug/Exceptions/FrameHandler/Collection.php',
    'Syscode\\Debug\\FrameHandler\\Formatter' => SYS_PATH.'classes/Debug/Exceptions/FrameHandler/Formatter.php',
    'Syscode\\Debug\\FrameHandler\\Frame' => SYS_PATH.'classes/Debug/Exceptions/FrameHandler/Frame.php',
    'Syscode\\Debug\\FrameHandler\\Supervisor' => SYS_PATH.'classes/Debug/Exceptions/FrameHandler/Supervisor.php',
    'Syscode\\Debug\\Handlers\\CallbackHandler' => SYS_PATH.'classes/Debug/Exceptions/Handlers/CallbackHandler.php',
    'Syscode\\Debug\\Handlers\\MainHandler' => SYS_PATH.'classes/Debug/Exceptions/Handlers/MainHandler.php',
    'Syscode\\Debug\\Handlers\\JsonResponseHandler' => SYS_PATH.'classes/Debug/Exceptions/Handlers/JsonResponseHandler.php',
    'Syscode\\Debug\\Handlers\\PlainTextHandler' => SYS_PATH.'classes/Debug/Exceptions/Handlers/PlainTextHandler.php',
    'Syscode\\Debug\\Handlers\\PleasingPageHandler' => SYS_PATH.'classes/Debug/Exceptions/Handlers/PleasingPageHandler.php',
    'Syscode\\Debug\\Util\\ArrayTable' => SYS_PATH.'classes/Debug/Exceptions/Util/ArrayTable.php',
    'Syscode\\Debug\\Util\\Misc' => SYS_PATH.'classes/Debug/Exceptions/Util/Misc.php',
    'Syscode\\Debug\\Util\\System' => SYS_PATH.'classes/Debug/Exceptions/Util/System.php', 
    'Syscode\\Debug\\Util\\TableLabel' => SYS_PATH.'classes/Debug/Exceptions/Util/TableLabel.php',
    'Syscode\\Debug\\Util\\TemplateHandler' => SYS_PATH.'classes/Debug/Exceptions/Util/TemplateHandler.php',
    'Syscode\\Debug\\Benchmark' => SYS_PATH.'classes/Debug/Benchmark.php',
    'Syscode\\Debug\\ExceptionHandler' => SYS_PATH.'classes/Debug/ExceptionHandler.php',
    'Syscode\\Debug\\GDebug' => SYS_PATH.'classes/Debug/GDebug.php', 
    'Syscode\\Filesystem\\Exceptions\\FileException' => SYS_PATH.'classes/Filesystem/Exceptions/FileException.php',
    'Syscode\\Filesystem\\Exceptions\\FileNotFoundException' => SYS_PATH.'classes/Filesystem/Exceptions/FileNotFoundException.php',
    'Syscode\\Filesystem\\Exceptions\\FileUnableToMoveException' => SYS_PATH.'classes/Filesystem/Exceptions/FileUnableToMoveException.php',
    'Syscode\\Filesystem\\Exceptions\\UnexpectedTypeException' => SYS_PATH.'classes/Filesystem/Exceptions/UnexpectedTypeException.php',
    'Syscode\\Filesystem\\FileMimeType' => SYS_PATH.'classes/Filesystem/FileMimeType.php',
    'Syscode\\Filesystem\\Filesystem' => SYS_PATH.'classes/Filesystem/Filesystem.php',  
    'Syscode\\Filesystem\\FilesystemServiceProvider' => SYS_PATH.'classes/Filesystem/FilesystemServiceProvider.php', 
    'Syscode\\Http\\Contributors\\Headers' => SYS_PATH.'classes/Http/Contributors/Headers.php',
    'Syscode\\Http\\Contributors\\Parameters' => SYS_PATH.'classes/Http/Contributors/Parameters.php',
    'Syscode\\Http\\Contributors\\Server' => SYS_PATH.'classes/Http/Contributors/Server.php',
    'Syscode\\Http\\Contributors\\Status' => SYS_PATH.'classes/Http/Contributors/Status.php', 
    'Syscode\\Http\\Exceptions\\HttpResponseException' => SYS_PATH.'classes/Http/Exceptions/HttpResponseException.php',
    'Syscode\\Http\\Exceptions\\HttpURIException' => SYS_PATH.'classes/Http/Exceptions/HttpURIException.php',
    'Syscode\\Http\\Exceptions\\PostTooLargeHttpException' => SYS_PATH.'classes/Http/Exceptions/PostTooLargeHttpException.php',
    'Syscode\\Http\\Exceptions\\RequestErrorException' => SYS_PATH.'classes/Http/Exceptions/RequestErrorException.php',
    'Syscode\\Http\\Http' => SYS_PATH.'classes/Http/Http.php',
    'Syscode\\Http\\JsonResponse' => SYS_PATH.'classes/Http/JsonResponse.php',
    'Syscode\\Http\\RedirectResponse' => SYS_PATH.'classes/Http/RedirectResponse.php',
    'Syscode\\Http\\Request' => SYS_PATH.'classes/Http/Request.php',
    'Syscode\\Http\\Response' => SYS_PATH.'classes/Http/Response.php',
    'Syscode\\Http\\ResponseTrait' => SYS_PATH.'classes/Http/ResponseTrait.php',
    'Syscode\\Http\\URI' => SYS_PATH.'classes/Http/URI.php',
    'Syscode\\Log\\Logger' => SYS_PATH.'classes/Log/Logger.php',
    'Syscode\\Redis\\RedisManager' => SYS_PATH.'classes/Redis/RedisManager.php',       
    'Syscode\\Routing\\Exceptions\\RouteNotFoundException' => SYS_PATH.'classes/Routing/Exceptions/RouteNotFoundException.php',
    'Syscode\\Routing\\Redirector' => SYS_PATH.'classes/Routing/Redirector.php',
    'Syscode\\Routing\\Route' => SYS_PATH.'classes/Routing/Route.php',
    'Syscode\\Routing\\RouteGroup' => SYS_PATH.'classes/Routing/RouteGroup.php',
    'Syscode\\Routing\\RouteMapTrait' => SYS_PATH.'classes/Routing/RouteMapTrait.php',
    'Syscode\\Routing\\Router' => SYS_PATH.'classes/Routing/Router.php',
    'Syscode\\Routing\\RouteResolver' => SYS_PATH.'classes/Routing/RouteResolver.php',
    'Syscode\\Routing\\RouteResponse' => SYS_PATH.'classes/Routing/RouteResponse.php',
    'Syscode\\Routing\\RoutingServiceProvider' => SYS_PATH.'classes/Routing/RoutingServiceProvider.php',
    'Syscode\\Routing\\UrlGenerator' => SYS_PATH.'classes/Routing/UrlGenerator.php',
    'Syscode\\Support\\Chronos\\Exceptions\\InvalidDateTimeException' => SYS_PATH.'classes/Support/Chronos/Exceptions/InvalidDateTimeException.php',
    'Syscode\\Support\\Chronos\\Traits\\Comparison' => SYS_PATH.'classes/Support/Chronos/Traits/Comparison.php',
    'Syscode\\Support\\Chronos\\Traits\\Date' => SYS_PATH.'classes/Support/Chronos/Traits/Date.php',
    'Syscode\\Support\\Chronos\\Traits\\Difference' => SYS_PATH.'classes/Support/Chronos/Traits/Difference.php',
    'Syscode\\Support\\Chronos\\Traits\\Factory' => SYS_PATH.'classes/Support/Chronos/Traits/Factory.php',
    'Syscode\\Support\\Chronos\\Traits\\Schedule' => SYS_PATH.'classes/Support/Chronos/Traits/Schedule.php',
    'Syscode\\Support\\Chronos\\Time' => SYS_PATH.'classes/Support/Chronos/Time.php',
    'Syscode\\Support\\Facades\\App' => SYS_PATH.'classes/Support/Facades/App.php',
    'Syscode\\Support\\Facades\\Cache' => SYS_PATH.'classes/Support/Facades/Cache.php',
    'Syscode\\Support\\Facades\\Config' => SYS_PATH.'classes/Support/Facades/Config.php',
    'Syscode\\Support\\Facades\\Date' => SYS_PATH.'classes/Support/Facades/Date.php',
    'Syscode\\Support\\Facades\\Facade' => SYS_PATH.'classes/Support/Facades/Facade.php',
    'Syscode\\Support\\Facades\\File' => SYS_PATH.'classes/Support/Facades/File.php',
    'Syscode\\Support\\Facades\\Http' => SYS_PATH.'classes/Support/Facades/Http.php',
    'Syscode\\Support\\Facades\\Lang' => SYS_PATH.'classes/Support/Facades/Lang.php',
    'Syscode\\Support\\Facades\\Prime' => SYS_PATH.'classes/Support/Facades/Prime.php',
    'Syscode\\Support\\Facades\\Request' => SYS_PATH.'classes/Support/Facades/Request.php',
    'Syscode\\Support\\Facades\\Response' => SYS_PATH.'classes/Support/Facades/Response.php',
    'Syscode\\Support\\Facades\\Route' => SYS_PATH.'classes/Support/Facades/Route.php',
    'Syscode\\Support\\Facades\\View' => SYS_PATH.'classes/Support/Facades/View.php',
    'Syscode\\Support\\Arr' => SYS_PATH.'classes/Support/Arr.php',
    'Syscode\\Support\\Chronos' => SYS_PATH.'classes/Support/Chronos.php', 
    'Syscode\\Support\\Finder' => SYS_PATH.'classes/Support/Finder.php',
    'Syscode\\Support\\HigherOrderTakeProxy' => SYS_PATH.'classes/Support/HigherOrderTakeProxy.php',
    'Syscode\\Support\\InteractsWithTime' => SYS_PATH.'classes/Support/InteractsWithTime.php',
    'Syscode\\Support\\ServiceProvider' => SYS_PATH.'classes/Support/ServiceProvider.php',
    'Syscode\\Support\\Str' => SYS_PATH.'classes/Support/Str.php',
    'Syscode\\Translation\\Translator' => SYS_PATH.'classes/Translation/Translator.php',
    'Syscode\\Version' => SYS_PATH.'classes/Version/Version.php',
    'Syscode\\View\\Exceptions\\ViewException' => SYS_PATH.'classes/View/Exceptions/ViewException.php',
    'Syscode\\View\\Extension' => SYS_PATH.'classes/View/Extension.php',
    'Syscode\\View\\Parser' => SYS_PATH.'classes/View/Parser.php',
    'Syscode\\View\\View' => SYS_PATH.'classes/View/View.php',
    'Syscode\\View\\ViewServiceProvider' => SYS_PATH.'classes/View/ViewServiceProvider.php',
    'Psr\\Container\\ContainerExceptionInterface' => SYS_PATH.'external/psr/container/src/ContainerExceptionInterface.php',
    'Psr\\Container\\ContainerInterface' => SYS_PATH.'external/psr/container/src/ContainerInterface.php',
    'Psr\\Container\\NotFoundExceptionInterface' => SYS_PATH.'external/psr/container/src/NotFoundExceptionInterface.php',

];