<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* Name:			Twitter
*
* Description:	Gateway to the Twitter API
* 
*/

class Twitter_Connect {
	
	private $ci;
	private $settings;
	private $twitter;
	
	
	// --------------------------------------------------------------------------
	
	
	/**
	 * Constructor
	 *
	 * @access	public
	 * @return	void
	 * @author	Pablo
	 **/
	public function __construct()
	{
		$this->ci =& get_instance();
		
		// --------------------------------------------------------------------------
		
		//	Fetch our config variables
		$this->ci->config->load( 'twitter' );
		$this->settings = $this->ci->config->item( 'twitter' );
		
		// --------------------------------------------------------------------------
		
		//	Fire up and initialize the SDK
		require NAILS_PATH . 'libraries/_resources/twitter-codebird/codebird.php';
		Codebird::setConsumerKey( $this->settings['consumer_key'], $this->settings['consumer_secret'] );
		$this->twitter = new Codebird();
	}
	
	
	// --------------------------------------------------------------------------
	
	
	/**
	 * Determines whether the active user has already linked their Twitter profile
	 *
	 * @access	public
	 * @return	void
	 * @author	Pablo
	 **/
	public function user_is_linked()
	{
		return (bool) active_user( 'tw_id' );
	}
	
	
	// --------------------------------------------------------------------------
	
	
	/**
	 * Unlinks a local account from Twitter
	 *
	 * @access	public
	 * @return	void
	 * @author	Pablo
	 **/
	public function unlink_user( $user_id )
	{
		dumpanddie( 'TODO Unlink a user' );
	}
	
	
	// --------------------------------------------------------------------------
	
	
	/**
	 * Fetches the login URL
	 *
	 * @access	public
	 * @param	string $success Where to redirect the user to on successful login
	 * @param	string $fail Where to redirect the user to on failed login
	 * @return	void
	 **/
	public function get_login_url( $success, $fail )
	{	
		$_params					= array();
		$_params['oauth_callback']	= $this->_get_redirect_url( $success, $fail );
		$_request_token = $this->oauth_requestToken( $_params );
		
		if ( ! $_request_token ) :
		
			return FALSE;
		
		endif;
		
		// --------------------------------------------------------------------------
		
		$this->setToken( $_request_token->oauth_token, $_request_token->oauth_token_secret );
		$this->ci->session->set_userdata( 'tw_request_token', $_request_token );
		
		return $this->oauth_authenticate();
	}
	
	
	// --------------------------------------------------------------------------
	
	
	/**
	 * Gets the URL where the user will be redirected to after connecting/logging in
	 *
	 * @access	public
	 * @param	string $success Where to redirect the user to on successful login
	 * @param	string $fail Where to redirect the user to on failed login
	 * @return	void
	 **/
	private function _get_redirect_url( $success, $fail )
	{
		//	Set a little userdata for when we come back
		$_data									= array();
		$_data['nailsTWConnectReturnTo']		= $success ? $success : active_user( 'group_homepage' );
		$_data['nailsTWConnectReturnToFail']	= $fail ? $fail : $success;
		
		//	Filter out empty items
		$_data = array_filter( $_data );
		$_query_string = $_data ? '?' . http_build_query( $_data ) : NULL;
		
		return site_url( 'auth/tw/connect/verify' . $_query_string  );
	}
	
	
	// --------------------------------------------------------------------------
	
	
	/**
	 * Sets a user's access token
	 *
	 * @access	public
	 * @param	string $token The token to use
	 * @param	string $secret The secret to use
	 * @return	void
	 **/
	public function set_access_token( $token, $secret )
	{
		$this->twitter->setToken( $token, $secret );
	}
	
	
	// --------------------------------------------------------------------------
	
	
	/**
	 * Fetches a user's access token
	 *
	 * @access	public
	 * @return	void
	 **/
	public function get_access_token( $code )
	{
		return $this->oauth_accessToken( $code );
	}
	
	
	// --------------------------------------------------------------------------
	
	
	/**
	 * Map unknown method calls to the Twitter library
	 *
	 * @access	public
	 * @return	mixed
	 * @author	Pablo
	 **/
	public function __call( $method, $arguments )
	{
		return call_user_func_array( array( $this->twitter, $method ), $arguments );
	}
}

/* End of file Twitter_connect.php */
/* Location: ./application/libraries/Twitter_connect.php */