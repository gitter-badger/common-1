<div class="group-blog settings">
	<p>
		Configure various aspects of the blog.
	</p>

	<hr />

		<ul class="tabs">
			<?php $_active = $this->input->post( 'update' ) == 'settings' || ! $this->input->post() ? 'active' : ''?>
			<li class="tab <?=$_active?>">
				<a href="#" data-tab="tab-general">General</a>
			</li>
		</ul>

		<section class="tabs pages">

			<?php $_display = $this->input->post( 'update' ) == 'settings' || ! $this->input->post() ? 'active' : ''?>
			<div id="tab-general" class="tab page <?=$_display?> general">
				<?=form_open( NULL, 'style="margin-bottom:0;"')?>
				<?=form_hidden( 'update', 'settings' )?>
				<p>
					Generic blog settings. Use these to control some blog behaviours.
				</p>
				<hr />
				<fieldset id="blog-settings-url">
					<legend>URL</legend>
					<p>
						Customise the blog's URL by specifying it here.
					</p>
					<?php

						if ( $settings['blog_url'] != 'blog/' ) :

							$_routes_file = file_get_contents( FCPATH . APPPATH . '/config/routes.php' );
							$_pattern = '#\$route\[\'' . str_replace( '/', '\/', substr( $settings['blog_url'], 0, -1 ) ) . '\(\/\(\:any\)\?\/\?\)\?\'\]\s*?=\s*?\'blog\/\$2\'\;#';

							if ( ! preg_match( $_pattern, $_routes_file) ) :

								//	Check the routes_app file
								$_routes_file = @file_get_contents( FCPATH . APPPATH . '/config/routes_app.php' );

								if ( ! $_routes_file || ! preg_match( $_pattern, $_routes_file ) ) :

									echo '<p class="system-alert message no-close">';
									echo '<strong>Please Note:</strong> Ensure that the following route is in the app\'s <code>routes.php</code> or <code>routes_app.php</code> file or the blog may not work as expected.';
									echo '<code style="display:block;margin-top:10px;border:1px solid #CCC;background:#EFEFEF;padding:10px;">$route[\'' . substr( $settings['blog_url'], 0, -1 ) . '(/(:any)?/?)?\'] = \'blog/$2\';</code>';
									echo '</p>';

								endif;

							endif;

						endif;

						// --------------------------------------------------------------------------

						//	Blog URL
						$_field					= array();
						$_field['key']			= 'blog_url';
						$_field['label']		= 'Blog URL';
						$_field['default']		= $settings['blog_url'];
						$_field['placeholder']	= 'Customise the Blog\'s URL (include trialing slash)';

						echo form_field( $_field );

					?>
				</fieldset>

				<fieldset id="blog-settings-excerpts">
					<legend>Post Excerpts</legend>
					<p>
						Excerpts are short post summaries of posts. If enabled these sumamries will be shown
						beneath the title of the post on the main blog page (i.e to read the full post the
						user will have to click through to the post itself).
					</p>
					<?php

						//	Enable/disable post excerpts
						$_field					= array();
						$_field['key']			= 'use_excerpts';
						$_field['label']		= 'Use excerpts';
						$_field['default']		= $settings['use_excerpts'];

						echo form_field_boolean( $_field );
					?>
				</fieldset>

				<fieldset id="blog-settings-cattag">
					<legend>Categories &amp; Tags</legend>
					<?php

						//	Enable/disable categories
						$_field					= array();
						$_field['key']			= 'categories_enabled';
						$_field['label']		= 'Categories';
						$_field['default']		= $settings['categories_enabled'];

						echo form_field_boolean( $_field );

						// --------------------------------------------------------------------------

						//	Enable/disable tags
						$_field					= array();
						$_field['key']			= 'tags_enabled';
						$_field['label']		= 'Tags';
						$_field['default']		= $settings['tags_enabled'];

						echo form_field_boolean( $_field );

					?>
				</fieldset>
				<p style="margin-top:1em;margin-bottom:0;">
					<?=form_submit( 'submit', lang( 'action_save_changes' ), 'style="margin-bottom:0;"' )?>
				</p>
				<?=form_close()?>
			</div>

		</section>
</div>