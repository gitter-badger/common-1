<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class CDN_Controller extends NAILS_Controller
{
	protected $_cdn_root;
	
	// --------------------------------------------------------------------------
	
	public function __construct()
	{
		parent::__construct();
		
		// --------------------------------------------------------------------------
		
		$this->_cdn_root = NAILS_PATH . 'modules/cdn/';
		
		// --------------------------------------------------------------------------
		
		$this->lang->load( 'cdn', 'english' );
	}
	
	
	// --------------------------------------------------------------------------
	
	
	protected function _serve_from_cache( $file )
	{
		//	Cache object exists, set the appropriate headers and return the
		//	contents of the file.
		
		$_stats = stat( CACHE_DIR . $file );
		
		header( 'content-type: image/png' );
		header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s', $_stats[9] ) . 'GMT' );
		header( 'ETag: "' . md5( $file ) . '"' );
		header( 'X-CDN-CACHE: HIT' );
		
		// --------------------------------------------------------------------------
		
		//	Send the contents of the file to the browser
		echo file_get_contents( CACHE_DIR . $file );
	}
	
	
	// --------------------------------------------------------------------------
	
	
	protected function _serve_not_modified( $file )
	{
		$_headers = apache_request_headers();	
		
		if ( isset( $_headers['If-None-Match'] ) && ( $_headers['If-None-Match'] == '"' . md5( $file ) . '"' ) ) :
		
			header( 'Not Modified', TRUE, 304 );
			return TRUE;
		
		endif;
		
		// --------------------------------------------------------------------------
		
		return FALSE;
	}
}