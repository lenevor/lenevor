# Release Notes

## [Version 1.0.0-stable]
Releade Date: 


## [Version 1.0.0-release-candidate.1]

Release Date: 


## [Version 1.0.0-beta.2]

Release Date:


## [Version 1.0.0-beta.1]()

Release Date: 


## [Version 0.9.4-alpha.17]

Releade Date: April 16, 2019

**Next alpha release of Lenevor**

### Added
- Added new interface `Table` in `Contracts/Debug`
- Added news classes `ArrayTable` and `TableLabel` for gets the key label and data value
- Added the folder `Resources` in Debug for use views

### Changed
- 


## [Version 0.9.3-alpha.16]

Releade Date: April 2, 2019

**Next alpha release of Lenevor**

### Added
- Added `ExceptionHandler` folder for manage the debugging and views error
- Added new attributes at constructor of exceptions class 

### Changed
- Changed tabulating all files
- Moved folders to `classes/Debug` from `classes/Core/Debug`


## [Version 0.9.2-alpha.15]

Release Date: March 15, 2019

**Next alpha release of Lenevor**

### Added
- Added the `filesystem` system for create, read, delete, copy, rename, move files and folders

### Changed
- Update Version class in constant `RELEASEDATE`


## [Version 0.9.1-alpha.14]

Release Date: February 24, 2019

**Next alpha release of Lenevor**

### Added
- Added `ParserEnv` class for parse and load file `.env`
- Added `ProviderService` class for initialize all the services
- Added `RoutingServiceProvider` class what register events in the routes
- Added `RouteServiceProvider` class what register the namespace of controllers and the file of routes
- Added `ServiceProvider` class for the register of providers and groups of classes 
- Added `ViewServiceProvider` class for initialize the services of the views


## [Version 0.9.0-alpha.13]

Release Date: February 14, 2019

**Next alpha release of Lenevor**

### Added
- Added `AliasLoader` class for load a class alias if it is registered
- Added `Application` class to `app/Console` folder for generate CLI commands
- Added `Lenevor` class to load main of framework
- Added `stubs` folder for replacer in your files a class complete 
- Added `Configure` interface in `Contracts/Config` folder

### Removed
- Removed `AppConsole` class in `app\Console` folder and `ExceptionHandler` class in `app/Exceptions` folder


## [Version 0.8.1-alpha.12]

Release Date: February 9, 2019

**Next alpha release of Lenevor**

### Added
- Added `bootstrapper` system in `Application` class

### Changed
- Changed value of constant `PRODUCT` and add the constant `YEAR`


## [Version 0.8.0-alpha.11]

Release Date: February 4, 2019

**Next alpha release of Lenevor**

### Added
- Added `Bootstrap` folder to `Core` folder for loader configurations main of system
- Added the `Debug` system for debugging errors to `classes/Core` folder


## [Version 0.7.0-alpha.10]

Release Date: January 2, 2019

**Next alpha release of Lenevor**

### Added
- Added method `pushHandler` for call the `PleasingPageHandler` class in the `Handler` class
- Added new methods to the `PleasingPageHandler` class
- Added the conditional for configure the `production` environment

### Changed
- Modify copyright in all the classes


## [Version 0.6.2-alpha.9]

Release Date: November 27, 2018

**Next alpha release of Lenevor**

### Added
- Added `Application` interface in `Contracts/Core` folder
- Added `Lenevor` interface in `Contracts/Core` folder
- Added `Handler` interface in `Contracts/Debug` folder
- Added `Handler` class to `app/Exceptions` folder for loader exceptions own


## [Version 0.6.1-alpha.8]

Release Date: November 20, 2018

**Next alpha release of Lenevor**

### Changed
- Changed namespace to register files and classes


## [Version 0.6.0-alpha.7]

Release Date: September 18, 2018

**Next alpha release of Lenevor**

### Added
- Added `Application` class for loaded bootstrapping
- Added `Exceptions` folder to `core` folder for debugging of errors
- Added `Helpers` for loaded of functions core
- Added `RouteResponse` class for loader content of a view
- Added `View` interface in `Contracts/View` folder


## [Version 0.5.1-alpha.6]

Release Date: August 21, 2018

**Next alpha release of Lenevor**

### Added
- Added `RouteMap` trait in `syscode/classes/Routing` folder


## [Version 0.5.0-alpha.5]

Release Date: August 9, 2018

**Next alpha release of Lenevor**

### Added
- Added `Facades` folder to minimize the performance of declared classes
- Added `Http` class for detects and returns the current URI 
- Added `Translator` class, parses the language string for a file
- Added `Uri` class for return the base URL string


## [Version 0.4.0-alpha.4]

Release Date: August 5, 2018

**Next alpha release of Lenevor**

### Added
- Added CLI system basic
- Added `Contracts` folder order the interfaces with your folders relations to each class implemented
- Added `Routable` interface in `Contracts/Routing` folder


## [Version 0.3.0-alpha.3] 

Release Date: June 22, 2018

**Next alpha release of Lenevor**

### Added
- Added the `cache` system

### Changed
- Changed namespace the Filesystem in `FileMimeType` class
- Simple quotes are removed to double quotes to generate capture variables

### Removed
- Removed `Repository` class in Cache folder


## [Version 0.2.0-alpha.2]

Release Date: May 28, 2018

**Next alpha release of Lenevor**

### Added
- Added `Log` class for register log events
- Added `Server` class for generate server object
- Added `Status` class 

### Changed
- Changed anual copyright update in the classes

### Removed
- Removed `Builder` and `Collection` files of database system


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
- Added `Finder` class for the search path of files
- Added `Helpers` for loaded of functions support
- Added `Request` use headers, segments and verbs HTTP
- Added `Response` use headers and content 
- Added routing system with verbs HTTP, regex and routes map
- Added `Str` class for manipulation of strings
- Added versioning system
- Added `View` use files and data for views
- Added `prime` file for CLI console
- Added file `composer.json`
- Added `BSD 3-clause` "New" or "Revised" license
- Added `/app` folder for create appilcations web
- Added `/bootstrap` folder as starter system of framework 
- Added `/config` folder for the configuration framework
- Added `/resources` folder for use files the views reference as: css, js, lang and views
- Added `/routes` folder with `web ` file as routes loader
- Added `readme.md` file