<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Name:		API_Controller
 *
 * Created:		18/11/2012
 * Modified:	18/11/2012
 *
 * Description:	This controller executes various bits of common admin API functionality
 * 
 **/


class API_Controller extends NAILS_Controller
{
	/**
	*	Take the input and spit it out as JSON
	*	
	*	@access	public
	*	@param	none
	*	@return void
	*	@author Pablo
	*	
	**/
	protected function _out( $out = array() )
	{
		//	Set JSON headers
		$this->output->set_header( 'Cache-Control: no-store, no-cache, must-revalidate' );
		$this->output->set_header( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' );
		$this->output->set_header( 'Content-type: application/json' ); 
		$this->output->set_header( 'Pragma: no-cache' );
		
		// --------------------------------------------------------------------------
		
		//	Send the correct status header, default to 200 OK
		if ( isset( $out['status'] ) ) :
		
			switch ( $out['status'] ) :
			
				case 400 :	$this->output->set_header( 'HTTP/1.0 400 Bad Request' );			break;
				case 401 :	$this->output->set_header( 'HTTP/1.0 401 Unauthorized' );			break;
				case 404 :	$this->output->set_header( 'HTTP/1.0 404 Not Found' );				break;
				case 500 :	$this->output->set_header( 'HTTP/1.0 500 Internal Server Error' );	break;
				default  :	$this->output->set_header( 'HTTP/1.0 200 OK' );						break;
			
			endswitch;
			
		else:
		
			$out['status'] = 200;
			$this->output->set_header( 'HTTP/1.0 200 OK' );
		
		endif;
		
		// --------------------------------------------------------------------------
		
		//	Output content
		$this->output->set_output( json_encode( $out ) );
	}
	
	
	// --------------------------------------------------------------------------
	
	
	function _remap( $method )
	{
		if ( method_exists( $this, $method ) ) :
		
			$this->{$method}();
		
		else :
		
			$this->_out( array(
				'status'	=> 400,
				'error'		=> '"' . $method . '" is not a valid method name'
			) );
		
		endif;
	}
}

/* End of file _api.php */
/* Location: ./application/modules/api/controllers/_api.php */