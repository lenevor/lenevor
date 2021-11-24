# Release Notes

## [Version 0.7.7-alpha.7] 

Release Date: Nov 24, 2021

**Next alpha release of Lenevor**

### Added
- Added method for pad a strings
- Added the word reserved `public` en constants
- Added interface of inputOption for calling to constants
- Added class for show the description of command attributes
- Added conditional for path location of bootstrap file
- Added to a new folder called `ApplicationBundle`
- Added method for parses a short and long option
- Added `setDecorated()` function for activates usage of color tags
- Added new methods for formatting of text output
- Added new methods for formatting of text output
- Added new style formats for text output
- Added conditional to check if string has dots of paths
- Added an alias function name for `make()`
- Added new method for call on any terminable middleware
- Added method for a locale from a given Json

### Fixed
- Fix bug in insert and update of `Query Builder`

### Changed
- Changed the format for to color tags of a text
- Changed the format at representation of SQL parameters
- Changed variable for '?' of string replace

### Created
- Created the `SingleLists` and `MultiLists` classes
- Created class for configure the color to use in any text

### Removed
- Removed classes for frame of arguments
- Removed methods for format of arguments 
- Removed tags for frame of arguments
- Removed identify of method output
- Removed `preg_replace` method PHP


- Added new methods for environment local
## [Version 0.7.6-alpha.7] 

Release Date: Sep 12, 2021

**Next alpha release of Lenevor**

### Added
- Added method for load the deferred provider
- Added the methods to create a collection which using callback
- Added new function for return the given value
- Added type of parameter for implement Container interface
- Added a route compiler for parameters capture
- Added loop for get compiler route pattern
- Added methods for setting console
- Added method call for used given callable or class@method
- Added in array the interface implementation in the key of the Translator
- Added interface of `Translator` and added new methods for the default locale
- Added SVG tag for show logo of framework
- Added script of javascript for classes toggle
- Added method for resolve command instance
- Added methods for register the conditions of development code web
- Added new method for create the transpiled file directory
- Added method for register the literal blocks
- Added method for generate form field to spoof HTTP verb
- Added new method for checks the request specified type
- Added new method for retrieve a query string in request HTTP
- Added new class referring normalized HTTP requests
- Added variables and methods for returns the given text with color codes
- Added variables and methods for outputs a string to the CLI command
- Added new method for returns of console formatted for comments
- Added method for show version of the application
- Added new argument of `app->version()`
- Added new methods for sets style foreground, background and options defining style
- Added new methods where formatter show console output messages
- Added methods for output formatter
- Added methods for output to a given stream
- Added new methods for replace tags in color codes output
- Added method for color style from a parameter string
- Added methods for represents a command line argument
- Added methods for shutdown the application
- Added method for replaces tag on command console
- Added variable for alias of command name of the class
- Added the code when execute a command
- Added conditional for enabled of command errors
- Added method and variable for verbosity
- Added configures of input and output instances
- Added class for list global option of commands

### Fixed
- Fix bug for include namespace `ReflectionFunctionAbstract`
- Fix bugs in loops and conditionals

### Changed
- Changed name of theme to light-theme class
- Changed result output to string in console system
- Changed the class name of styles and added method of rendered styles
- Changed configure() method at __construct method

### Created
- Created new class for arrays of autoloading
- Created new interface for loads messages for the given locale
- Created new interface for gets the translations of text lines
- Created new class for loads the translation messages to from an array
- Created method with alias of isXmlHtmlRequest()
- Created interface for defining styles
- Created ANSI Code for use the colors and options format on CLI command
- Created interface for input to CLI arguments
- Created trait for show internal commands

### Removed
- Removed type of parameter
- Removed loadLegacy method in `Autoloader` class
- Removed keys with your respective values
- Removed call at methods setStyle
- Removed indentificated of output arguments in method
- Removed identificater of method output


## [Version 0.7.5-alpha.7] 

Release Date: Mar 16, 2021

**Next alpha release of Lenevor**

### Added
- Added call the given take function in bootstrap of framework
- Added method for determine if has been disable all middleware's
- Added function isCli()
- Added function for activate the command line
- Added methods for register middlewares
- Added variables for groups and routes of middleware
- Added new class for include or exclude methods of middlewares
- Added methods for route resolver callback
- Added methods for register the report and the render to callback
- Added method for load the deferred provider

### Changed
- Changed to environment method of application function helper

### Created
- Created the event function for dispatcher of events and call of listeners
- Creates method for call invokable method

### Removed
- Removed path for requirements file


## [Version 0.7.4-alpha.7] 

Release Date: Feb 09, 2021

**Next alpha release of Lenevor**

### Added
- Added methods of checked of variables of environment
- Added class for gets to all the adapters in `Dotenv` class
- Create class for allows will load the .env
- Added method for get a class with ReflectionClass

### Fixed
- Fix missing parameter problem

### Updated
- Updated comments


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