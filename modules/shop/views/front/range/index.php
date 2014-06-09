<div class="container">
	<div class="row">
		<div class="jumbotron <?=BS_COL_LG_10?> <?=BS_COL_LG_OFFSET_1?>">
			<h3 class="text-center">
				Ranges
			</h3>
			<h4 class="text-center">
				The "Phillip J. Fry" collection, it's out of this world
			</h4>
			<hr />
			<p>
				This page is shown when no particular range is requested. It should probably
				list all the ranges available in your store; good for SEO, maybe.
			</p>
			<p>
				This page can be enabled/disabled in Shop Settings.
			</p>
			<hr />
			<p>
				You'll want to override this view in your app by placing a view here:
			</p>
			<?php

				echo '<pre>';
				echo str_replace( NAILS_PATH, FCPATH . APPPATH , __FILE__ );
				echo '</pre>';

			?>
			<h5>
				Available Data
			</h5>
			<ul class="list-group">
			<?php

				$_data_available					= array();
				$_data_available[0]					= new stdClass();
				$_data_available[0]->variable		= 'ranges';
				$_data_available[0]->description	= 'An array of the ranges containing active items, including count.';

				// --------------------------------------------------------------------------

				foreach( $_data_available AS $index => $item ) :

					$this->load->view( 'shop/front/_utilities/variable', array( 'index' => $index, 'item' => $item ) );

				endforeach;

			?>
			</ul>
			<?php

				if ( ! empty( ${$_data_available[0]->variable}[0]->slug ) ) :

					echo '<h5>Other Pages</h5>';
					echo '<p>Here are some handy links to other pages handled by the Shop module:</p>';

					echo '<ul class="list-unstyled">';

						echo '<li>&rsaquo; ' . anchor( app_setting( 'url', 'shop' ) . 'range/' . ${$_data_available[0]->variable}[0]->slug, 'Single Range page' ) . '</li>';

					echo '</ul>';

				endif;

			?>
		</div>
	</div>
</div>
<?php

	$this->load->view( 'shop/front/_utilities/css_js' );