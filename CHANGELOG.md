# Release Notes

## [Version 0.8.13-alpha.8]

Release Date: Jan 31, 2025

**Next alpha release of Lenevor**

### Added
- Added methods for handle Request and Prime command
- Added new methods for initialize the configuration of a new application
- Added methods for register the resolving callback in the `Container` class
- Added argument for raise events in the resolve method in the `Application` class
- Added new methods for register the reportable and renderable in the `ExceptionHandler` interface
- Added new methods for register the before and after of resolving callback in the `Container` interface
- Added new variable for the commands to be registered of development in the `PrimeServiceProvider` class
- Added new method for get the character at the specified index in the `Str` class
- Added method for check an array attribute in the `Validator` class
- Added new methods for validate inputs, rules and messages in the `Validator` class
- Added new method for register the database presence verifier in the `ValidationServiceProvider` class
- Added new trait for get configuration files in the `Configure` class 
- Added new method for gets the URL generator instance in the `Redirector` class

### Changed
- Changed the name of method withExceptions by assignExceptions in the `ApplicationBootstrap` class

### Updated
- Updated PHP release to 8.2
- Updated components release to 0.8.13

### Fixed
- Added character '\' for identify at closure function in the `Support\Helpers` file

### Removed
- Removed the identify final in the class in the `Rules\Accepted` class


## [Version 0.8.12-alpha.8]

Release Date: Apr 01, 2024

**Next alpha release of Lenevor**

### Added
- Added new methods for get the recipient's email address and name
- Added methods for gets the encoded text stream
- Added methods for iterate messages of email in `RawMessage` class
- Added new methods for settings the senders and recipients
- Added new methods for the sent messages at recipients email
- Added new variables for gets the MIME types and $guessers
- Added as abstract class and implements Transport interface
- Added the extends the `Envelope` class
- Added method for sets the maximum number of messages to send per second in `AbstractTransport` class
- Added the call to variables for generate the URL and the connection with the socket
- Added methods for use the debug when be send to mailbox
- Added variables for allows the authentication of user
- Added the SmtpTransport class as an extends and the __construct() method
- Added method for gets the string of URL from domain
- Added the implementation of the `Mailbox` interface
- Added the interface using Renderable for call render() method
- Added methods for get this header rendering with encoded name in `Address` class
- Added methods for create a parse of data mailing
- Added new methods for make a list of address a header body
- Added new methods for gets the text and html in content body
- Added new method for send a new message synchronously in `Mailer` interface
- Added new methods of Log and Array transport
- Added new item for relationated to the `Mail` class
- Added new item for relationated to the `MailManager` and `Mailer` class

### Changed
- Changed filename .env.dist by .env.example

### Created
- Created class for initialized the callback with the given locale in `Localizable` trait

### Updated
- Updated components release to 0.8.12

### Fixed
- [0.8.x] Fix bug the name of the `IdentificationMessageHeader` class


## [Version 0.8.11-alpha.8]

Release Date: Nov 21, 2023

**Next alpha release of Lenevor**

### Added
- Added new variables and methods for get an item of the pages
- Added new methods for get the pagination with URLs in `AbstractPaginator` class
- Added methods for get the number of items in paginated
- Added new methods for generate the render of paginator using a given view
- Added method for the pagination state using the application container in `PaginationState` class
- Added item in array to the pagination service provider
- Added methods for collections of data in `AbstractPaginator` class
- Added interface SimplePaginator and render method is formatted
- Added methods for generate the complete pagination and simple pagination
- Added methods for generate a complete and simple pagination in `Erostrine\Builder` class
- Added methods for get the number to return per page in `Model` class
- Added magic method __get() for access builder proxies
- Added new method for parse a string, buts preserving dots in variable names
- Added new method for parse a string, buts preserving dots in variable names in `Helpers\RequestUtils` class
- Added static method for loaded the query string of a url in `PaginationState` class
- Added the calling of methods for indicate the pages number to display in `Erostrine\Builder` class
- Added the all() method for load the items that to use in `Paginator` and `SimplePaginator` class
- Added method for render the contents of the paginator to HTML

### Changed
- Changed the method count() by getCountForPagination() in `Erostrine\Builder` class

### Created
- Created the `Serializable` interface for string serializable or string unserializable
- Created new view for generate pagination of registers of database
- Created new method for run a map over of the items in the array in `Arr` class
- Created the messages of pagination for translation

### Updated
- Updated components release to 0.8.11

### Fixed
- [0.8.x] Fix bug removing the some arguments type in __construct() method in `Paginator` class
- [0.8.x] Fix bug at call a method not exist in `AbstractPaginator` class
- [0.8.x] Fix bug removing the type and output of argument in `Connection` class
- [0.8.x] Fix bug for changed by getLine() to get() method
- [0.8.x] Fix bug the type of output bool to string in Arr::query

### Removed
- Removed is methods reference the window of URLs in the pages


## [Version 0.8.10-alpha.8]

Release Date: Oct 24, 2023

**Next alpha release of Lenevor**

### Added
- Added arguments in required methods by the ArrayAccess interface
- Added identify of integer in argument of the `Log\Handlers\FileLogger` class
- Added the type output of method of the DateTime class
- Added argument type to match with the DateTime class in `Support\Chronos\Traits\Factory` trait
- Added methods for register a view file namespace in the `ServiceProvider` class

### Changed
- Changed the container notation by method of the `Application` class

### Created
- Created new class for pagination of database register 

### Updated
- Updated PHP release to 8.1
- Updated components release to 0.8.10

### Removed
- Removed Serializable Interface and deprecated in PHP 8.1


## [Version 0.8.9-alpha.8]

Release Date: Oct 10, 2023

**Next alpha release of Lenevor**

### Added
- Added new methods for manages the format of strings in console
- Added new method for wrap a color style tag
- Added conditional for paths iterate in `FinderHelper` class
- Added iterator to go through the iterators one by one in `Finder` class
- Added new methods for returns an iterator of directory in `DirectoryFilterIterator` class
- Added new method for allows rules that filenames in `Finder` class
- Added new methods for gets the hash and the content as decoded JSON in files
- Added the connection with the methods of `Filesystem` class
- Added new methods for resolve the given disk in `FilesystemManager` class
- Added new methods for get the disk local in `FilesystemManager` class
- Added methods for call a custom driver creator in `FilesystemManager` class
- Added new array related with the filesystem in `Application` class
- Added new methods for register the filesystem manager

### Changed
- Changed new the path for the classes of description
- Changed new the path for the class of Descriptor interface
- Changed at a new folder to system of translation
- Changed the name of method getSize() by size() in `Filesystem` class

### Fixed
- [0.8.x] Fix bug to removed argument not existing in `FormatUtil` class
- [0.8.x] Fix bug of variable misspelled in `FinderHelper` class

### Created
- Created Helper class for the base class for all helper classes
- Created the config for manage the files system

### Updated
- Updated release to 0.8.9

### Removed
- Removed the keys for translation items of system main


## [Version 0.8.8-alpha.8]

Release Date: Sep 12, 2023

**Next alpha release of Lenevor**

### Added
- Added conditional for verify variable if have data in the `Benchmark` class
- Added conditionals for have control of elements to header from `Resources/compiled/js/debug.base.js` in debug system
- Added new keys for call to path of views in `PleasingPageHandler` class
- Added new more elements for list of editors in `PleasingPageHandler` class
- Added new tags for config of the debugging
- Added new commands for use the console 

### Changed
- Changed the conditional for identify element HTML in your class from `Resources/compiled/js/debug.base.js` in debug system

### Updated
- Updated release to 0.8.8

### Removed
- Removed the get() method in `CacheManager` class
- Removed 'return' PHP function from `ApplicationDescription` class in console system


## [Version 0.8.7-alpha.8]

Release Date: Aug 02, 2023

**Next alpha release of Lenevor**

### Added
- Added the `decodePath()` method for use the uri given in the `URI` class
- Added the others missing arguments of the `NotFoundHttpException` class
- Added new functions to expand features of the framework
- Added new methods to get the requested from the flashed
- Added new key with value of a path of view for translation
- Added key for call the debug service provider
- Added method PHP `array_reverse()` in `GDebug` class
- Added new key for translate titles and paragraphs in the debugging
- Added the editor key for assigned the value of handler register
- Added the tags HTML for show content `$_SERVER` in a list
- Added the types of argument for identify to parameter
- Added the keys for translation string in template of exceptions
- Added new methods for defined middleware groups
- Added new interface of debugging

### Changed
- Changed the method `Arr::get()` by the helper `data_get()`
- Changed the file names of view to be organize in folders
- Changed the key for a new translation value
- Changed path the `DebugHandler` class to the Debugging folder
- Changed the namespace Debug by Stopwatch
- Changed the folder previous by the Stopwatch folder
- Changed the class of exception previous by MissingAppKeyException
- Changed the PHP get_class() function by getClass() of helper
- Changed the value of quantity of code lines
- Changed the value of argument for generate a quantity of code lines to show
- Changed the values hexadecimals for generate color

### Created
- Created trait to using flash in input of a session
- Created the interface for render the exception found
- Created a new Debug exception render instance
- Created debug service provider

### Removed
- Removed the keys of `ArrayTable` class


## [Version 0.8.6-alpha.8]

Release Date: Jun 22, 2023

**Next alpha release of Lenevor**

### Added
- Added new method for returns a default value of the given value
- Added the `isAlias($id)` method in `bound($id)` and Formatting
- Added conditional for verify if is null the $name variable in the `CacheManager` class
- Added new methods for know it exist been registered of bindings in the `Container` class
- Added the type of value that content the APP_DEBUG constant in the `config/app` file
- Added new method to get the default services providers in the `servicerProvider` class
- Added the new methods to extender closure for services in the `Container` class
- Added new methods for current Request and session resolver
- Added new methods for current Request and session resolver in the `UrlGenerator` class
- Added method for prepare bindings for update in the `Query/Builder` class
- Added new methods for convert the route a bundle route

### Changed
- Changed the name of the class `CallboundCallback` by `CallBoundMethod`
- Changed the position some arguments of `compareUri()` method in the `RouteRequestMatchesGiven` class
- Changed the name of the class `RouteParameterBinding` by `RouteParameter`

### Fixed
- [0.8.x] Fix bug to remove some arguments of the methods `rtrim()` and `compareUri()` in the `BaseRouteCollection` class
- Fixed the namespace to commented heading on createBcryptDriver() method in the `HashManager` class

### Created
- Created new classes for allows the check and verification of the hash given value with `Argon2i` and `Argon2id`
- Created new classes for route describes from `application bundle`

### Updated
- Updated release to 0.8.6
- Updated provider key to merge the provider collection in the `config/services` file

### Removed
- Removed PHP method array_filter in the `Route` class


## [Version 0.8.5-alpha.8]

Release Date: Jun 09, 2023

**Next alpha release of Lenevor**

### Added
- Added the getter methods for the CompiledRoute class
- Added method for handle the matched route
- Changed the property of class final by abstract
- Created class for gets the route for parse
- Added the getCheckedRoutes() method for verify if has matches in route
- Added the type output of method and method for pattern loop routes
- Added method for compile patterns of routes
- Added new methods for returns the compilation of pattern in the routes
- Added new method for the parameter list form the host in the request
- Added new method for get the compiled version of the route

### Changed
- Changed the regular expression to load route parameters
- Changed to shorthand function for PHP 8.0 version
- Changed the path of the RouteCompiler class at `Generator` folder
- Changed the name method getRoute() by getUri() in the `RouteResponse` class

### Fixed
- [0.8.x] Fix bug of given parse URI in the `Route` class

### Created
- Created a new interface for validate rules
- Created new controller for redirect the routes easily

### Updated
- Updated release to 0.8.5

### Removed
- Removed the static word in method and variables with static name


## [Version 0.8.4-alpha.8]

Release Date: Abr 29, 2023

**Next alpha release of Lenevor**

### Added
- Added new trait for lets know if it is precognitive in the `Request` class
- Added the static type output of method
- Added new methods for get the ViewErrorBag usin JSON
- Added loop for can delete memory in the `Session\Store` class
- Added the type output of method in the interfaces
- Added new method for parses a Cache-Control HTTP header in the `Utilities\Headers` class
- Added the type of parameter that have for the arguments
- Added test for activate the foreground and background of colors in console
- Added method to call the string expression recognized in the database in the `Grammar` class

### Changed
- Changed to shorthand function for PHP 8.0 version
- Changed the method name shutdown() by finalize()
- Changed method deprecated getClass() by getType()
- Changed the type output of method self to static
- Changed reset() PHP method to headItem() helper method

### Fixed
- Fix bug in use the comparison operators in the `Output` class


## [Version 0.8.3-alpha.8] 

Release Date: Feb 15, 2023

**Next alpha release of Lenevor**

### Added
- Added middlewares for authentication of users
- Added new methods for the validation errors as messages to the view
- Added methods for show errors to the session
- Added the conditionals of authentication by mean of plaze template engine
- Added new function of the available auth instance
- Added the count() method for results by the iterators
- Added static method for calls the Finder class
- Added new methods and namespaces for gets the results of search
- Added the dark theme to the missing HTML tags
- Added the file filter iterator for search onto path of files

### Changed
- Changed to shorthand function for PHP 8.0 version

### Fixed
- Fix bug in the namespace of the Auth/Factory interface
- Fix bug of filter for friendly URLs
- Fix bug in the pattern to given comparator of operators in Finder system
- Fix bug in the numeration value of $matches variable

### Created
- Created the RedirectResponseHeader class
- Created new methods for the authentication users into valid PHP
- Create the Finder system


## [Version 0.8.2-alpha.8] 

Release Date: Jan 08, 2023

**Next alpha release of Lenevor**

### Changed
- Changed release to 0.8.2-alpha.8
- Changed the year of copyright in comments heading of class
- Changed to shorthand function for PHP 8.0 version in some of the classes to framework
- Changed is other form to color style in console command in the `TextDescirptor` class

### Updated
- Updated the variable type for names in the `RouteDependencyResolver` class


## [Version 0.8.1-alpha.8] 

Release Date: Dec 31, 2022

**Next alpha release of Lenevor**

### Added
- Added new methods for refresh instance on the given target and method in the `Container` class
- Added the key / value of the auth in routeMiddleware
- Added method for resolve the given guard
- Added the key / value for call the service of auth
- Added new methods for determine the current request of JSON response
- Added new items for start session of user
- Added new key / value for the authentication basic of user
- Added new method for create session driver
- Added new method for determine if the request of a prefetch all completed
- Added StartSesion middleware in the register of the `SessionServiceProvider` class
- Added method for generates a normalized URI for the request
- Added method for the route URL generator instance
- Added new methods for redirected response in `RouteResponse` class
- Added new colors for dark mode as theme for debug system
- Added the comments at started of class and assign a trait in `Access/Gate` class
- Added new method for the authorization of exceptions if response was denied in `Access/Response` class
- Added methods for register the service of authentication
- Added the `InteractsIO` trait in the `Console/Command` class 
- Added the comments in heading in all the methods of the classes
- Added variable as array for loaded of path in `Finder` class

### Fixed
- Fix bug the error of namespace in `config\Auth.php`

### Changed
- Changed release 0.8.1-alpha.8
- Changed the value at the constant of STATUS in `Version` class
- Changed the HTML Code for show text in the web editor
- Changed method getClass() by getType()->getName() in the `Container` class

### Created
- Created Authenticate middleware allow the user is logged using redirected
- Created Authentication class to using with the facade
- Created new event class for get the failed user
- Created new event class for get the authenticated device logout user
- Created new middleware for determine if the user is logged
- Created the Recaller class for allows has the recaller in a cookie
- Created new class for the start session allows authenticate logged on users
- Created Gate class for authorized to the users logged
- Created class for called the user model connection to database
- Created a new interface for get a entity has a given ability 

### Removed
- Removed the type output of method in shouldUse
- Removed the type output of method and added new argument in `StateGuard` interface
- Removed the variable that generated count of the string in `Finder` class
- Removed the properties phpdoc of author in the heading in all the classes


## [Version 0.8.0-alpha.8] 

Release Date: Oct 10, 2022

**Next alpha release of Lenevor**

### Added
- Added new comments for identify the parameters of the __construct() method
- Added $this as argument in the `compileRoute` method from the `Route` class
- Added javascript code for get the dark mode in localStorage
- Added new class for displays an incorrect namespace in console 
- Added methods for input interactive in the `Console\Input\Input` class
- Added conditionals for gets (no interaction) in the inputs
- Added new method for returns the client Ip address
- Added variable for gets a request of the client ip
- Added the `MakeQueries` trait for use the first() function
- Added conditional for using the links of login and register
- Added 'HOME 'variable for are users authencticated and routes redirected
- Added parameter of null in argument of the validateIP method
- Added new interface for verify a hash given value
- Added new service provider for hashing
- Added conditional for checks if exist a group of routes of controller
- Added the $testNow static property in the `Factory` trait

## Changed
- Changed is the MiddlewareResolver class to the Resolver folder
- Changed position of variable in the `addResourceEdit()` method
- Changed is the `RouteResolver` class to the Resolver folder
- Changed is the `AwaitingResourceRegistration` class to the Resources folder
- Changed is the `ResourceRegister` class to the Resources folder
- Changed is the `RouteFileRegister` class to the Resources folder
- Changed is the `RouteRegister` class to the Resources folder
- Changed is the `Pipeline` class to the Supported folder
- Changed the regex for found uri from the `RouteParameterBinding` class
- Formatting and the connection with database store of the cache

### Created
- Created new trait for returns the HTTP requests
- Created is the UriMatches class for check a request url matches given route
- Created new class for allows to handle throwables running a command
- Created the class for detecting client IP address
- Created new `Attempting` class as authentication event
- Created new Authenticated class as the authenticated user event
- Created method for parse the encryption key
- Created method for instance of the cookie session driver
- Created method for remove any duplicate middleware

### Removed
- Removed array in `$this->commands` variable in the `Console\Application` class


## [Version 0.7.10-alpha.7] 

Release Date: Jul 06, 2022

**Next alpha release of Lenevor**

### Added
- Added new method for string formatting of output
- Added the type output of method in `SqlServerGrammar` class
- Added new namespace and the type output for some methods
- Added identify '?' for formated of null in variable of `Chronos/Factory` trait
- Added new variable for application namespace
- Added new variables for autoloading in `Autoloadconfig` class
- Added new method for get alls an application namespace in `Core/Application` class
- Added new function for get autoloader of classes and namespace in `Core/Helpers` file
- Added the identify of parameter Container in `View/Factory` class
- Added conditional if variable is boolean in `Http/Response` class
- Added new method for call to the commands in `app/Console/Lenevor` class
- Added new methods for return ocurrence of a given value in `Support/Str` class
- Added new methods for session in the `request` class
- Added methods only and except for specified list of items in `Http/Concerns/InteractsWithInput` class
- Added conditional if exist 'session.store' in `RoutingServiceProvider` class
- Added class for normalize the query string in the `request` class
- Added new cookie function in `core/Helpers` file
- Added new methods for management of cookies in `Http/Response/ResponseHeaders` class
- Added new parameter for uses the attributes of a request in `Http/Request` class
- Added new methods for get and remove the data of a session in `Contracts/session/Session` interface

### Fixed
- Fix bug in return variable of method
- Fix bug in variables in `SqlServerGrammar` class 
- Fix bug to named correct the function of helper

### Changed
- Changed release 0.7.10-alpha.7
- Changed is `stub` extension to the new extension `tpl` for template files
- Changed the regex in the methods that is uses in the `Routing/RouteCollection` class

### Created
- Created the `SessionNotFoundException` class
- Created new session handlers
- Created is `Cookie/CokkieManager` class
- Create is `Http/Concerns/BuildCookieHeader` trait
- Created is new `Http/Concerns/ResponseLoads` trait for loads the responses
- Created the `Http/Request/RequestUtils` class
- Created the `Http/Response/ResponseHeaders` class
- Created new methods for the http protocol version in `Http/Concerns/ResponseLoads` trait
- Created new methods for returns the headers in the `Http/Response/ResponseHeaders` class

### Moved
- Moved class of Collections to Routing folder


## [Version 0.7.9-alpha.7] 

Release Date: May 01, 2022

**Next alpha release of Lenevor**

### Added
- Added the Route facade import in `routes/web.php`
- Added method for eager load the relationships in models
- Added methods for the loaded relations
- Added new methods for instantiate relationship of tables
- Added exception for relation not found
- Added relation hasMany for given the parent model
- Added relation hasOne for given on the parent model
- Added relation hasOneOrMany for given on the parent model
- Added method gets the query builder
- Added method setRelation() of query builder
- Added new methods for returns items of a collection
- Added new method for related instance for the given model
- Added variable $with for relations to eager load in query
- Added new method for create a new pivot model
- Added methods for get the collection of items in array and callback
- Added methods for array records to insert and update in the `concerns/InteractsWithPivotTable` trait
- Added new methods with where clauses to the query
- Added new methods for get a schema builder and grammar
- Added method for determine if an item exists in the `Collection` class
- Added new methods for indicate the columns that requires to a table
- Added new methods for using dot notation in items
- Added new method for get operators checker callback
- Added new methods for compare two models have the same ID
- Added variable for using to string length by default
- Added new methods for create name column on the tables in the `Schema/Dataprint` class
- Added the type output of method and new item for database schema builder in the `Application` class
- Added new methods for modify | create tables on the schema builder
- Added new method for wrap arrays of values
- Added new methods for manipulates database, tables and columns
- Added new methods for compile all commands on the columns
- Added new methods for defined any column type for call in the tables
- Added pattern for segments values of search in the `Database/Grammar` class
- Added new method for quote the given string in the `Database/Grammar` class
- Added new method for the SQL needed to rebuild the database

### Fixed
- Fix bug at call the word reserved 'return'
- Fix bug in call bad the clause of the database
- Fix bugs in use namespace and type output of parameter by define
- Fix bug for declaration change of protected to public in the `BuildConsoleVersion` trait

### Changed
- Changed release 0.7.9-alpha.7
- Changed method name for call model
- Changed method name of access() to accessible() in the `Arr` class

### Created
- Created collection of models
- Created new trait for support model relations
- Created new facade for initialize the database schema builder

### Removed
- Removed namespaces without using
- Removed the type output on compileForeign method in the `Grammars/Grammar` class
- Removed the type of output method in the `Builders/Builder` class


## [Version 0.7.8-alpha.7] 

Release Date: Feb 14, 2022

**Next alpha release of Lenevor**

### Added
- Added new method for calls to `query builder`
- Added method for bootstrap the database connection resolver
- Added extend of collection
- Added the type output of much methods by the php 7.4 version
- Added method PHP `func_get_args()` for incorporate arguments
- Added trait and method for connection of database
- Added variable for relation of tables
- Added methods of many `where` clause
- Added new methods for differents `where` clause to query
- Added new methods for enabled and restrictions attributes in the `model`
- Added new method for fire an event of null-non responses
- Added methods for get and convert items in json from `Enumerates` trait
- Added new methods for save and delete data in `model`
- Added new method for a given connection in `model`
- Added `Macroable` trait
- Added conditional for identify the pluralize of a class
- Added methods for generate relationship in the `model`
- Added new method for parser the processes command line
- Added new method for generate a new command line
- Added new function for replace a given pattern in `helper`

### Fixed
- Fix bug for call method incorrect in the `Query builder`
- Fix bug in named variables incorrectly in the `Query builder`
- Fix bugs in the called into function helper in `Grammar` database
- Fix bug of method not invoke in the processor database
- Fix bug for collection of data in columns in the `Query builder`
- Fix bug of caracter special in variable from `ManagesTransactions` trait

### Changed
- Changed comment for word reserved in console system
- Changed the file to path whitin of the folder `Erostrine`
- Changed namespace for the `ModelNotFoundException` class
- Changed Release 0.7.8-alpha.7

### Created
- Created methods for connection resolver instance
- Created `Manager` class in database component
- Created trait for gets attributes on the `model`
- Created trait for manage events in the model
- Created class for identify words in irregular plural
- Created class for identify words in plural
- Created class for identify words in singular
- Created class for identify words in uncountable

### Removed
- Removed the type of parameter in `FlattenException` class


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