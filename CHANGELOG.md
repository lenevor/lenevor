# Release Notes

## [Version 0.2.0-alpha.2]

Release Date: May 7, 2019

**Next alpha release of Lenevor**

### Changed
- Changed Release 0.2.0-alpha.2


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