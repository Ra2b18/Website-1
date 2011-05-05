<?php defined('SYSPATH') or die('No direct script access.');

// -- Environment setup --------------------------------------------------------

// Load the core Kohana class
require SYSPATH.'classes/kohana/core'.EXT;

if (is_file(APPPATH.'classes/kohana'.EXT))
{
	// Application extends the core
	require APPPATH.'classes/kohana'.EXT;
}
else
{
	// Load empty core extension
	require SYSPATH.'classes/kohana'.EXT;
}

/**
 * Set the default time zone.
 *
 * @see  http://kohanaframework.org/guide/using.configuration
 * @see  http://php.net/timezones
 */
date_default_timezone_set('Australia/Melbourne');

/**
 * Set the default locale.
 *
 * @see  http://kohanaframework.org/guide/using.configuration
 * @see  http://php.net/setlocale
 */
setlocale(LC_ALL, 'en_US.utf-8');

/**
 * Enable the Kohana auto-loader.
 *
 * @see  http://kohanaframework.org/guide/using.autoloading
 * @see  http://php.net/spl_autoload_register
 */
spl_autoload_register(array('Kohana', 'auto_load'));

/**
 * Enable the Kohana auto-loader for unserialization.
 *
 * @see  http://php.net/spl_autoload_call
 * @see  http://php.net/manual/var.configuration.php#unserialize-callback-func
 */
ini_set('unserialize_callback_func', 'spl_autoload_call');

// -- Configuration and initialization -----------------------------------------

/**
 * Set the default language
 */
I18n::lang('en-us');

/**
 * Set Kohana::$environment if a 'KOHANA_ENV' environment variable has been supplied.
 *
 * Note: If you supply an invalid environment name, a PHP warning will be thrown
 * saying "Couldn't find constant Kohana::<INVALID_ENV_NAME>"
 */
if (isset($_SERVER['KOHANA_ENV']))
{
	Kohana::$environment = constant('Kohana::'.strtoupper($_SERVER['KOHANA_ENV']));
}
else
{
	// Set the environment
	Kohana::$environment = strpos($_SERVER['SERVER_NAME'], '.local') === false ? Kohana::PRODUCTION : Kohana::DEVELOPMENT;
}

/**
 * Initialize Kohana, setting the default options.
 *
 * The following options are available:
 *
 * - string   base_url    path, and optionally domain, of your application   NULL
 * - string   index_file  name of your index file, usually "index.php"       index.php
 * - string   charset     internal character set used for input and output   utf-8
 * - string   cache_dir   set the internal cache directory                   APPPATH/cache
 * - boolean  errors      enable or disable error handling                   TRUE
 * - boolean  profile     enable or disable internal profiling               TRUE
 * - boolean  caching     enable or disable internal caching                 FALSE
 */
Kohana::init(array(
	'base_url'		=> '/',
	'index_file'	=> false,
	'caching'		=> Kohana::$environment === Kohana::PRODUCTION,
	'profile' 		=> Kohana::$environment !== Kohana::PRODUCTION,
));

/**
 * Set the exception handler
 */
set_exception_handler(array('ExceptionHandler', 'handle'));

/**
 * Attach the file write to logging. Multiple writers are supported.
 */
Kohana::$log->attach(new Log_File(APPPATH.'logs'));

/**
 * Attach a file reader to config. Multiple readers are supported.
 */
Kohana::$config->attach(new Config_File);

/**
 * Enable modules. Modules are referenced by a relative or absolute path.
 */
Kohana::modules(array(
	// 'auth'       => MODPATH.'auth',       // Basic authentication
	// 'cache'      => MODPATH.'cache',      // Caching with multiple backends
	// 'codebench'  => MODPATH.'codebench',  // Benchmarking tool
	'database'   => MODPATH.'database',   // Database access
	// 'image'      => MODPATH.'image',      // Image manipulation
	'orm'        => MODPATH.'orm',        // Object Relationship Mapping
	// 'unittest'   => MODPATH.'unittest',   // Unit testing
	// 'userguide'  => MODPATH.'userguide',  // User guide and API documentation
	'pagination' => MODPATH.'pagination',
	'dispatcher' => MODPATH.'dispatcher',
	'akismet'	 => MODPATH.'akismet',
	));

/**
 * Set the routes. Each route must have a minimum of a name, a URI and a set of
 * defaults for the URI.
 *
 * If running on staging or production, cache the routes
 */
if (Kohana::$environment >= Kohana::TESTING || !Route::cache())
{

	/*Route::set('indexPage', 'index.htm')
		->defaults(array(
			'controller' => 'site',
			'action'     => 'home'
		));*/

	Route::set('page', '<action>.htm')
		->defaults(array(
			'controller' => 'site'
		));
		
	// TODO: Change "newblog" to "blog"
	Route::set('blog_view', 'newblog/<year>/<month>/<slug>', array('year' => '\d{4}', 'month' => '\d{2}'))
		->defaults(array(
			'controller' => 'blog',
			'action'     => 'view',
		));
	Route::set('blog_category', 'newblog/category/<slug>')
		->defaults(array(
			'controller' => 'blog',
			'action'     => 'category',
		));
	Route::set('blog_tag', 'newblog/tag/<slug>')
		->defaults(array(
			'controller' => 'blog',
			'action'     => 'tag',
		));
	/*Route::set('blog_page', 'newblog/page/<page>', array('page' => '\d+'))
		->defaults(array(
			'controller' => 'blog',
			'action'     => 'index',
		));*/
		
	// Temporary blog route for testing, while the old blog is in use.
	Route::set('blog', 'newblog/<action>(/<id>)')
		->defaults(array(
			'controller' => 'blog',
			'action'     => 'index',
		));	
		
	Route::set('blog_home', 'newblog')
		->defaults(array(
			'controller' => 'blog',
			'action'     => 'index',
		));	
	
		
	// Errors
	Route::set('error', 'error/<action>(/<message>)', array('action' => '[0-9]++', 'message' => '.+'))
		->defaults(array(
			'controller' => 'error'
		));	
		
	Route::set('default', '(<controller>(/<action>(/<id>)))')
		->defaults(array(
			'controller' => 'site',
			'action'     => 'index',
		));
		
	// Only cache on staging or production
	if (Kohana::$environment < Kohana::TESTING)
		Route::cache(true);
}