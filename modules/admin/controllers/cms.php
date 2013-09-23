<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* Name:			Admin : Basic CMS
*
* Description:	A basic CMS for applications
*
*/

require_once NAILS_PATH . 'modules/admin/controllers/_admin.php';

/**
 * OVERLOADING NAILS' ADMIN MODULES
 *
 * Note the name of this class; done like this to allow apps to extend this class.
 * Read full explanation at the bottom of this file.
 *
 **/

class NAILS_Cms extends NAILS_Admin_Controller
{

	/**
	 * Announces this module's details to those in the know.
	 *
	 * @access	static
	 * @param none
	 * @return	void
	 **/
	static function announce()
	{
		if ( ! module_is_enabled( 'cms' ) ) :

			return FALSE;

		endif;

		// --------------------------------------------------------------------------

		$d = new stdClass();

		// --------------------------------------------------------------------------

		//	Configurations
		$d->name				= 'Content Management';					//	Display name.

		// --------------------------------------------------------------------------

		//	Navigation options
		$d->funcs				= array();
		$d->funcs['pages']		= 'Manage Pages';					//	Sub-nav function.
		$d->funcs['blocks']		= 'Manage Blocks';					//	Sub-nav function.
		$d->funcs['sliders']	= 'Manage Sliders';					//	Sub-nav function.

		// --------------------------------------------------------------------------

		//	Only announce the controller if the user has permission to know about it
		return self::_can_access( $d, __FILE__ );
	}


	// --------------------------------------------------------------------------


	public function __construct()
	{
		parent::__construct();

		// --------------------------------------------------------------------------

		//	Load helpers
		$this->load->helper( 'cms' );

		// --------------------------------------------------------------------------

		//	Load the CKEditor librar
		$this->asset->library( 'ckeditor' );
	}


	// --------------------------------------------------------------------------

	/* ! PAGES */

	// --------------------------------------------------------------------------


	/**
	 * Route requests for 'block' editing
	 *
	 * @access public
	 * @param none
	 * @return	void
	 **/
	public function pages()
	{
		//	Load common blocks items
		$this->load->model( 'cms/cms_page_model', 'cms_page' );

		// --------------------------------------------------------------------------

		$_method = $this->uri->segment( 4 ) ? $this->uri->segment( 4 ) : 'index';

		if ( method_exists( $this, '_pages_' . $_method ) ) :


			if ( ! $this->cms_page->can_write_routes() ) :

				$this->data['message'] = '<strong>Hey!</strong> There\'s a problem with the routing system: ' . implode( '', $this->cms_page->get_error() );

			endif;

			// --------------------------------------------------------------------------

			$this->{'_pages_' . $_method}();

		else :

			show_404();

		endif;
	}


	// --------------------------------------------------------------------------


	/**
	 * Manage pages of content
	 *
	 * @access public
	 * @param none
	 * @return	void
	 **/
	protected function _pages_index()
	{
		//	Page Title
		$this->data['page']->title = 'Manage Pages';

		// --------------------------------------------------------------------------

		//	Fetch all the pages in the DB
		$this->data['pages'] = $this->cms_page->get_all();

		// --------------------------------------------------------------------------

		//	Assets
		$this->asset->load( 'mustache.min.js', TRUE );
		$this->asset->load( 'nails.admin.cms.pages.min.js', TRUE );

		// --------------------------------------------------------------------------

		$this->load->view( 'structure/header',		$this->data );
		$this->load->view( 'admin/cms/pages/index',	$this->data );
		$this->load->view( 'structure/footer',		$this->data );
	}


	// --------------------------------------------------------------------------


	/**
	 * Edit a page
	 *
	 * @access public
	 * @param none
	 * @return	void
	 **/
	protected function _pages_edit()
	{
		$this->data['cmspage'] = $this->cms_page->get_by_id( $this->uri->segment( 5 ), TRUE );

		if ( ! $this->data['cmspage'] ) :

			$this->session->set_flashdata( 'error', '<strong>Sorry,</strong> no page found by that ID' );

		endif;

		// --------------------------------------------------------------------------

		//	Load form validation (for error checking in the view, always needs t be available)
		$this->load->library( 'form_validation' );

		// --------------------------------------------------------------------------

		if ( $this->input->post() ) :

			//	Set Rules
			$this->form_validation->set_rules( 'title',				'Title',			'xss_clean|required' );
			$this->form_validation->set_rules( 'slug',				'Slug',				'xss_clean|callback__callback_slug' );
			$this->form_validation->set_rules( 'seo_description',	'SEO Description',	'xss_clean|required' );
			$this->form_validation->set_rules( 'seo_keywords',		'SEO Keywords',		'xss_clean|required' );

			//	Set messages
			$this->form_validation->set_message( 'required', lang( 'fv_required' ) );

			//	Loop the widgets and get any widget specific validation rules
			$_areas = array( 'hero', 'body', 'sidebar' );

			foreach ( $_areas AS $area ) :

				if ( is_array( $this->input->post( 'widgets_' . $area ) ) ) :

					foreach( $this->input->post( 'widgets_' . $area ) AS $postkey => $widget ) :

						foreach( $widget AS $field => $value ) :

							//	Skip the slug
							if ( $field == 'slug' ) :

								continue;

							endif;

							$_rules = $this->cms_page->get_widget_validation_rules( $widget['slug'], $field );

							if ( $_rules ) :

								$this->form_validation->set_rules( 'widgets_' . $area . '[' . $postkey . '][' . $field . ']', $field,	$_rules );

							endif;

						endforeach;

					endforeach;

				endif;

			endforeach;

			//	Execute
			if ( $this->form_validation->run( $this ) ) :

				//	Update the page
				$_data					= new stdClass();
				$_data->title			= $this->input->post( 'title' );
				$_data->slug			= $this->input->post( 'slug' );
				$_data->layout			= $this->input->post( 'layout' );
				$_data->sidebar_width	= $this->input->post( 'sidebar_width' );
				$_data->seo_description	= $this->input->post( 'seo_description' );
				$_data->seo_keywords	= $this->input->post( 'seo_keywords' );

				foreach ( $_areas AS $area ) :

					$_data->{'widgets_' . $area }	= $this->input->post( 'widgets_' . $area );

				endforeach;

				if ( $this->cms_page->update( $this->uri->segment( 5 ), $_data ) ) :

					//	Saved!
					$this->session->set_flashdata( 'success', '<strong>Success!</strong> Page updated.' );
					redirect( 'admin/cms/pages' );

				else :

					$this->data['error'] = '<strong>Sorry,</strong> there was a problem saving the page: ' . implode( $this->cms_page->get_error() );

				endif;

			else :

				$this->data['error'] = lang( 'fv_there_were_errors' );

			endif;

		endif;

		// --------------------------------------------------------------------------

		//	Set method info
		$this->data['page']->title	= 'Edit Page "' . $this->data['cmspage']->title . '"';

		//	Get available widgets
		$this->data['widgets']	= $this->cms_page->get_available_widgets();

		// --------------------------------------------------------------------------

		//	Assets
		$this->asset->load( 'mustache.min.js', TRUE );
		$this->asset->load( 'nails.admin.cms.pages.editor.min.js', TRUE );

		// --------------------------------------------------------------------------

		$this->load->view( 'structure/header',		$this->data );
		$this->load->view( 'admin/cms/pages/edit',	$this->data );
		$this->load->view( 'structure/footer',		$this->data );
	}


	// --------------------------------------------------------------------------


	protected function _pages_rewrite_routes()
	{
		if ( $this->cms_page->write_routes() ) :

			$this->session->set_flashdata( 'success', '<strong>Success!</strong> Routes rewritten successfully.' );

		else :

			$this->session->set_flashdata( 'error', '<strong>Sorry,</strong> there was a problem writing the routes:<br />' . array_pop( $this->cms_page->get_error() ) );

		endif;

		// --------------------------------------------------------------------------

		redirect( 'admin/cms/pages' );
	}


	// --------------------------------------------------------------------------


	public function _callback_slug( $str )
	{
		$str = trim( $str );

		//	Check is valid
		if ( preg_match( '/[^a-zA-Z0-9\-_\/\.]+/', $str ) ) :

			$this->form_validation->set_message( '_callback_slug', 'Contains invalid characters (A-Z, 0-9, -, _ and / only).' );
			return FALSE;

		endif;

		// --------------------------------------------------------------------------

		//	Prepare the slug
		$str = explode( '/', trim( $str ) );
		foreach ( $str AS &$value ) :

			$value = url_title( $value, 'dash', TRUE );

		endforeach;
		$str = implode( '/', $str );

		// --------------------------------------------------------------------------

		$this->db->where( 'id !=', $this->uri->segment( 5 ) );
		$this->db->where( 'slug', $str );

		if ( $this->db->count_all_results( 'cms_page' ) ) :

			$this->form_validation->set_message( '_callback_slug', 'Slug must be unique.' );
			return FALSE;

		endif;

		// --------------------------------------------------------------------------

		return TRUE;
	}


	// --------------------------------------------------------------------------

	/* ! BLOCKS */

	// --------------------------------------------------------------------------


	/**
	 * Route requests for 'block' editing
	 *
	 * @access public
	 * @param none
	 * @return	void
	 **/
	public function blocks()
	{
		//	Load common blocks items
		$this->load->model( 'cms/cms_block_model', 'cms_block' );
		$this->asset->load( 'mustache.min.js', TRUE );
		$this->asset->load( 'nails.admin.cms.blocks.min.js', TRUE );

		// --------------------------------------------------------------------------

		//	Define block types; block types allow for proper validation
		$this->data['block_types']				= array();
		$this->data['block_types']['plaintext']	= 'Plain Text';
		$this->data['block_types']['richtext']	= 'Rich Text';
		//$this->data['block_types']['image']		= 'Image (*.jpg, *.png, *.gif)';
		//$this->data['block_types']['file']		= 'File (*.*)';
		//$this->data['block_types']['number']	= 'Number';
		//$this->data['block_types']['url']		= 'URL';

		// --------------------------------------------------------------------------

		$_method = $this->uri->segment( 4 ) ? $this->uri->segment( 4 ) : 'index';

		if ( method_exists( $this, '_blocks_' . $_method ) ) :

			$this->{'_blocks_' . $_method}();

		else :

			show_404();

		endif;
	}


	// --------------------------------------------------------------------------


	protected function _blocks_index()
	{
		//	Set method info
		$this->data['page']->title		= 'Manage Blocks';

		// --------------------------------------------------------------------------

		$this->data['blocks']		= $this->cms_block->get_all();
		$this->data['languages']	= $this->language->get_all_supported_flat();

		// --------------------------------------------------------------------------

		$this->load->view( 'structure/header',			$this->data );
		$this->load->view( 'admin/cms/blocks/index',	$this->data );
		$this->load->view( 'structure/footer',			$this->data );
	}


	// --------------------------------------------------------------------------


	protected function _blocks_edit()
	{
		$this->data['block'] = $this->cms_block->get_by_id( $this->uri->segment( 5 ), TRUE );

		if ( ! $this->data['block'] ) :

			$this->session->set_flashdata( 'error', '<strong>Sorry,</strong> no block found by that ID' );

		endif;

		// --------------------------------------------------------------------------

		if ( $this->input->post() ) :

			//	Loop through and update translations, keep track of translations which have been updated
			$_updated = array();
			foreach ( $this->input->post( 'translation' ) AS $translation ) :

				$this->cms_block->update_translation( $this->data['block']->id, $translation['lang_id'], $translation['value'] );
				$_updated[] = $translation['lang_id'];

			endforeach;

			//	Delete translations that weren't updated (they have been removed)
			if ( $_updated ) :

				$this->db->where( 'block_id', $this->data['block']->id );
				$this->db->where_not_in( 'lang_id', $_updated );
				$this->db->delete( 'cms_block_translation' );

			endif;

			//	Loop through and add new translations
			foreach ( $this->input->post( 'new_translation' ) AS $translation ) :

				$this->cms_block->create_translation( $this->data['block']->id, $translation['lang_id'], $translation['value'] );

			endforeach;

			// --------------------------------------------------------------------------

			//	Send the user on their merry way
			$this->session->set_flashdata( 'success', '<strong>Success!</strong> The block was updated successfully!' );
			redirect( 'admin/cms/blocks' );
			return;

		endif;

		// --------------------------------------------------------------------------

		//	Set method info
		$this->data['page']->title	= 'Edit Block "' . $this->data['block']->title . '"';

		// --------------------------------------------------------------------------

		//	Fetch data
		$this->data['languages']	= $this->language->get_all_supported_flat();
		$this->data['default_id']	= $this->language->get_default_id();

		// --------------------------------------------------------------------------

		$this->load->view( 'structure/header',		$this->data );
		$this->load->view( 'admin/cms/blocks/edit',	$this->data );
		$this->load->view( 'structure/footer',		$this->data );
	}


	// --------------------------------------------------------------------------


	protected function _blocks_create()
	{
		if ( ! $this->user->is_superuser() ) :

			show_404();

		endif;

		// --------------------------------------------------------------------------

		if ( $this->input->post() ) :

			//	Form Validation
			$this->load->library( 'form_validation' );

			$this->form_validation->set_rules( 'slug',			'Slug',			'xss_clean|required|callback__fvcallback_block_slug' );
			$this->form_validation->set_rules( 'title',			'Title',		'xss_clean|required' );
			$this->form_validation->set_rules( 'description',	'Description',	'xss_clean|required' );
			$this->form_validation->set_rules( 'located',		'Located',		'xss_clean|required' );
			$this->form_validation->set_rules( 'type',			'Block Type',	'xss_clean|required|callback__fvcallback_block_type' );
			$this->form_validation->set_rules( 'value',			'Value',		'xss_clean|required' );

			$this->form_validation->set_message( 'required',			lang( 'fv_required' ) );
			$this->form_validation->set_message( 'string_to_boolean',	lang( 'fv_required' ) );

			if ( $this->form_validation->run( $this ) ) :

				$_type	= $this->input->post( 'type' );
				$_slug	= $this->input->post( 'slug' );
				$_title	= $this->input->post( 'title' );
				$_desc	= $this->input->post( 'description' );
				$_loc	= $this->input->post( 'located' );
				$_val	= $this->input->post( 'value' );

				if ( $this->cms_block->create_block( $_type, $_slug, $_title, $_desc, $_loc, $_val ) ) :

					$this->session->set_flashdata( 'success', '<strong>Success!</strong> Block created successfully.' );
					redirect( 'admin/cms/blocks' );
					return;

				else :

					$this->data['error'] = '<strong>Sorry,</strong> there was a problem creating the new block.';

				endif;

			else :

				$this->data['error'] = lang( 'fv_there_were_errors' );

			endif;

		endif;

		// --------------------------------------------------------------------------

		$this->data['languages'] = $this->language->get_all_supported_flat();

		// --------------------------------------------------------------------------

		$this->load->view( 'structure/header',			$this->data );
		$this->load->view( 'admin/cms/blocks/create',	$this->data );
		$this->load->view( 'structure/footer',			$this->data );
	}


	// --------------------------------------------------------------------------


	public function _fvcallback_block_slug( $slug )
	{
		$slug = trim( $slug );

		//	Check slug's characters are ok
		if ( ! preg_match( '/[^a-zA-Z0-9\-\_]/', $slug ) ) :

			$_block = $this->cms_block->get_by_slug( $slug );

			if ( ! $_block ) :

				//	OK!
				return TRUE;

			else :

				$this->form_validation->set_message( '_fvcallback_block_slug', 'Must be unique' );
				return FALSE;

			endif;

		else :

			$this->form_validation->set_message( '_fvcallback_block_slug', 'Invalid characters' );
			return FALSE;

		endif;
	}


	// --------------------------------------------------------------------------


	public function _fvcallback_block_type( $type )
	{
		$type = trim( $type );

		if ( $type ) :

			if ( isset( $this->data['block_types'][$type] ) ) :

				return TRUE;

			else :

				$this->form_validation->set_message( '_fvcallback_block_type', 'Block type not supported.' );
				return FALSE;

			endif;

		else :

			$this->form_validation->set_message( '_fvcallback_block_type', lang( 'fv_required' ) );
			return FALSE;

		endif;
	}


	// --------------------------------------------------------------------------


	public function sliders()
	{
		//	Load common slider items
		//$this->load->model( 'cms/cms_block_model', 'cms_block' );
		//$this->asset->load( 'mustache.min.js', TRUE );
		//$this->asset->load( 'nails.admin.cms.blocks.min.js', TRUE );

		// --------------------------------------------------------------------------

		$_method = $this->uri->segment( 4 ) ? $this->uri->segment( 4 ) : 'index';

		if ( method_exists( $this, '_sliders_' . $_method ) ) :

			$this->{'_sliders_' . $_method}();

		else :

			show_404();

		endif;
	}


	// --------------------------------------------------------------------------


	protected function _sliders_index()
	{
		$this->data['page']->title = 'Manage Sliders';

		// --------------------------------------------------------------------------

		//	Fetch all the sliders in the DB
		//$this->data['sliders'] = $this->cms_page->get_all();

		// --------------------------------------------------------------------------

		//	Assets
		//$this->asset->load( 'mustache.min.js', TRUE );
		//$this->asset->load( 'nails.admin.cms.pages.min.js', TRUE );

		// --------------------------------------------------------------------------

		$this->load->view( 'structure/header',			$this->data );
		$this->load->view( 'admin/cms/sliders/index',	$this->data );
		$this->load->view( 'structure/footer',			$this->data );
	}
}


// --------------------------------------------------------------------------


/**
 * OVERLOADING NAILS' ADMIN MODULES
 *
 * The following block of code makes it simple to extend one of the core admin
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
 * If/when we want to extend the main class we simply define NAILS_ALLOW_EXTENSION_CLASSNAME
 * before including this PHP file and extend as normal (i.e in the same way as below);
 * the helper won't be declared so we can declare our own one, app specific.
 *
 **/

if ( ! defined( 'NAILS_ALLOW_EXTENSION_CMS' ) ) :

	class Cms extends NAILS_Cms
	{
	}

endif;


/* End of file admin.php */
/* Location: ./application/modules/admin/controllers/admin.php */