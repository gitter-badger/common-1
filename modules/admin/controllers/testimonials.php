<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Name:		Testimonials Admin
 *
 * Description:	Manage events
 * 
 **/

//	Include Admin_Controller; executes common admin functionality.
require_once '_admin.php';

/**
 * OVERLOADING NAILS'S ADMIN MODULES
 * 
 * Note the name of this class; done like this to allow apps to extend this class.
 * Read full explanation at the bottom of this file.
 * 
 **/
 
class NAILS_Testimonials extends Admin_Controller {

	/**
	 * Announces this module's details to those in the know.
	 *
	 * @access	static
	 * @param	none
	 * @return	void
	 * @author	Pablo
	 **/
	static function announce()
	{
		$d = new stdClass();
		
		// --------------------------------------------------------------------------
		
		//	Load the laguage file
		get_instance()->lang->load( 'admin_testimonials', RENDER_LANG );
		
		// --------------------------------------------------------------------------
		
		//	Configurations
		$d->name = lang( 'testimonials_module_name' );
		
		// --------------------------------------------------------------------------
		
		//	Navigation options
		$d->funcs['index']		= lang( 'testimonials_nav_index' );
		
		// --------------------------------------------------------------------------
		
		//	Only announce the controller if the user has permisison to know about it
		return self::_can_access( $d, __FILE__ );
	}
	
	
	// --------------------------------------------------------------------------
	
	
	/**
	 * Manage evcents
	 *
	 * @access	public
	 * @param	none
	 * @return	void
	 * @author	Pablo
	 **/
	public function index()
	{
		//	Page Title
		$this->data['page']->title = lang( 'testimonials_manage_title' );
		
		// --------------------------------------------------------------------------
		
		//	Load views
		$this->load->view( 'structure/header',			$this->data );
		$this->load->view( 'admin/testimonials/index',	$this->data );
		$this->load->view( 'structure/footer',			$this->data );
	}
}


// --------------------------------------------------------------------------


/**
 * OVERLOADING NAILS'S ADMIN MODULES
 * 
 * The following block of code makes it simple to extend one of the core admin
 * controllers. Some might argue it's a little hacky but it's a simple 'fix'
 * which negates the need to massively extend the CodeIgniter Loader class
 * even further (in all honesty I just can't face understanding the whole
 * Loader class well enough to change it 'properly').
 * 
 * Here's how it works:
 * 
 * CodeIgniter  instanciate a class with the same name as the file, therefore
 * when we try to extend the parent class we get 'cannot redeclre class X' errors
 * and if we call our overloading class something else it will never get instanciated.
 * 
 * We solve this by prefixing the main class with NAILS_ and then conditionally
 * declaring this helper class below; the helper gets instanciated et voila.
 * 
 * If/when we want to extend the main class we simply define NAILS_ALLOW_EXTENSION_CLASSNAME
 * before including this PHP file and extend as normal (i.e in the same way as below);
 * the helper won't be declared so we can declare our own one, app specific.
 * 
 **/
 
if ( ! defined( 'NAILS_ALLOW_EXTENSION_DASHBOARD' ) ) :

	class Testimonials extends NAILS_Testimonials
	{
	}

endif;


/* End of file testimonials.php */
/* Location: ./application/modules/admin/controllers/testimonials.php */