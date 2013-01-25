<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Name:		Admin Help Model
 *
 * Docs:		http://nails.shedcollective.org/docs/users/
 *
 * Created:		18/11/2012
 * Modified:	18/11/2012
 *
 * Description:	This model contains logic for the amdin help pages.
 * 
 */

class Admin_help_model extends NAILS_Model
{
	public function get_all()
	{
		$this->db->select( 'id, title, description, vimeo_id' );
		return $this->db->get( 'admin_help_video' )->result();
	}
}

/* End of file admin_help_model.php */
/* Location: ./application/models/admin_help_model.php */