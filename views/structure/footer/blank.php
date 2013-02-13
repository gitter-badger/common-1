		<!--	GLOBAL JS	-->
		<script tyle="text/javascript">
		<!--//
		
			var _nails;
			
			$(function(){
			
				//	Initialise Nails_JS
				_nails = new NAILS_JS();
				_nails.init();
			
			});
						
			// --------------------------------------------------------------------------
			
			//	Fancyboxes
			$( '.fancybox' ).fancybox();
			
			// --------------------------------------------------------------------------
			
			//	Scroll to first field error
			var _inline	= $( 'div.field.error:visible' );
			var _scroll;
			
			if ( _inline.length )
			{
				//	Scroll to this item
				_scroll = $(_inline.get(0));
			}
			else
			{
				var _system = $( 'div.system-alert.error:visible' );
				_scroll = $(_system.get(0));
			}
			
			if ( _scroll.length )
			{
				//	Giving the browser a slight chance to work out sizes etc
				setTimeout( function() {$.scrollTo( _scroll, 'fast', { axis: 'y', offset : { top: -25 } } )}, 750 );
			}
			
			// --------------------------------------------------------------------------
			
			//	Global tabs
			var Tabs;
			Tabs = function()
			{
				this.init = function()
				{
					//	Bind handlers
					var _this = this;	/*	Ugly Scope Hack	*/
					
					$( 'li.tab a' ).on( 'click', function() { _this.switch_to_tab( $(this) ); return false; } );
				};
				
				
				// --------------------------------------------------------------------------
				
				
				this.switch_to_tab = function( switch_to )
				{
					//	Switch tab
					$( 'li.tab' ).removeClass( 'active' );
					switch_to.parent().addClass( 'active' );
					
					// --------------------------------------------------------------------------
					
					//	Show results
					var _tab = switch_to.attr( 'data-tab' );
					$( 'div.tab.page' ).hide();
					$( '#' + _tab ).show();
				};
			};
			
			var _tabs = new Tabs();
			_tabs.init();
			
			// --------------------------------------------------------------------------
			
			//	Global Forms
			var Forms;
			Forms = function()
			{
				this.init = function()
				{
					this._add_stripes();
				};
				
				
				// --------------------------------------------------------------------------
				
				
				this._add_stripes = function()
				{
					$( 'fieldset' ).each( function() {
						
						$( 'div.field', this ).removeClass( 'odd even' );
						$( 'div.field:visible:odd', this ).addClass( 'odd' );
						$( 'div.field:visible:even', this ).addClass( 'even' );
						
					});
				};
			};
			
			var _forms = new Forms();
			_forms.init();
		
		//-->
		</script>
	</body>
</html>