<?php

/**
 * Name:		Thumb
 *
 * Description:	Generates a thumbnail of an image
 * 
 **/

//	Include _cdn_local.php; executes common functionality
require_once '_cdn_local.php';

class Thumb extends CDN_Controller
{
	protected $_fail;
	protected $_bucket;
	protected $_width;
	protected $_height;
	protected $_object;
	protected $_extension;
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
		
		//	Bad src variables
		$this->_fail		= $this->_cdn_root . '_resources/img/fail.png';
		
		// --------------------------------------------------------------------------
		
		//	Determine dynamic values
		$this->_width		= $this->uri->segment( 3, 100 );
		$this->_height		= $this->uri->segment( 4, 100 );
		$this->_bucket		= $this->uri->segment( 5 );
		$this->_object		= $this->uri->segment( 6 );
		$this->_extension	= ( ! empty( $this->_object ) ) ? strtolower( substr( $this->_object, strrpos( $this->_object, '.' ) ) ) : FALSE;
		
		//	Set a unique filename (but one which is constant if requested twice, i.e
		//	no random values)
		
		$this->_cache_file	= $this->_bucket . '-' .											//	Bucket name
							  substr( $this->_object, 0, strrpos( $this->_object, '.' ) ) .		//	Filename
							  '-' . $this->_width . 'x' . $this->_height .						//	Dimensions
							  $this->_extension;												//	Extension
		
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
		//	We must have a bucket, object and extension in order to work with this
		if ( ! $this->_bucket || ! $this->_object || ! $this->_extension )
			return $this->_bad_src();
		
		// --------------------------------------------------------------------------
		
		//	Set the prefix for the cache file
		$this->_cache_file = 'thumb-' . $this->_cache_file;
		
		// --------------------------------------------------------------------------
		
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
			
			if ( file_exists( CDN_PATH . $this->_bucket . '/' . $this->_object ) ) :
			
				//	Object exists, time for manipulation fun times :>
				
				//	Set some PHPThumbFactory options
				$_options['resizeUp']		= TRUE;
				$_options['jpegQuality']	= 80;
				
				// --------------------------------------------------------------------------
				
				//	Perform the resize (3rd param tells PHPThumbFactory that we're using a
				//	data stream rather than a file).
				
				$thumb = PhpThumbFactory::create( CDN_PATH . $this->_bucket . '/' . $this->_object, $_options );
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
				$thumb->save( CACHE_DIR . $this->_cache_file, strtoupper( substr( $this->_extension, 1 ) ) );
			
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
		header( 'HTTP/1.1 404 Not Found' );
		header( 'Cache-Control: no-cache, must-revalidate' );
		header( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' );
		header( 'Content-type: application/json' );
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