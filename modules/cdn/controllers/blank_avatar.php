<?php

/**
 * Name:			Blank Avatar
 * Description:	Generates a blank avatar
 * 
 **/

//	Include _cdn.php; executes common functionality
require_once '_cdn.php';

/**
 * OVERLOADING NAILS' CDN MODULES
 * 
 * Note the name of this class; done like this to allow apps to extend this class.
 * Read full explanation at the bottom of this file.
 * 
 **/

class NAILS_Blank_avatar extends NAILS_CDN_Controller
{
	protected $_fail;
	protected $_man;
	protected $_woman;
	protected $_width;
	protected $_height;
	protected $_sex;
	protected $_cache_file;
	
	
	// --------------------------------------------------------------------------
	
	
	/**
	 * Construct the class; set defaults
	 *
	 * @access	public
	 * @return	void
	 * @author	Pablo
	 * 
	 **/
	public function __construct()
	{
		parent::__construct();
		
		// --------------------------------------------------------------------------
		
		//	'Constant' variables
		$this->_man			= $this->_cdn_root . '_resources/img/avatar_man.png';
		$this->_woman		= $this->_cdn_root . '_resources/img/avatar_woman.png';
				
		// --------------------------------------------------------------------------
		
		//	Determine dynamic values
		$this->_width		= $this->uri->segment( 3, 100 );
		$this->_height		= $this->uri->segment( 4, 100 );
		$this->_sex			= $this->uri->segment( 5, 'man' );
		
		//	Set a unique filename (but one which is constant if requested twice, i.e
		//	no random values)
		
		$this->_cache_file	= 'blank_avatar-' . $this->_width . 'x' . $this->_height . '-' . $this->_sex . '.png';
		
		// --------------------------------------------------------------------------
		
		//	Load phpThumb
		require_once $this->_cdn_root. '_resources/classes/phpthumb/phpthumb.php';
	}
	
	
	// --------------------------------------------------------------------------
	
	
	/**
	 * Generate the thumbnail
	 *
	 * @access	public
	 * @return	void
	 * @author	Pablo
	 **/
	public function index()
	{
		//	Check the request headers; avoid hitting the disk at all if possible. If the Etag
		//	matches then send a Not-Modified header and terminate execution.
		
		if ( $this->_serve_not_modified( $this->_cache_file ) )
			return;
		
		// --------------------------------------------------------------------------
		
		//	The browser does not have a local cache (or it's out of date) check the
		//	cache to see if this image has been processed already; serve it up if
		//	it has.
		
		if ( defined( 'CACHE_DIR' ) && file_exists( CACHE_DIR . $this->_cache_file ) ) :
		
			$this->_serve_from_cache( $this->_cache_file );
		
		else :
		
			//	Cache object does not exist, fetch the original, process it and save a
			//	version in the cache bucket.
			
			//	Which original are we using?
			switch( $this->_sex ) :
			
				case 'female' :
				case 'woman' :
				case 'f' :
				case 'w' :
				case '2' :
				
					$_src = $this->_woman;
				
				break;
				
				// --------------------------------------------------------------------------
				
				case 'male' :
				case 'man' :
				case 'm' :
				case '1' :

					$_src = $this->_man;

				break;

				// --------------------------------------------------------------------------

				//	Fallback to a default avatar
				//	TODO: Make this avatar gender neutral
				default :
				
					$_src = $this->_man;
				
				break;
			
			endswitch;
			
			if ( file_exists( $_src ) ) :
			
				//	Object exists, time for manipulation fun times :>
				
				//	Set some PHPThumbFactory options
				$_options['resizeUp']		= TRUE;
				
				// --------------------------------------------------------------------------
				
				//	Perform the resize (3rd param tells PHPThumbFactory that we're using a
				//	data stream rather than a file).
				
				$thumb = PhpThumbFactory::create( $_src, $_options );
				$thumb->adaptiveResize( $this->_width, $this->_height );
				
				// --------------------------------------------------------------------------
				
				//	Set the appropriate cache headers
				header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s', time() ) . 'GMT' );
				header( 'ETag: "' . md5( $this->_cache_file ) . '"' );
				header( 'X-CDN-CACHE: MISS' );
				
				// --------------------------------------------------------------------------
				
				//	Output the newly rendered file to the browser
				$thumb->show();
				
				// --------------------------------------------------------------------------
				
				//	Save local version
				if ( defined( 'CACHE_DIR' ) && is_writable( CACHE_DIR ) ) :

					$thumb->save( CACHE_DIR . $this->_cache_file );

				elseif( defined( 'CACHE_DIR' ) ) :

					//	Inform developers
					$_subject	= 'Cache (scale) dir not writeable';
					$_message	= 'The CDN cannot write to the cache directory.'."\n\n";
					$_message	.= 'Dir: ' . CACHE_DIR . $this->_cache_file . "\n\n";
					$_message	.= 'URL: ' . $_SERVER['REQUEST_URI'];
					
					send_developer_mail( $_subject, $_message );

				endif;
			
			else :
			
				//	This object does not exist.
				log_message( 'error', 'CDN: Blank Avatar: File not found; ' . $_src );
				return $this->_bad_src( $this->_width, $this->_height );
			
			endif;
			
		endif;
	}
	
	
	// --------------------------------------------------------------------------
	
	
	public function _remap()
	{
		$this->index();
	}
}


// --------------------------------------------------------------------------


/**
 * OVERLOADING NAILS' CDN MODULES
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
 
if ( ! defined( 'NAILS_ALLOW_EXTENSION_BLANK_AVATAR' ) ) :

	class Blank_avatar extends NAILS_Blank_avatar
	{
	}

endif;


/* End of file thumb.php */
/* Location: ./application/modules/cdn/controllers/thumb.php */