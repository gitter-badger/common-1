<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Name:		Auth [Register]
 *
 * Description:	Handles user registration
 * 
 **/

/**
 * OVERLOADING NAILS'S AUTH MODULE
 * 
 * Note the name of this class; done like this to allow apps to extend this class.
 * Read full explanation at the bottom of this file.
 * 
 **/

require_once '_auth.php';

class NAILS_Register extends NAILS_Auth_Controller
{
	/**
	 * Constructor
	 *
	 * @access	public
	 * @param	none
	 * @return	void
	 * @author	Pablo
	 **/
	public function __construct()
	{
		parent::__construct();
		
		// --------------------------------------------------------------------------
		
		//	Load libraries
		$this->load->library( 'form_validation' );
		
		// --------------------------------------------------------------------------
		
		//	Specify a default title for this page
		$this->data['page']->title = 'Register';
	}
	
	
	// --------------------------------------------------------------------------
	
	
	/**
	 * Display registration form, validate data and create user
	 *
	 * @access	public
	 * @param	none
	 * @return	void
	 * @author	Pablo
	 **/
	public function index()
	{
		//	If you're logged in you shouldn't be accessing this method
		if ( $this->user->is_logged_in() ) :
		
			$this->session->set_flashdata( 'error', lang( 'no_access_already_logged_in', active_user( 'email' ) ) );
			redirect( '/' );
			
		endif;
		
		// --------------------------------------------------------------------------
		
		//	If there's POST data attempt to log user in
		if ( $this->input->post() ) :
		
			//	Validate input
			$this->form_validation->set_rules( 'first_name',	'First Name',			'required|xss_clean' );
			$this->form_validation->set_rules( 'last_name',		'Surname',				'required|xss_clean' );
			$this->form_validation->set_rules( 'email',			'Email',				'required|xss_clean|valid_email|is_unique[user.email]' );
			$this->form_validation->set_rules( 'password',		'Password',				'required|xss_clean' );
			$this->form_validation->set_rules( 'terms',			'Terms & Conditions',	'required|xss_clean' );
			
			// --------------------------------------------------------------------------
			
			//	Change default messages
			$this->form_validation->set_message( 'required',				lang( 'required_field' ) );
			$this->form_validation->set_message( 'valid_email',				lang( 'valid_email' ) );
			$this->form_validation->set_message( 'alpha_dash_space_accent',	lang( 'alpha_dash_space_accent' ) );
			$this->form_validation->set_message( 'matches',					lang( 'matches' ) );
			$this->form_validation->set_message( 'is_unique',				lang( 'is_unique', site_url( 'auth/forgotten_password' ) ) );
			
			// --------------------------------------------------------------------------
			
			//	Run validation
			if ( $this->form_validation->run() == TRUE ) :
			
				//	Attempt the registration
				$email		= $this->input->post( 'email' );
				$password	= $this->input->post( 'password' );
				$remember	= $this->input->post( 'remember' );
				
				// --------------------------------------------------------------------------
				
				//	Meta data
				$data['first_name']	= $this->input->post( 'first_name' );
				$data['last_name']	= $this->input->post( 'last_name' );
				$data['marketing']	= $this->input->post( 'marketing' );
				
				// --------------------------------------------------------------------------
				
				//	Handle referrals
				if ( $this->session->userdata( 'referred_by' ) ) :
				
					$data['referred_by'] = $this->session->userdata( 'referred_by' );
				
				endif;
				
				// --------------------------------------------------------------------------
				
				//	Create new user, group 2 (member)
				$_group_id = 2;
				$_uid = $this->user->create( $email, $password, $_group_id, $data );
				
				if ( $_uid ) :
				
					//	Some nice data...
					$this->data['email']	= $email;
					$this->data['user_id']	= $_uid['id'];
					$this->data['hash']		= $_uid['activation'];
					
					// --------------------------------------------------------------------------
					
					//	Registration was successfull, send the activation email...
					$this->load->library( 'emailer' );
					
					$_email							= new stdClass();
					$_email->type					= 'verify_email_' . $_group_id;
					$_email->to_id					= $_uid['id'];
					$_email->data					= array();
					$_email->data['user']			= $this->user->get_user( $_uid['id'] );
					$_email->data['group']			= $this->user->get_group( $_group_id )->display_name;
					
					if ( ! $this->emailer->send( $_email, TRUE ) ) :
					
						//	Failed to send using the group email, try using the generic email
						$_email->type = 'verify_email';
						
						if ( ! $this->emailer->send( $_email, TRUE ) ) :
						
							//	Email failed to send, for now, do nothing.
						
						endif;
					
					endif;
					
					// --------------------------------------------------------------------------
					
					//	Log the user in
					$this->user->set_login_data( $_uid['id'], $email, $_group_id );
					
					// --------------------------------------------------------------------------
					
					//	Create an event for this event
					create_event( 'did_register', $_uid['id'], 0, NULL, array( 'method' => 'native' ) );
					
					// --------------------------------------------------------------------------
					
					//	Redirect to the group homepage
					$_user = $this->user->get_user( $_uid['id'] );
					$this->session->set_flashdata( 'success', '<strong>Welcome, ' . $_user->first_name . '!</strong>' );
					redirect( $_user->group_homepage );
					return;
				
				endif;
			
			else:
			
				$this->data['error'] = lang( 'register_error' );
			
			endif;
		
		endif;
		
		// --------------------------------------------------------------------------
		
		//	Load the views; using the auth_model view loader as we need to check if
		//	an overload file exists which should be used instead
		
		$this->load->view( 'structure/header',		$this->data );
		$this->load->view( 'auth/register/form',	$this->data );
		$this->load->view( 'structure/footer',		$this->data );
	}
	
	
	// --------------------------------------------------------------------------
	
	
	/**
	 * Allows a user to resend their activation email
	 *
	 * @access	public
	 * @param	none
	 * @return	void
	 * @author	Pablo
	 **/
	public function resend()
	{
		$_id	= $this->uri->segment( 4 );
		$_hash	= $this->uri->segment( 5 );
		
		// --------------------------------------------------------------------------
		
		//	We got details?
		if ( $_id === FALSE || $_hash === FALSE ):
		
			$this->session->set_flashdata( 'error', 'Invalid credentials supplied. Unable to resend activation email. <small class="right">Error #1</small>' );
			redirect( '/' );
			return;
		
		endif;
		
		// --------------------------------------------------------------------------
		
		//	Valid user?
		$_u = $this->user->get_user( $_id );
		
		if ( $_u === FALSE ) :
		
			$this->session->set_flashdata( 'error', 'Invalid credentials supplied. Unable to resend activation email.  <small class="right">Error #2</small>' );
			redirect( '/' );
			return;
		
		endif;
		
		// --------------------------------------------------------------------------
		
		//	Account active?
		if ( $_u->active ) :
		
			$this->session->set_flashdata( 'error', 'Account already active, please try logging in. <small class="right">Error #3</small>' );
			redirect( 'auth/login' );
			return;
		
		endif;
		
		// --------------------------------------------------------------------------
		
		//	Hash match?
		if ( md5( $_u->activation_code ) != $_hash ) :
		
			$this->session->set_flashdata( 'error', 'Invalid credentials supplied. Unable to resend activation email. <small class="right">Error #4</small>' );
			redirect( '/' );
			return;
		
		endif;
		
		// --------------------------------------------------------------------------
		
		//	All good, resend now
		
		//	Load up emailer
		$this->load->library( 'emailer' );
		
		// --------------------------------------------------------------------------
		
		//	Send user their welcome email
		
		//	Initialise vars
		$_data = new StdClass();
		$_data->data = array();
		
		$_data->to						= $_u->email;
		$_data->type					= 'register_activate_resend';
		$_data->data['first_name']		= $_u->first_name;
		$_data->data['user_id']			= $_u->id;
		$_data->data['activation_code']	= $_u->activation_code;
		
		// --------------------------------------------------------------------------
		
		//	Send it off now
		$this->emailer->send_now( $_data );
		
		// --------------------------------------------------------------------------
		
		//	Set some data for the view
		$this->data['email'] = $_u->email;
		
		// --------------------------------------------------------------------------
		
		//	Load the views; using the auth_model view loader as we need to check if
		//	an overload file exists which should be used instead
		
		$this->load->view( 'structure/header',		$this->data );
		$this->load->view( 'auth/register/resend',	$this->data );
		$this->load->view( 'structure/footer',		$this->data );

	}
}


// --------------------------------------------------------------------------


/**
 * OVERLOADING NAILS'S AUTH MODULE
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

	class Register extends NAILS_Register
	{
	}

endif;

/* End of file register.php */
/* Location: ./application/modules/auth/controllers/register.php */