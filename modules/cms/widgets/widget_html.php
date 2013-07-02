<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Nails_CMS_Widget_html extends Nails_CMS_Widget
{
	static function details()
	{
		$_d			= parent::details();
		$_d->name	= 'HTML';
		$_d->slug	= 'Widget_html';
		$_d->iam	= 'Nails_CMS_Widget_html';
		$_d->info	= 'Plain, completely unformatted text.';
		
		return $_d;
	}
	
	// --------------------------------------------------------------------------
	
	private $_key;
	private $_body;
	
	// --------------------------------------------------------------------------
	
	public function __construct()
	{
		$this->_key		= 'html';
		$this->_body	= '';
	}
	
	
	// --------------------------------------------------------------------------
	
	
	public function setup( $data )
	{
		if ( isset( $data['body'] ) ) :
		
			$this->_body = $data['body'];
		
		endif;
		
		// --------------------------------------------------------------------------
		
		if ( isset( $data['key'] ) && ! is_null( $data['key'] ) ) :
		
			$this->_key = $data['key'];
		
		endif;
	}
	
	// --------------------------------------------------------------------------
	
	public function render()
	{
		return $this->_body;
	}
	
	// --------------------------------------------------------------------------
	
	public function get_editor_html()
	{
		$_details = self::details();
		
		//	Include the slug as a hidden field, required for form rebuilding
		$_out = form_hidden( $this->_key . '[slug]', $_details->slug );
		
		// --------------------------------------------------------------------------
		
		//	Return editor HTML
		$_out .= form_textarea( $this->_key . '[body]', set_value( $this->_key . '[body]', $this->_body ) );
		
		// --------------------------------------------------------------------------
		
		return $_out;
	}
}