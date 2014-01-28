var NAILS_Admin_CMS_Menus;
NAILS_Admin_CMS_Menus = function()
{
	this.init_search = function()
	{
		var _this = this;	/*	Ugly Scope Hack	*/
		$( '.search-text input' ).on( 'keyup', function() { _this._do_search( $(this).val() ); return false; } );
	};


	// --------------------------------------------------------------------------


	this._do_search = function( term )
	{
		$( 'tr.menu' ).each(function()
		{
			var regex = new RegExp( term, 'gi' );

			if ( regex.test( $(this).attr( 'data-label' ) ) )
			{
				$(this).show();
			}
			else
			{
				$(this).hide();
			}
		});
	};
};