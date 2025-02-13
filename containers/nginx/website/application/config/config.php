<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Main application config
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2010 Kohana Team
 * @license    http://kohanaphp.com/license
 */

/**
 * Base path of the web site. If this includes a domain, eg: localhost/kohana/
 * then a full URL will be used, eg: http://localhost/kohana/. If it only includes
 * the path, and a site_protocol is specified, the domain will be auto-detected.
 *
 * @default '/kohana/'
 */
$config['site_domain'] = '/';

/**
 * Force a default protocol to be used by the site. If no site_protocol is
 * specified, then the current protocol is used, or when possible, only an
 * absolute path (with no protocol/domain) is used.
 *
 * @default ''
 */
$config['site_protocol'] = '';

/**
 * Name of the front controller for this application. Default: index.php
 *
 * This can be removed by using URL rewriting.
 *
 * @default 'index.php'
 */
$config['index_page'] = 'index.php';

/**
 * Fake file extension that will be added to all generated URLs. Example: .html
 *
 * @default ''
 */
$config['url_suffix'] = '';

/**
 * Length of time of the internal cache in seconds. 0 or FALSE means no caching.
 * The internal cache stores file paths and config entries across requests and
 * can give significant speed improvements at the expense of delayed updating.
 *
 * @default FALSE
 */
$config['internal_cache'] = FALSE;

/**
 * Internal cache directory.
 *
 * @default APPPATH.'cache/'
 */
$config['internal_cache_path'] = APPPATH.'cache/';

/**
 * Enable internal cache encryption - speed/processing loss
 * is neglible when this is turned on. Can be turned off
 * if application directory is not in the webroot.
 *
 * @default TRUE
 */
$config['internal_cache_encrypt'] = TRUE;

/**
 * Encryption key for the internal cache, only used
 * if internal_cache_encrypt is TRUE.
 * The cache is deleted when/if the key changes.
 *
 * [!!] Make sure you specify your own key here!
 *
 * @default TRUE
 */
$config['internal_cache_key'] = 'foobar-changeme';

/**
 * Enable or disable gzip output compression. This can dramatically decrease
 * server bandwidth usage, at the cost of slightly higher CPU usage. Set to
 * the compression level (1-9) that you want to use, or FALSE to disable.
 *
 * [!!] Do not enable this option if you are using output compression in php.ini!
 *
 * @default FALSE
 */
$config['output_compression'] = FALSE;

/**
 * Enable or disable global XSS filtering of GET, POST, and SERVER data. This
 * option also accepts a string to specify a specific XSS filtering tool.
 *
 * @default FALSE
 */
$config['global_xss_filtering'] = FALSE;

/**
 * Enable or disable hooks.
 *
 * @default FALSE
 */
$config['enable_hooks'] = FALSE;
/**
 * Enable or disable displaying of Kohana error pages. This will not affect
 * logging. Turning this off will disable ALL error pages.
 *
 * @default TRUE
 */
$config['display_errors'] = TRUE;

/**
 * Enable or disable statistics in the final output. Stats are replaced via
 * specific strings, such as {execution_time}.
 *
 * @default TRUE
 */
$config['render_stats'] = TRUE;

/**
 * Filename prefixed used to determine extensions. For example, an
 * extension to the Controller class would be named MY_Controller.php.
 *
 * @default 'MY_'
 */
$config['extension_prefix'] = 'MY_';

/**
 * An optional list of Config Drivers to use, they "fallback" to the one below them if they
 * dont work so the first driver is tried then so on until it hits the built in "array" driver and fails
 *
 * @default array()
 */
$config['config_drivers'] = array();

/**
 * Additional resource paths, or "modules". Each path can either be absolute
 * or relative to the docroot. Modules can include any resource that can exist
 * in your application directory, configuration files, controllers, views, etc.
 *
 * @default array()
 */
$config['modules'] = array
(
	// MODPATH.'auth',      // Authentication
);
