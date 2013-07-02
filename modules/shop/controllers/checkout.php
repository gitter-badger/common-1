<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Name:		Shop - Checkout
 *
 * Description:	This controller handles the user's checkout experience
 * 
 **/

/**
 * OVERLOADING NAILS' AUTH MODULE
 * 
 * Note the name of this class; done like this to allow apps to extend this class.
 * Read full explanation at the bottom of this file.
 * 
 **/

//	Include _shop.php; executes common functionality
require_once '_shop.php';

class NAILS_Checkout extends NAILS_Shop_Controller
{
	public function __construct()
	{
		parent::__construct();
	}
	
	
	// --------------------------------------------------------------------------
	
	
	/**
	 * Handle the checkout process
	 * 
	 * @access	public
	 * @return	void
	 * @author	Pablo
	 * 
	 **/
	public function index()
	{
		if ( ! $this->_can_checkout() ) :
		
			$this->session->set_flashdata( 'error', '<strong>Sorry,</strong> you can\'t checkout right now: ' . $this->data['error'] );
			redirect( shop_setting( 'shop_url' ) . 'basket' );
			return;
		
		endif;
		
		// --------------------------------------------------------------------------
		
		if ( $this->user->is_logged_in() || $this->input->get( 'guest' ) ) :
			
			//	Continue, user is logged in or is checking out as a guest
			if ( $this->input->get( 'guest' ) ) :
			
				$this->data['guest'] = TRUE;
			
			else :
			
				$this->data['guest'] = FALSE;
			
			endif;
			
			// --------------------------------------------------------------------------
			
			//	Check the order to see if we need to take shipping information
			$this->data['requires_shipping'] = FALSE;
			foreach ( $this->data['basket']->items AS $item ) :
			
				if ( $item->type->requires_shipping ) :
				
					$this->asset->load( 'jquery.chosen.min.js', TRUE );
					$this->data['requires_shipping'] = TRUE;
					break;
				
				endif;
			
			endforeach;
			
			// --------------------------------------------------------------------------
			
			//	If there's no shipping and only one payment gateway then skip this page
			//	entirely - simples! Unless they are a guest, in which case we need to take
			//	some personal details

			if ( ! $this->data['guest'] && ! $this->data['requires_shipping'] && ( count( $this->data['payment_gateways'] ) == 1 || $this->data['basket']->totals->grand == 0 ) ) :
			
				//	Save payment gateway info to the session
				if ( $this->data['basket']->totals->grand != 0 ) :

					$this->basket->add_payment_gateway( $this->data['payment_gateways'][0]->id );

				else :

					$this->basket->remove_payment_gateway();

				endif;
				
				//	... and redirect to confirm
				$_uri  = shop_setting( 'shop_url' ) . 'checkout/confirm';
				$_uri .= $this->data['guest'] ? '?guest=true' : '';
				
				redirect( $_uri );
				return;
			
			endif;
			
			// --------------------------------------------------------------------------
			
			//	If there's post data, then deal with that. If shipping is required then verify shipping info
			//	If not then punt onto shop/checkout/confirm
			
			if ( $this->input->post() ) :
			
				//	Validate
				$this->load->library( 'form_validation' );
				
				if ( $this->data['guest'] ) :
				
					$this->form_validation->set_rules( 'first_name',	'First Name',	'xss_clean|required' );
					$this->form_validation->set_rules( 'last_name',		'Surname',		'xss_clean|required' );
					$this->form_validation->set_rules( 'email',			'Email',		'xss_clean|required|valid_email' );
				
				endif;
				
				// --------------------------------------------------------------------------
				
				if ( $this->data['requires_shipping'] ) :
				
					$this->form_validation->set_rules( 'addressee',	'Addressee',	'xss_clean|required' );
					$this->form_validation->set_rules( 'line_1',	'Line_1',		'xss_clean|required' );
					$this->form_validation->set_rules( 'line_2',	'Line_2',		'xss_clean|required' );
					$this->form_validation->set_rules( 'town',		'Town',			'xss_clean|required' );
					$this->form_validation->set_rules( 'postcode',	'Postcode',		'xss_clean|required' );
					$this->form_validation->set_rules( 'country',	'Country',		'xss_clean|required' );
					
					//	If country is USA then us_state is required
					if ( $this->input->post( 'country' ) == 'ID OF USA' ) :
					
						$this->form_validation->set_rules( 'us_state',		'State',		'xss_clean|required' );
						
					else :
					
						$this->form_validation->set_rules( 'us_state',		'State',		'xss_clean' );
					
					endif;
					
					//	If country is AUSTRALIA then aus_state is required
					if ( $this->input->post( 'country' ) == 'ID OF AUSTRALIA' ) :
					
						$this->form_validation->set_rules( 'aus_state',		'State',		'xss_clean|required' );
						
					else :
					
						$this->form_validation->set_rules( 'aus_state',		'State',		'xss_clean' );
					
					endif;
				
				endif;
				
				// --------------------------------------------------------------------------
				
				//	Payment gateway
				if ( $this->data['basket']->totals->grand > 0 ) :

					$this->form_validation->set_rules( 'payment_gateway', 'Payment Gateway', 'xss_clean|required|is_natural' );

				endif;
				
				// --------------------------------------------------------------------------
				
				//	Set messages
				$this->form_validation->set_message( 'required',	lang( 'fv_required' ) );
				$this->form_validation->set_message( 'is_natural',	lang( 'fv_required' ) );
				$this->form_validation->set_message( 'valid_email',	lang( 'fv_valid_email' ) );
				
				if ( $this->form_validation->run() ) :
				
					//	Save personal info to session
					if ( $this->data['guest'] ) :
					
						$_details				= new stdClass();
						$_details->first_name	= $this->input->post( 'first_name' );
						$_details->last_name	= $this->input->post( 'last_name' );
						$_details->email		= $this->input->post( 'email' );
						
						$this->basket->add_personal_details( $_details );
					
					else :

						//	In case it's already there for some reason
						$this->basket->remove_personal_details();

					endif;
					
					// --------------------------------------------------------------------------
					
					//	Save shipping info to the session
					if ( $this->data['requires_shipping'] ) :
					
						$_details				= new stdClass();
						$_details->addressee	= $this->input->post( 'addressee' );
						$_details->line_1		= $this->input->post( 'line_1' );
						$_details->line_2		= $this->input->post( 'line_2' );
						$_details->town			= $this->input->post( 'town' );
						$_details->postcode		= $this->input->post( 'postcode' );
						$_details->country		= $this->input->post( 'country' );
						
						if ( $this->input->post( 'country' ) == 'ID OF USA' ) :
						
							$_details->state	= $this->input->post( 'us_state' );
							
						elseif ( $this->input->post( 'country' ) == 'ID OF AUSTRALIA' ) :
						
							$_details->state	= $this->input->post( 'aus_state' );
						
						else :
						
							$_details->state	= '';
						
						endif;
						
						$this->basket->add_shipping_details( $_details );

					else :

						//	In case it's already there for some reason
						$this->basket->remove_shipping_details();
					
					endif;
					
					// --------------------------------------------------------------------------
					
					//	Redirect to the appropriate payment gateway. If there's only one, then
					//	bump straight along to that one
					
					if ( $this->data['basket']->totals->grand > 0 && count( $this->data['payment_gateways'] ) == 1 ) :
					
						//	Save payment gateway info to the session
						$this->basket->add_payment_gateway( $this->data['payment_gateways'][0]->id );
						
						//	... and confirm
						$_uri  = shop_setting( 'shop_url' ) . 'checkout/confirm';
						$_uri .= $this->data['guest'] ? '?guest=true' : '';
						
						redirect( $_uri );
					
					elseif ( $this->data['basket']->totals->grand > 0 && count( $this->data['payment_gateways'] ) >= 1 ) :
					
						foreach ( $this->data['payment_gateways'] AS $pg ) :
						
							if ( $pg->id == $this->input->post( 'payment_gateway' ) ) :
							
								//	Save payment gateway info to the session
								$this->basket->add_payment_gateway( $pg->id );
								
								//	... and confirm
								$_uri  = shop_setting( 'shop_url' ) . 'checkout/confirm';
								$_uri .= $this->data['guest'] ? '?guest=true' : '';
								
								redirect( $_uri );
								break;
							
							endif;
						
						endforeach;

					elseif ( $this->data['basket']->totals->grand == 0 ) :

						//	Incase it's already there for some reason
						$this->basket->remove_payment_gateway();

						// --------------------------------------------------------------------------

						$_uri  = shop_setting( 'shop_url' ) . 'checkout/confirm';
						$_uri .= $this->data['guest'] ? '?guest=true' : '';
						
						redirect( $_uri );
						
					endif;
					
					// --------------------------------------------------------------------------
					here();
					//	Something went wrong.
					$this->data['error'] = '<strong>Sorry,</strong> we couldn\'t verify your payment option. Please try again.';
				
				else :
				
					$this->data['error'] = lang( 'fv_there_were_errors' );
				
				endif;
			
			endif;
			
			// --------------------------------------------------------------------------
			
			//	Set appropriate title
			$_titles = array();
			
			if ( $this->data['guest'] ) :
			
				$_titles[] = 'Personal Details';
			
			endif;
			
			if ( $this->data['requires_shipping'] ) :
			
				$_titles[] = 'Shipping Details';
			
			endif;
			
			if ( count( $this->data['payment_gateways'] ) > 1 ) :
			
				$_titles[] = 'Payment Options';
			
			endif;
			
			$this->data['page']->title = 'Checkout &rsaquo; ' . str_lreplace( ', ', ' &amp; ', implode( ', ', $_titles ) );
			
			// --------------------------------------------------------------------------
			
			//	Load veiws
			$this->load->view( 'structure/header',			$this->data );
			$this->load->view( 'shop/checkout/checkout',	$this->data );
			$this->load->view( 'structure/footer',			$this->data );
		
		else :
		
			$this->data['page']->title = 'Checkout &rsaquo; Please Sign In';
			
			// --------------------------------------------------------------------------
			
			$this->lang->load( 'auth/auth', RENDER_LANG_SLUG );
			
			// --------------------------------------------------------------------------
			
			$this->load->view( 'structure/header',		$this->data );
			$this->load->view( 'shop/checkout/signin',	$this->data );
			$this->load->view( 'structure/footer',		$this->data );
		
		endif;
	}
	
	
	// --------------------------------------------------------------------------
	
	
	/**
	 * Handle the checkout process
	 * 
	 * @access	public
	 * @return	void
	 * @author	Pablo
	 * 
	 **/
	public function confirm()
	{
		if ( ! $this->_can_checkout() ) :
		
			$this->session->set_flashdata( 'error', '<strong>Sorry,</strong> you can\'t checkout right now: ' . $this->data['error'] );
			redirect( shop_setting( 'shop_url' ) . 'basket' );
			return;
		
		endif;
		
		// --------------------------------------------------------------------------
		
		if ( $this->user->is_logged_in() || $this->input->get( 'guest' ) ) :
			
			//	Continue, user is logged in or is checking out as a guest
			if ( $this->input->get( 'guest' ) ) :
			
				$this->data['guest'] = TRUE;
			
			else :
			
				$this->data['guest'] = FALSE;
			
			endif;
			
			// --------------------------------------------------------------------------
			
			//	If there's no shipping required and there's only one payment gateway then
			//	just create the order and punt the user to the payment gateway's processing
			//	page.
			
			if ( ! $this->data['basket']->requires_shipping && count( $this->data['payment_gateways'] ) == 1 ) :
			
				$this->basket->add_payment_gateway( $this->data['payment_gateways'][0]->id );
				
				$_uri  = shop_setting( 'shop_url' ) . 'checkout/payment';
				$_uri .= $this->data['guest'] ? '?guest=true' : '';
				
				redirect( $_uri );
				return;
			
			endif;
			
			// --------------------------------------------------------------------------
			
			$this->data['page']->title = 'Checkout &rsaquo; Confirm Your Order';
			
			// --------------------------------------------------------------------------
			
			$this->load->view( 'structure/header',		$this->data );
			$this->load->view( 'shop/checkout/confirm',	$this->data );
			$this->load->view( 'structure/footer',		$this->data );
			
		else :
		
			redirect( shop_setting( 'shop_url' ) . 'checkout' );
		
		endif;
	}
	
	
	// --------------------------------------------------------------------------
	
	
	public function payment()
	{
		if ( ! $this->_can_checkout() ) :
		
			$this->session->set_flashdata( 'error', '<strong>Sorry,</strong> you can\'t checkout right now: ' . $this->data['error'] );
			redirect( shop_setting( 'shop_url' ) . 'basket' );
			return;
		
		endif;
		
		// --------------------------------------------------------------------------
		
		if ( $this->user->is_logged_in() || $this->input->get( 'guest' ) ) :
			
			//	Continue, user is logged in or is checking out as a guest
			if ( $this->input->get( 'guest' ) ) :
			
				$this->data['guest'] = TRUE;
			
			else :
			
				$this->data['guest'] = FALSE;
			
			endif;

			// --------------------------------------------------------------------------

			//	Is the order a zero-value order? If so, just mark it as paid and send
			//	to processing immediately
			
			if ( $this->data['basket']->totals->grand == 0 ) :

				//	Create order, then set as paid and redirect to processing page
				$_order = $this->order->create( $this->data['basket'], TRUE );
				
				if ( ! $_order ) :
				
					$this->session->set_flashdata( 'error', 'There was a problem checking out: ' . $this->data['error'] );
					redirect( shop_setting( 'shop_url' ) . 'basket' );
					return;
				
				endif;
				
				//	Set as paid
				$this->order->paid( $_order->id );

				//	Process the order, send receipt and send order notification
				$this->order->process( $_order );
				$this->order->send_receipt( $_order );
				$this->order->send_order_notification( $_order );

				if ( $_order->voucher ) :

					$this->voucher->redeem( $_order->voucher->id, $_order );

				endif;

				// --------------------------------------------------------------------------

				//	Destory the basket
				$this->basket->destroy();

				// --------------------------------------------------------------------------
				
				//	Redirect to processing page
				redirect( shop_setting( 'shop_url' ) . 'checkout/processing?ref=' . $_order->ref );

			endif;
			
			// --------------------------------------------------------------------------
			
			switch ( $this->data['basket']->payment_gateway ) :
			
				//	Known payment gateways
				case 1 :	$this->_payment_paypal();	break;
				case 2 :	$this->_payment_shedpay();	break;
				case 3 :	$this->_payment_cardsave();	break;
				case 4 :	$this->_payment_sagepay();	break;
				case 5 :	$this->_payment_worldpay();	break;
				case 6 :	$this->_payment_eway();		break;
				
				// --------------------------------------------------------------------------
				
				//	Unknown
				default :
				
					$this->session->set_flashdata( 'error', '<strong>Sorry,</strong> there was a problem verifying your chosen payment option. Please try again.' );
					redirect( shop_setting( 'shop_url' ) . 'basket' );
				
				break;
			
			endswitch;
			
		else :
		
			redirect( shop_setting( 'shop_url' ) . 'checkout' );
		
		endif;
	}
	
	
	// --------------------------------------------------------------------------
	
	
	protected function _payment_paypal()
	{
		//	Create the order
		$this->data['order'] = $this->order->create( $this->data['basket'], TRUE );
		
		if ( ! $this->data['order'] ) :
		
			$this->session->set_flashdata( 'error', 'There was a problem checking out: ' . $this->data['error'] );
			redirect( shop_setting( 'shop_url' ) . 'basket' );
			return;
		
		endif;
		
		// --------------------------------------------------------------------------
		
		//	Fetch payment gateway details
		foreach( $this->data['payment_gateways'] AS $pg ) :
		
			if ( $this->data['basket']->payment_gateway == $pg->id ) :
			
				$_payment_gateway =& $pg;
				break;
			
			endif;
		
		endforeach;
		
		// --------------------------------------------------------------------------
		
		//	Prepapre variables for the template
		$this->data['paypal'] = new stdClass();
		
		switch ( ENVIRONMENT ) :
		
			case 'production' :
			
				$this->data['paypal']->url			= 'https://www.paypal.com/cgi-bin/webscr';
				$this->data['paypal']->business		= $_payment_gateway->account_id;
			
			break;
			
			default :
			
				$this->data['paypal']->url			= 'https://www.sandbox.paypal.com/cgi-bin/webscr';
				$this->data['paypal']->business		= $_payment_gateway->sandbox_account_id;
			
			break;
		
		endswitch;
		
		$this->data['paypal']->notify		= site_url( shop_setting( 'shop_url' ) . 'checkout/notify/paypal' );
		$this->data['paypal']->cancel		= site_url( shop_setting( 'shop_url' ) . 'checkout/cancel' );
		$this->data['paypal']->processing	= site_url( shop_setting( 'shop_url' ) . 'checkout/processing' );
		
		// --------------------------------------------------------------------------
		
		//	Load the views
		$this->load->view( 'shop/checkout/payment/paypal/index',	$this->data );
	}
	
	
	// --------------------------------------------------------------------------
	
	
	protected function _payment_shedpay()
	{
		dumpanddie( 'TODO: Shedpay interface' );
	}
	
	
	// --------------------------------------------------------------------------
	
	
	protected function _payment_cardsave()
	{
		dumpanddie( 'TODO: CardSave interface' );
	}
	
	
	// --------------------------------------------------------------------------
	
	
	protected function _payment_sagepay()
	{
		dumpanddie( 'TODO: SagePay interface' );
	}
	
	
	// --------------------------------------------------------------------------
	
	
	protected function _payment_worldpay()
	{
		dumpanddie( 'TODO: WordlPay interface' );
	}
	
	
	// --------------------------------------------------------------------------
	
	
	protected function _payment_eway()
	{
		dumpanddie( 'TODO: eWay interface' );
	}
	
	
	// --------------------------------------------------------------------------
	
	
	protected function _can_checkout()
	{
		//	Check basket isn't empty
		$this->data['basket'] = $this->basket->get_basket();
		
		if ( ! $this->data['basket']->items ) :
		
			$this->data['error'] = 'Your basket is empty.';
			return FALSE;
		
		endif;
		
		// --------------------------------------------------------------------------
		
		//	Load the payment gateway model
		$this->load->model( 'shop_payment_gateway_model', 'payment_gateway' );
		
		//	Fetch the supported payment gateways
		$this->data['payment_gateways'] = $this->payment_gateway->get_all_supported();
		
		if ( ! $this->data['payment_gateways'] ) :
		
			//	Uh-oh, no supported payment gateways. Bad times but feedback to the user.			
			$this->data['error'] = 'There\'s an issue at the moment which is preventing ' . APP_NAME . ' form accepting online payment at the moment. Please try again later.';
			return FALSE;
		
		endif;
		
		// --------------------------------------------------------------------------
		
		return TRUE;
	}
	
	
	// --------------------------------------------------------------------------
	
	
	public function processing()
	{
		$this->data['order'] = $this->order->get_by_ref( $this->input->get( 'ref' ) );
		
		if ( ! $this->data['order'] ) :
		
			show_404();
		
		endif;
		
		// --------------------------------------------------------------------------
		
		//	Empty the basket
		$this->basket->destroy();
		
		// --------------------------------------------------------------------------
		
		switch( $this->data['order']->status ) :
		
			case 'UNPAID' :		$this->_processing_unpaid();		break;
			case 'PAID' :		$this->_processing_paid();			break;
			case 'PENDING' :	$this->_processing_pending();		break;
			case 'FAILED' :		$this->_processing_failed();		break;
			case 'ABANDONED' :	$this->_processing_abandoned();		break;
			case 'CANCELLED' :	$this->_processing_cancelled();		break;
			default :			$this->_processing_error();			break;
		
		endswitch;
	}
	
	
	// --------------------------------------------------------------------------
	
	
	protected function _processing_unpaid()
	{
		$this->load->view( 'shop/checkout/payment/processing/unpaid', $this->data );
	}


	// --------------------------------------------------------------------------


	protected function _processing_pending()
	{
		$this->load->view( 'structure/header',	$this->data );
		$this->load->view( 'shop/checkout/payment/processing/pending', $this->data );
		$this->load->view( 'structure/footer',	$this->data );
	}
	
	
	// --------------------------------------------------------------------------
	
	
	protected function _processing_paid()
	{
		$this->data['page']->title	= 'Thanks for your order!';
		$this->data['success']		= '<strong>Success!</strong> Your order has been processed.';
		
		// --------------------------------------------------------------------------
		
		$this->load->view( 'structure/header',	$this->data );
		$this->load->view( 'shop/checkout/payment/processing/paid', $this->data );
		$this->load->view( 'structure/footer',	$this->data );
	}
	
	
	// --------------------------------------------------------------------------
	
	
	protected function _processing_failed()
	{
		$this->_processing_error();
	}
	
	
	// --------------------------------------------------------------------------
	
	
	protected function _processing_abandoned()
	{
		$this->_processing_error();
	}
	
	
	// --------------------------------------------------------------------------
	
	
	protected function _processing_cancelled()
	{
		$this->_processing_error();
	}
	
	
	// --------------------------------------------------------------------------
	
	
	protected function _processing_error()
	{
		if ( ! $this->data['error'] ) :
		
			$this->data['error'] = '<strong>Sorry,</strong> there was a problem processing your order';
			
		endif;
		
		if ( ! isset( $this->data['page']->title ) || ! $this->data['page']->title ) :
		
			$this->data['page']->title = 'An error occurred';
			
		endif;
		
		// --------------------------------------------------------------------------
		
		$this->load->view( 'structure/header',	$this->data );
		$this->load->view( 'shop/checkout/payment/processing/error', $this->data );
		$this->load->view( 'structure/footer',	$this->data );
	}
	
	
	// --------------------------------------------------------------------------
	
	
	public function cancel()
	{
		$this->data['order'] = $this->order->get_by_ref( $this->input->get( 'ref' ) );
		
		if ( ! $this->data['order'] ) :
		
			show_404();
		
		endif;

		// --------------------------------------------------------------------------

		$this->order->cancel( $this->data['order']->id );

		$this->session->set_flashdata( 'message', '<strong>Checkout was cancelled.</strong><br />At your request, we cancelled checkout - you have not been charged.' );

		redirect( shop_setting( 'shop_url' ) . 'basket' );
	}
	
	
	// --------------------------------------------------------------------------
	
	
	public function notify()
	{
		//	Testing, testing, 1, 2, 3?
		$this->data['testing'] = $this->_notify_is_testing();
		
		//	Load the logger
		$this->load->library( 'logger' );
		
		//	Handle the notification in a way appropriate to the payment gateway
		switch( $this->uri->rsegment( 3 ) ) :
		
			case 'paypal';	$this->_notify_paypal();	break;
			
			// --------------------------------------------------------------------------
			
			default : /*	Silence is golden	*/	break;
		
		endswitch;
	}
	
	
	// --------------------------------------------------------------------------
	
	
	protected function _notify_paypal()
	{
		//	Configure logger
		$this->logger->log_dir( shop_setting( 'shop_url' ) . 'notify/paypal' );
		$this->logger->log_file( 'ipn-' . date( 'Y-m-d' ) . '.php' );
		
		$this->logger->line();
		$this->logger->line( '- - - - - - - - - - - - - - - - - - -' );
		$this->logger->line( 'Waking up IPN responder; handling with PayPal' );
		
		// --------------------------------------------------------------------------
		
		//	POST data?

		//	Want to test a previous IPN message?
		//	Paste the IPN message into the following and uncomment the following lines

		//	$_message = '';
		//	$_message = str_replace( '+', '%2B', $_message );
		//	parse_str( $_message, $_POST );
		
		if ( ! $this->data['testing'] && ! $this->input->post() ) :
		
			$this->logger->line( 'No POST data, going back to sleep...' );
			$this->logger->line( '- - - - - - - - - - - - - - - - - - -' );
			$this->logger->line();
			
			return;
		
		endif;
		
		// --------------------------------------------------------------------------
		
		//	Are we testing?
		if ( $this->data['testing'] ) :
		
			$_ipn = TRUE;
			$this->logger->line();
			$this->logger->line( '**TESTING**' );
			$this->logger->line( '**Simulating data sent from PayPal**' );
			$this->logger->line();
			
			//	Check order exists
			$_order = $this->order->get_by_ref( $this->input->get( 'ref' ) );
			
			if ( ! $_order ) :
			
				$this->logger->line( 'Invalid order reference, aborting.' );
				$this->logger->line( '- - - - - - - - - - - - - - - - - - -' );
				$this->logger->line();
				
				return;
			
			endif;
			
			// --------------------------------------------------------------------------
			
			$_paypal					= array();
			$_paypal['payment_type']	= 'instant';
			$_paypal['invoice']			= $_order->ref;
			$_paypal['custom']			=  $this->encrypt->encode( md5( $_order->ref . ':' . $_order->code ), APP_PRIVATE_KEY );
			$_paypal['txn_id']			= 'TEST:' . random_string( 'alpha', 6 );
			$_paypal['txn_type']		= 'cart';
			$_paypal['payment_status']	= 'Pending';
			$_paypal['pending_reason']	= 'PaymentReview';
			$_paypal['mc_fee']			= 0.00;
		
		else :
		
			$this->logger->line( 'Validating the IPN call' );
			$this->load->library( 'paypal' );
			
			$_ipn		= $this->paypal->validate_ipn();
			$_paypal	= $this->input->post();
			
			$_order = $this->order->get_by_ref( $this->input->post( 'invoice' ) );
			
			if ( ! $_order ) :
			
				$this->logger->line( 'Invalid order ID, aborting. Likely a transaction not initiated by the site.' );
				$this->logger->line( '- - - - - - - - - - - - - - - - - - -' );
				$this->logger->line();
				
				return;
			
			endif;
		
		endif;
		
		// --------------------------------------------------------------------------
		
		//	Did the IPN validate?
		if ( $_ipn ) :
		
			$this->logger->line( 'IPN Verified with PayPal' );
			$this->logger->line();
			
			// --------------------------------------------------------------------------
			
			//	Extra verification step, check the 'custom' variable decodes appropriately
			$this->logger->line( 'Verifying data' );
			$this->logger->line();
			
			$_verification = $this->encrypt->decode( $_paypal['custom'], APP_PRIVATE_KEY );
			
			if ( $_verification != md5( $_order->ref . ':' . $_order->code ) ) :
			
				$_data = array(
					'pp_txn_id'	=> $_paypal['txn_id']
				);
				$this->order->fail( $_order->id, $_data );
				
				$this->logger->line( 'Order failed secondary verification, aborting.' );
				$this->logger->line( '- - - - - - - - - - - - - - - - - - -' );
				$this->logger->line();
				
				// --------------------------------------------------------------------------
				
				//	Inform developers
				send_developer_mail( 'An IPN request failed', 'An IPN request was made which failed secondary verification, Order: ' . $_paypal['invoice'] );
				
				return;
			
			endif;
			
			// --------------------------------------------------------------------------
			
			//	Only bother to handle certain types
			//	TODO: handle refunds
			$this->logger->line( 'Checking txn_type is supported' );
			$this->logger->line();
			
			if ( $_paypal['txn_type'] != 'cart' ) :
			
				$this->logger->line( '"' . $_paypal['txn_type'] . '" is not a supported PayPal txn_type, gracefully aborting.' );
				$this->logger->line( '- - - - - - - - - - - - - - - - - - -' );
				$this->logger->line();
				
				return;
				
			endif;
			
			// --------------------------------------------------------------------------
			
			//	Check if order has already been processed
			$this->logger->line( 'Checking if order has already been processed' );
			$this->logger->line();
			
			if ( ENVIRONMENT == 'production' && $_order->status != 'UNPAID' ) :
			
				$this->logger->line( 'Order has already been processed, aborting.' );
				$this->logger->line( '- - - - - - - - - - - - - - - - - - -' );
				$this->logger->line();
				
				return;
			
			elseif ( ENVIRONMENT != 'production' && $_order->status != 'UNPAID' ) :

				$this->logger->line( 'Order has already been processed, but not on production so continuing anyway.' );
				$this->logger->line();

			endif;
			
			// --------------------------------------------------------------------------
			
			//	Check the status of the payment
			$this->logger->line( 'Checking the status of the payment' );
			$this->logger->line();


			switch( strtolower( $_paypal['payment_status'] ) ) :


				case 'completed' :

					//	Do nothing, this transaction is OK
					$this->logger->line( 'Payment status is "completed"; continuing...' );

				break;

				// --------------------------------------------------------------------------

				case 'reversed' :

					//	Transaction was cancelled, mark order as FAILED
					$this->logger->line( 'Payment was reversed, marking as failed and aborting' );
				
					$_data = array(
						'pp_txn_id'	=> $_paypal['txn_id']
					);
					$this->order->fail( $_order->id, $_data );

				break;

				// --------------------------------------------------------------------------

				case 'pending' :

					//	Check the pending_reason, if it's 'paymentreview' then gracefully stop
					//	processing; PayPal will send a further IPN once the payment is complete

					$this->logger->line( 'Payment status is "pending"; check the reason.' );

					if ( strtolower( $_paypal['pending_reason'] ) == 'paymentreview' ) :

						//	The transaction is pending review, gracefully stop proicessing, but don't cancel the order
						$this->logger->line( 'Payment is pending review by PayPal, gracefully aborting just now.' );
						$this->order->pending( $_order->id );
						return;

					else :

						$this->logger->line( 'Unsupported payment reason "' . $_paypal['pending_reason'] . '", aborting.' );

						// --------------------------------------------------------------------------

						$_data = array(
							'pp_txn_id'	=> $_paypal['txn_id']
						);
						$this->order->fail( $_order->id, $_data );

						// --------------------------------------------------------------------------

						//	Inform developers
						send_developer_mail( 'A PayPal payment failed', '<strong>' . $_order->user->first_name . ' ' . $_order->user->last_name . ' (' . $_order->user->email . ')</strong> has just attempted to pay for order ' . $_order->ref . '. The payment failed with status "' . $_paypal['payment_status'] . '" and reason "' . $_paypal['pending_reason'] . '".' );
						return;


					endif;

					// --------------------------------------------------------------------------

					return;

				break;

				// --------------------------------------------------------------------------

				default :

					//	Unknown/invalid payment status
					$this->logger->line( 'Invalid payment status' );
				
					$_data = array(
						'pp_txn_id'	=> $_paypal['txn_id']
					);
					$this->order->fail( $_order->id, $_data );
					
					// --------------------------------------------------------------------------
					
					//	Inform developers
					send_developer_mail( 'A PayPal payment failed', '<strong>' . $_order->user->first_name . ' ' . $_order->user->last_name . ' (' . $_order->user->email . ')</strong> has just attempted to pay for order ' . $_order->ref . '. The payment failed with status "' . $_paypal['payment_status'] . '" and reason "' . $_paypal['pending_reason'] . '".' );
					return;

				break;

			endswitch;
			
			// --------------------------------------------------------------------------
			
			//	All seems good, continue with order processing
			$this->logger->line( 'All seems well, continuing...' );
			$this->logger->line();
			
			$this->logger->line( 'Setting txn_id (' . $_paypal['txn_id'] . ') and fees_deducted (' . $_paypal['mc_fee'] . ').' );
			$this->logger->line();
			
			$_data = array(
				'pp_txn_id'		=> $_paypal['txn_id'],
				'fees_deducted'	=> $_paypal['mc_fee']
			);
			$this->order->paid( $_order->id, $_data );
			
			// --------------------------------------------------------------------------
			
			//	PROCESSSSSS...
			$this->order->process( $_order, $this->logger );
			$this->logger->line();
			
			// --------------------------------------------------------------------------
			
			//	Send a receipt to the customer
			$this->logger->line( 'Sending receipt to customer: ' . $_order->user->email );
			$this->order->send_receipt( $_order, $this->logger );
			$this->logger->line();
			
			// --------------------------------------------------------------------------
			
			//	Send a notification to the store owner(s)
			$this->logger->line( 'Sending notification to store owner(s): ' . shop_setting( 'notify_order' ) );
			$this->order->send_order_notification( $_order, $this->logger );

			// --------------------------------------------------------------------------

			if ( $_order->voucher ) :

				//	Redeem the voucher, if it's there
				$this->logger->line( 'Redeeming voucher: ' . $_order->voucher->code . ' - ' . $_order->voucher->label );
				$this->voucher->redeem( $_order->voucher->id, $_order );

			endif;
			
			// --------------------------------------------------------------------------
			
			$this->logger->line();
			
			// --------------------------------------------------------------------------
			
			$this->logger->line( 'All done here, going back to sleep...' );
			$this->logger->line( '- - - - - - - - - - - - - - - - - - -' );
			$this->logger->line();
			
			if ( $this->data['testing'] ) :
			
				echo anchor( shop_setting( 'shop_url' ) . 'checkout/processing?ref=' . $_order->ref, 'Continue to Processing Page' );
			
			endif;
		
		else :
		
			$this->logger->line( 'PayPal did not verify this IPN call, aborting.' );
			$this->logger->line( '- - - - - - - - - - - - - - - - - - -' );
			$this->logger->line();
		
		endif;
	}
	
	
	// --------------------------------------------------------------------------
	
	
	protected function _notify_is_testing()
	{
		if ( ENVIRONMENT == 'production' )
			return FALSE;
		
		// --------------------------------------------------------------------------
		
		if ( $this->input->get( 'testing' ) && $this->input->get( 'ref' ) ) :
		
			return TRUE;
		
		else :
		
			return FALSE;
		
		endif;
	}
}


// --------------------------------------------------------------------------


/**
 * OVERLOADING NAILS' SHOP MODULE
 * 
 * The following block of code makes it simple to extend one of the core shop
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
 
if ( ! defined( 'NAILS_ALLOW_EXTENSION_CHECKOUT' ) ) :

	class Checkout extends NAILS_Checkout
	{
	}

endif;

/* End of file checkout.php */
/* Location: ./application/modules/shop/controllers/checkout.php */