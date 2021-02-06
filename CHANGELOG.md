# Release Notes

## [Version 0.7.3-alpha.7] 

Release Date: Jan 08, 2021

**Next alpha release of Lenevor**

### Added
- Added new methods for allows connection with the database
- Added methods for query builder
- Added new Pipeline class for handle the given exception
- Added methods for finds route to given request

### Changed
- Changed release to 0.7.3
- Changed for variables of database settings
- Changed traits namespace in the `Routing` classes


## [Version 0.7.2-alpha.7] 

Release Date: Aug 01, 2020

**Next alpha release of Lenevor**

### Added
- Added the `Renderable` interface in the `Response` class to automatically trigger the render() method
- Added new method for called to JoinClause class and 'get' method the columns are use an array automatically
- Allows check if given request uri matches given uri method
- Added methods toHtml(), setter and getter for searched to path in `View` class
- Added $router as parameter in __construct in `Lenevor` class
- Added the files list map for call the route of files helpers in `Autoloader` class

### Changed
- Changed release to 0.7.2
- Changed name of the methods 'select' in `ConnectionInterface` interface
- Bug fix in the uri in `Route` class
- Gets a pattern from an array 'where' in `Router` class
- Spacing

### Removed
- Removed property of parameter in `Response` class


## [Version 0.7.1-alpha.7] 

Release Date: Jun 22, 2020

**Next alpha release of Lenevor**

### Added
- Added methods for get parameters for a route in `RouteParamBinding` class
- Added methods for insert and update
- Added methods for process of select statement in the `Builder` class
- Spacing

### Changed
- Changed for release 0.7.1 and extends of interface Renderable
- Changed namespace


## [Version 0.7.0-alpha.7] 

Release Date: May 20, 2020

**Next alpha release of Lenevor**

### Added
- Added alias methods for conditionals of route (host, port, scheme) in the `RouteConditionalTrait` trait
- Generate a Resource instance in `Router` class
- Added function for mapped of arrays in the `RouteMapTrait` trait

### Changed
- Changed release to 0.7.0 and the errors were fixed of syntax in most the classes of routing

### Removed
- Removed method for route resolver in `RouteServiceProvider` class
- Removed conditional of Renderable interface in `RouteResolverTrait` trait


## [Version 0.6.0-alpha.6] 

Release Date: Feb 26, 2020

**Next alpha release of Lenevor**

### Added
- Added the views system called plaze
- Added namespace for calls events in view class
- Added new methods of rendering
- Added class for rendering string


## [Version 0.5.0-alpha.5] 

Release Date: Jan 12, 2020

**Next alpha release of Lenevor**

### Added
- Added new class called `RouteAction`
- Added new trait for the `Route` class
- Added new methods for the `RouteGroup` class
- Added attribute macros of type array in the `Router` class
- Added new class called `RouteFileRegister`
- Added new system of encryption

### Changed
- Changed new theme for the debug system
- Changed Release 0.5.0-alpha.5
- Changed new number version


## [Version 0.4.0-alpha.4] 

Release Date: Nov 25, 2019

**Next alpha release of Lenevor**

### Added
- Added Session system
- Added Service providers system
- Added Logger system
- Added new facade for logging
- Added new method for delete keys and your contents
- Added new class called `Manager`
- Added function static random in the `Str` class
- Added new method for call all the service providers in the `Application` class
- Added new keys for session system in the `Application` class
- Added reportException method in the `Lenevor` class
- Added methods for report logging in the `Core/Exceptions/Handler` class
- Added new class boot for configured providers 
- Added new class for initialize providers
- Added new functions for security of sessions in `Core/Helpers` file
- Added new interface of session
- Added new interface for log system
- Added new keys for session in `.env.dist` file
- Added function of language in the `Response` class

### Changed
- Changed Release 0.4.0-alpha.4
- Changed new number version


## [Version 0.3.0-alpha.3] 

Release Date: Sep 07, 2019

**Next alpha release of Lenevor**

### Added
- Added `CacheRepository` class
- Added `InteractsWithTime` class
- Added `Chronos` DateTime system
- Added new methods of the `Filesystem` class

### Changed
- Changed Release 0.3.0-alpha.3
- Changed interface of GDebug system


## [Version 0.2.0-alpha.2]

Release Date: Aug 08, 2019

**Next alpha release of Lenevor**

### Added
- Added `FatalErrorException` class
- Added `FatalThrowableError` class
- Added `FlattenException` class
- Added `JsonResponse` class
- Added `Headers` class
- Added `OutOfMemoryException` class
- Added `Parameters` class
- Added `Redirector` class
- Added `RedirectResponse` class
- Added `RouteResponse` class
- Added `UrlGenerator` class
- Added new methods from the `Core/helpers`
- Added new class for redirection links in `Core/Exceptions/Handler` class
- Added new instances in `Core/Application` class
- Addded method for prepare base URL in `Http` class
- Added methods for control to the parse URL in `Request` class
- Added new method isRedirect for have a range of status code in `Response` class
- Added variables and method for parse URL in `URI` class
- Added new variables and methods of handler stack in `GDebug` class

### Changed
- Changed Release 0.2.0-alpha.2
- Changed for title when don't this active the debug in `ExceptionHandler` class


## [Version 0.1.0-alpha.1]

Release Date: May 2, 2019

**First Alpha Release: Created date of the Lenevor framework**

### Added
- Added `Autoloader` for psr4
- Added `AutoloadConfig` to Config folder
- Added `Arr` class for return the element arrays
- Added `Config` class for loader the files of configuration
- Added `Container` for bindings and resolved instances
- Added `Controller` class
- Added `TemplateController` class what extends of the `Controller` class
- Added the database `Holisen` system with ORM and system the relations beetween tables
- Added `Application` interface in `Contracts/Core` folder
- Added `Lenevor` interface in `Contracts/Core` folder
- Added `Handler` interface in `Contracts/Debug` folder
- Added `Handler` class to `app/Exceptions` folder for loader exceptions own
- Added new interface `Table` in `Contracts/Debug`
- Added news classes `ArrayTable` and `TableLabel` for gets the key label and data value
- Added the folder `Resources` in Debug for use views
- Added the `cache` system
- Added `Log` class for register log events
- Added `Server` class for generate server object
- Added `Status` class
- Added `ExceptionHandler` folder for manage the debugging and views error
- Added `Finder` class for the search path of files
- Added `Helpers` for loaded of functions support
- Added `Request` use headers, segments and verbs HTTP
- Added `Response` use headers and content 
- Added `Bootstrap` folder to `Core` folder for loader configurations main of system
- Added the `Debug` system for debugging errors to `classes/Core` folder
- Added `bootstrapper` system in `Application` class
- Added CLI system basic
- Added `Contracts` folder order the interfaces with your folders relations to each class implemented
- Added routing system with verbs HTTP, regex and routes map
- Added the `filesystem` system for create, read, delete, copy, rename, move files and folders
- Added `Str` class for manipulation of strings
- Added versioning system
- Added `AliasLoader` class for load a class alias if it is registered
- Added `Application` class to `app/Console` folder for generate CLI commands
- Added `Lenevor` class to load main of framework
- Added `stubs` folder for replacer in your files a class complete 
- Added `Configure` interface in `Contracts/Config` folder
- Added `View` use files and data for views
- Added `prime` file for CLI console
- Added file `composer.json`
- Added `Facades` folder to minimize the performance of declared classes
- Added `Http` class for detects and returns the current URI 
- Added `Translator` class, parses the language string for a file
- Added `Uri` class for return the base URL string
- Added `ParserEnv` class for parse and load file `.env`
- Added `ProviderService` class for initialize all the services
- Added `RoutingServiceProvider` class what register events in the routes
- Added `RouteServiceProvider` class what register the namespace of controllers and the file of routes
- Added `ServiceProvider` class for the register of providers and groups of classes 
- Added `ViewServiceProvider` class for initialize the services of the views
- Added `BSD 3-clause` "New" or "Revised" license
- Added `/app` folder for create appilcations web
- Added `/bootstrap` folder as starter system of framework 
- Added `/config` folder for the configuration framework
- Added `/resources` folder for use files the views reference as: css, js, lang and views
- Added `/routes` folder with `web ` file as routes loader
- Added `readme.md` file