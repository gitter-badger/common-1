<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Name:		Render
 *
 * Description:	Renders a CMS controlled page
 * 
 **/
 
/**
 * OVERLOADING NAILS'S AUTH MODULE
 * 
 * Note the name of this class; done like this to allow apps to extend this class.
 * Read full explanation at the bottom of this file.
 * 
 **/

//	Include _cdn_local.php; executes common functionality
require_once '_cms.php';

class NAILS_Render extends NAILS_CMS_Controller
{
	private $_slug;
	
	
	// --------------------------------------------------------------------------
	
	
	public function __construct()
	{
		parent::__construct();
		
		// --------------------------------------------------------------------------
		
		$this->load->model( 'cms_page_model', 'cms_page' );
		
		// --------------------------------------------------------------------------
		
		$this->_slug = uri_string();
	}
	
	
	// --------------------------------------------------------------------------
	
	
	public function page()
	{
		$_page = $this->cms_page->get_by_slug( $this->_slug, TRUE );
		
		if ( ! $_page ) :
		
			show_404();
		
		endif;
		
		// --------------------------------------------------------------------------
		
		//	Get the page HTML
		$this->data['page']->title		= $_page->title;
		$this->data['rendered_page']	= $this->cms_page->render( $_page );
		
		$this->load->view( 'structure/header',	$this->data );
		$this->load->view( 'cms/page/render',	$this->data );
		$this->load->view( 'structure/footer',	$this->data );
	}
}



// --------------------------------------------------------------------------


/**
 * OVERLOADING NAILS'S CMS MODULE
 * 
 * The following block of code makes it simple to extend one of the core auth
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
 * If/when we want to extend the main class we simply define NAILS_ALLOW_EXTENSION
 * before including this PHP file and extend as normal (i.e in the same way as below);
 * the helper won't be declared so we can declare our own one, app specific.
 * 
 **/
 
if ( ! defined( 'NAILS_ALLOW_EXTENSION' ) ) :

	class Render extends NAILS_Render
	{
	}

endif;