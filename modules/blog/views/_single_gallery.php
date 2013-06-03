<?php

	//	Render the Blog post's gallery Markup and call any JS.
	if ( $post->gallery_type == 'fancy' ) :

		//	This is a simple list of thumbnails with a fancybox gallery
		echo '<ul class="gallery fancy">';
		foreach ( $post->gallery AS $slide ) :

			echo '<li class="slide">';
			echo anchor( cdn_serve( 'blog', $slide->image_filename ), img( cdn_thumb( 'blog', $slide->image_filename, 100, 100 ) ), 'class="fancybox-gallery" data-fancybox-group="blog-post-gallery" title="' . $slide->image_caption . '"' );
			echo '</li>';

		endforeach;
		echo '</ul>';

		?>
		<script type="text/javascript">

			//	Satandard Nails fancybox init doesn't work as expected.
			$(function(){ $( 'a.fancybox-gallery' ).fancybox({ helpers : { title : { type : 'inside' } } }); });

		</script>
		<?php

	elseif( $post->gallery_type == 'slider' ) :

		dump( 'TODO: Slider Gallery' );

	endif;