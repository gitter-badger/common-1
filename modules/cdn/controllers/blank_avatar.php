<?php

/**
* Name:			Blank Avatar
*
* Created:		20/11/2012
* Modified:		20/11/2012
*
* Description:	Generates a blank avatar
* 
*/

//	Include _cdn_local.php; executes common functionality
require_once '_cdn_local.php';

class Blank_avatar extends CDN_Controller
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
		$this->_fail		= $this->_cdn_root . '_resources/img/fail.png';
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
		
		if ( file_exists( CACHE_DIR . $this->_cache_file ) ) :
		
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
				default :
				
					$_src = $this->_man;
				
				break;
			
			endswitch;
			
			if ( file_exists( $_src ) ) :
			
				//	Object exists, time for manipulation fun times :>
				
				//	Set some PHPThumbFactory options
				$_options['resizeUp']		= TRUE;
				$_options['jpegQuality']	= 80;
				
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
				
				//	Save to the cache, this involves saving a local copy then punting that up to S3
				
				//	Save local version
				$thumb->save( CACHE_DIR . $this->_cache_file );
			
			else :
			
				//	This object does not exist.
				return $this->_bad_src();
			
			endif;
			
		endif;
	}
	
	
	// --------------------------------------------------------------------------
	
	
	/**
	 * Generate a fail image
	 *
	 * @access	public
	 * @return	void
	 * @author	Pablo
	 **/
	protected function _bad_src()
	{
		//	Create the icon
		$_icon = @imagecreatefrompng( $this->_fail );
		$_icon_w = imagesx( $_icon );  
		$_icon_h = imagesy( $_icon );
		
		// --------------------------------------------------------------------------
		
		//	Create the background
		$_bg	= imagecreatetruecolor( $this->_width, $this->_height );
		$_white	= imagecolorallocate( $_bg, 255, 255, 255);
		imagefill( $_bg, 0, 0, $_white );
		
		// --------------------------------------------------------------------------
		
		//	Merge the two
		$_center_x = ( $this->_width / 2 ) - ( $_icon_w / 2 );
		$_center_y = ( $this->_height / 2 ) - ( $_icon_h / 2 );
		imagecopymerge( $_bg, $_icon, $_center_x, $_center_y, 0, 0, $_icon_w, $_icon_h, 100 );
		
		// --------------------------------------------------------------------------
		
		//	Output to browser
		header( 'Content-type: image/png' );
		imagepng( $_bg );
		
		// --------------------------------------------------------------------------
		
		//	Destroy the images
		imagedestroy( $_icon );
		imagedestroy( $_bg );
	}
	
	
	// --------------------------------------------------------------------------
	
	
	public function _remap()
	{
		$this->index();
	}
}


/* End of file thumb.php */
/* Location: ./application/modules/cdn_local/controllers/thumb.php */