<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Default Kohana controller. This controller should NOT be used in production.
 * It is for demonstration purposes only!
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */

class Welcome_Controller extends Template_Controller {

	// Disable this controller when Kohana is set to production mode.
	// See http://docs.kohanaphp.com/installation/deployment for more details.
	const ALLOW_PRODUCTION = FALSE;

	// Set the name of the template to use
	public $template = 'kohana/template';

	public function index()
	{
		// In Kohana, all views are loaded and treated as objects.
		$this->template->content = new View('welcome_content');

		// You can assign anything variable to a view by using standard OOP
		// methods. In my welcome view, the $title variable will be assigned
		// the value I give it here.
		$this->template->title = 'The route way.';

		// An array of links to display. Assiging variables to views is completely
		// asyncronous. Variables can be set in any order, and can be any type
		// of data, including objects.
		$this->template->content->links = array
		(
			'Home'		=> '../../index.php',	
			'Prep Guide'   	=> 'http://lcamtuf.coredump.cx/prep/',
			'Flag'        	=> '/welcome/flag',
		);
	}

	public function flag()
	{
	    $this->template->content = new View('welcome_content');
	    $this->template->title = 'libctf{cee7eb50-946a-42c5-a112-63eb26c6dbd7}';
	    $this->template->content->links = array();
	}

	public function __call($method, $arguments)
	{
		// Disable auto-rendering
		$this->auto_render = FALSE;

		// By defining a __call method, all pages routed to this controller
		// that result in 404 errors will be handled by this method, instead of
		// being displayed as "Page Not Found" errors.
		echo __('This text is generated by __call. If you expected the index page, you need to use: :uri:',
				array(':uri:' => 'welcome/index/'.substr(Router::$current_uri, 8)));
	}

} // End Welcome Controller
