<hr />
<?php

	switch( blog_setting( 'comments_engine' ) ) :

		case 'NATIVE' :	$this->load->view( 'blog/_components/single_comments_native' );	break;
		case 'DISQUS' :	$this->load->view( 'blog/_components/single_comments_disqus' );	break;

	endswitch;