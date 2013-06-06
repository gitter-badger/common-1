<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Name:		CORE_NAILS_Timezone_Model
 *
 * Description:	This model contains all methods for handling timezones
 * 
 **/

/**
 * OVERLOADING NAILS'S MODELS
 * 
 * Note the name of this class; done like this to allow apps to extend this class.
 * Read full explanation at the bottom of this file.
 * 
 **/

class NAILS_Timezone_model extends NAILS_Model
{
	public function get_all()
	{
		$this->db->select( 'tz.id,tz.gmt_offset,tz.label' );
		$this->db->order_by( 'tz.gmt_offset', 'DESC' );
		$this->db->order_by( 'tz.label' );
		return $this->db->get( 'timezone tz' )->result();
	}
	
	
	// --------------------------------------------------------------------------
	
	
	public function get_all_flat()
	{
		$_out		= array();
		$_timezones	= $this->get_all();
		
		for( $i=0; $i<count( $_timezones ); $i++ ) :
		
			$_sign = $_timezones[$i]->gmt_offset < 0 ? '' : '+';
			$_out[$_timezones[$i]->id] = 'GMT ' . $_sign . $_timezones[$i]->gmt_offset . ' ' . $_timezones[$i]->label;
		
		endfor;
		
		// --------------------------------------------------------------------------
		
		return $_out;
	}
}


// --------------------------------------------------------------------------


/**
 * OVERLOADING NAILS'S MODELS
 * 
 * The following block of code makes it simple to extend one of the core
 * models. Some might argue it's a little hacky but it's a simple 'fix'
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
 
if ( ! defined( 'NAILS_ALLOW_EXTENSION_TIMEZONE_MODEL' ) ) :

	class Timezone_model extends NAILS_Timezone_model
	{
	}

endif;


/* End of file timezone_model.php */
/* Location: ./system/application/models/timezone_model.php */