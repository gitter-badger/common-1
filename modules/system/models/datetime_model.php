<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Name:		Datetime_model
 *
 * Description:	This model contains all methods for handling dates, times and timezones
 * 
 **/

/**
 * OVERLOADING NAILS' MODELS
 * 
 * Note the name of this class; done like this to allow apps to extend this class.
 * Read full explanation at the bottom of this file.
 * 
 **/

class NAILS_Datetime_model extends NAILS_Model
{
	protected $_timezone_nails;
	protected $_timezone_user;
	protected $_format_date;
	protected $_format_time;

	// --------------------------------------------------------------------------

	public function set_usr_obj( &$usr )
	{
		$this->user =& $usr;
	}


	// --------------------------------------------------------------------------


	public function set_timezones( $tz_nails, $tz_user )
	{
		$this->_timezone_nails	= $tz_nails;
		$this->_timezone_user	= $tz_user;
	}


	// --------------------------------------------------------------------------


	public function set_formats( $date, $time )
	{
		$this->_format_date = $date;
		$this->_format_time = $time;
	}


	// --------------------------------------------------------------------------


	public function user_date( $timestamp = NULL, $format_date = NULL, $format_time = NULL )
	{
		//	Has a specific timestamp been given?
		if ( is_null( $timestamp ) ) :

			$timestamp = date( 'Y-m-d H:i:s' );

		else :

			//	Are we dealing with a UNIX timestamp or a datetime?
			if ( is_numeric( $timestamp ) ) :

				$timestamp = date( 'Y-m-d H:i:s', strtotime( $timestamp ) );

			endif;

		endif;

		// --------------------------------------------------------------------------

		//	Has a date/time format been supplied? If so overwrite the defaults
		$_format_date	= is_null( $format_date ) ? $this->_format_date : $format_date; 
		$_format_time	= is_null( $format_time ) ? $this->_format_time : $format_time; 

		// --------------------------------------------------------------------------

		//	Create the new DateTime object
		$_datetime = new DateTime( $timestamp, new DateTimeZone( $this->_timezone_nails ) );

		// --------------------------------------------------------------------------

		//	If the user's timezone is different from the Nails. timezone then set it so.
		if ( $this->_timezone_nails != $this->_timezone_user )
			$_datetime->setTimeZone( new DateTimeZone( $this->_timezone_user ) );

		// --------------------------------------------------------------------------

		//	Return the formatted date
		return $_datetime->format( $_format_date . ' ' . $_format_time );
	}


	// --------------------------------------------------------------------------

	//	DATE FORMAT METHODS

	// --------------------------------------------------------------------------

	public function get_all_date_format()
	{
		return $this->db->get( 'date_format_date dfd' )->result();
	}


	// --------------------------------------------------------------------------

	public function get_all_date_format_flat()
	{
		$_out		= array();
		$_formats	= $this->get_all_date_format();
		
		for( $i=0; $i<count( $_formats ); $i++ ) :
		
			$_out[$_formats[$i]->id] = $_formats[$i]->label;
		
		endfor;
		
		// --------------------------------------------------------------------------
		
		return $_out;
	}


	// --------------------------------------------------------------------------

	//	TIME FORMAT METHODS

	// --------------------------------------------------------------------------

	public function get_all_time_format()
	{
		return $this->db->get( 'date_format_time dft' )->result();
	}


	// --------------------------------------------------------------------------

	public function get_all_time_format_flat()
	{
		$_out		= array();
		$_formats	= $this->get_all_time_format();
		
		for( $i=0; $i<count( $_formats ); $i++ ) :
		
			$_out[$_formats[$i]->id] = $_formats[$i]->label;
		
		endfor;
		
		// --------------------------------------------------------------------------
		
		return $_out;
	}

	// --------------------------------------------------------------------------

	//	TIMEZONE METHODS

	// --------------------------------------------------------------------------

	public function get_all_timezone()
	{
		//	Hat-tip to: https://gist.github.com/serverdensity/82576
		$_zones		= DateTimeZone::listIdentifiers();
		$_locations	= array();

		foreach ( $_zones as $zone ) :
		
			$zoneExploded = explode( '/', $zone ); // 0 => Continent, 1 => City
			
			// Only use "friendly" continent names
			if ( $zoneExploded[0] == 'Africa' || $zoneExploded[0] == 'America' || $zoneExploded[0] == 'Antarctica' || $zoneExploded[0] == 'Arctic' || $zoneExploded[0] == 'Asia' || $zoneExploded[0] == 'Atlantic' || $zoneExploded[0] == 'Australia' || $zoneExploded[0] == 'Europe' || $zoneExploded[0] == 'Indian' || $zoneExploded[0] == 'Pacific' ) :
			       
				if ( isset( $zoneExploded[1] ) != '' ) :
				
					$area = str_replace( '_', ' ', $zoneExploded[1] );
					
					if ( ! empty( $zoneExploded[2] ) ) :
					
						$area = $area . ' (' . str_replace('_', ' ', $zoneExploded[2]) . ')';

					endif;
					
					$_locations[$zoneExploded[0]][$zone] = $area; // Creates array(DateTimeZone => 'Friendly name')

				endif;

			endif;

		endforeach;

		return $_locations;
	}
	
	
	// --------------------------------------------------------------------------
	
	
	public function get_all_timezone_flat()
	{
		//	Hat-tip to: https://gist.github.com/serverdensity/82576
		$_zones		= DateTimeZone::listIdentifiers();
		$_locations	= array();

		foreach ( $_zones as $zone ) :
		
			$zoneExploded = explode( '/', $zone ); // 0 => Continent, 1 => City
			
			// Only use "friendly" continent names
			if ( $zoneExploded[0] == 'Africa' || $zoneExploded[0] == 'America' || $zoneExploded[0] == 'Antarctica' || $zoneExploded[0] == 'Arctic' || $zoneExploded[0] == 'Asia' || $zoneExploded[0] == 'Atlantic' || $zoneExploded[0] == 'Australia' || $zoneExploded[0] == 'Europe' || $zoneExploded[0] == 'Indian' || $zoneExploded[0] == 'Pacific' ) :
			       
				if ( isset( $zoneExploded[1] ) != '' ) :
				
					$area = str_replace( '_', ' ', $zoneExploded[1] );
					
					if ( ! empty( $zoneExploded[2] ) ) :
					
						$area = $area . ' (' . str_replace('_', ' ', $zoneExploded[2]) . ')';

					endif;
					
					$_locations[$zone] = $zoneExploded[0] . ' - ' . $area; // Creates array(DateTimeZone => 'Friendly name')

				endif;

			endif;

		endforeach;
		
		return $_locations;
	}


	// --------------------------------------------------------------------------

	//	OTHER METHODS

	// --------------------------------------------------------------------------

	public function nice_time( $date = FALSE, $tense = TRUE, $opt_bad_msg = NULL, $greater_1_week = NULL, $less_10_mins = NULL )
	{
		if( empty( $date ) || $date == '0000-00-00' ) :
		
			if ( $opt_bad_msg ) :
			
				return $opt_bad_msg;
				
			else :
			
				return 'No date supplied';
				
			endif;
			
		endif;
		
		$periods		= array( 'second', 'minute', 'hour', 'day', 'week', 'month', 'year', 'decade' );
		$lengths		= array( 60,60,24,7,'4.35', 12, 10 );
		
		$now			= time();
		
		if ( is_int( $date ) ) :
		
			$unix_date = $date;
			
		else :
		
			$unix_date = strtotime( $date );
			
		endif;
		
		//	Check date supplied is valid
		if( empty( $unix_date ) ) :
		
			if ( $opt_bad_msg ) :
			
				return $opt_bad_msg;
			
			else :
			
				return 'Bad date supplied ('.$date.')';
			
			endif;
			
		endif;
		
		//	If date is effectively NULL
		if ( $date == '0000-00-00 00:00:00' )
			return 'Unknown';
		
		//	Determine past or future date
		if( $now >= $unix_date ) :
			$difference = $now - $unix_date;
			
			if( $tense === TRUE ) :
				$tense = 'ago';
			endif;
		
		else :
			$difference = $unix_date - $now;
			if( $tense === TRUE ) :
				$tense = 'from now';
			endif;
		endif;
		
		for( $j = 0; $difference >= $lengths[$j] && $j < count( $lengths )-1; $j++ ) :
			$difference /= $lengths[$j];
		endfor;
		
		$difference = round( $difference );
		
		if( $difference != 1 ) :
			$periods[$j] .= 's';
		endif;
		
		// If it's greater than 1 week and $greater_1_week is defined, return that
		if ( substr( $periods[$j], 0, 4 ) == 'week' && $greater_1_week !== NULL)
			return $greater_1_week;
			
		// If it's less than 20 seconds, return 'Just now'
		if ( is_null( $less_10_mins ) && substr( $periods[$j], 0, 6 ) == 'second' && $difference <=20 )
			return 'a moment ago';
			
		//	If $less_10_mins is set then return that if less than 10 minutes
		if (	! is_null( $less_10_mins )
				&&
				(
					(substr( $periods[$j], 0, 6 ) == 'minute'	&& $difference <=10)||
					(substr( $periods[$j], 0, 6 ) == 'second'	&& $difference <=60)
				)
			)
			return $less_10_mins;
		
		if( "{$difference} {$periods[$j]} {$tense}" == '1 day ago') :
		
			return 'Yesterday';
		
		else :
		
			return "{$difference} {$periods[$j]} {$tense}";
			
		endif;
	}
}


// --------------------------------------------------------------------------


/**
 * OVERLOADING NAILS' MODELS
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
 
if ( ! defined( 'NAILS_ALLOW_EXTENSION_DATETIME_MODEL' ) ) :

	class Datetime_model extends NAILS_Datetime_model
	{
	}

endif;


/* End of file datetime_model.php */
/* Location: ./system/application/models/datetime_model.php */