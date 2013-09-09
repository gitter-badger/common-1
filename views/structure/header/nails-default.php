<!DOCTYPE html>
<!--[if lt IE 7 ]><html class="ie ie6" lang="en"> <![endif]-->
<!--[if IE 7 ]><html class="ie ie7" lang="en"> <![endif]-->
<!--[if IE 8 ]><html class="ie ie8" lang="en"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!--><html lang="en"> <!--<![endif]-->
	<head>

		<!--	META	-->
		<meta charset="utf-8">
		<title><?=isset( $page->title ) && $page->title ? $page->title . ' - ' : ''?><?=APP_NAME?></title>
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
		<meta name="description" content="<?=isset( $page->description ) && $page->description ? $page->description : ''?>">
		<meta name="description" content="<?=isset( $page->keywords ) && $page->keywords ? $page->keywords : ''?>">

		<!--	JS GLOBALS	-->
		<script type="text/javascript" src="<?=NAILS_URL?>config/assets.json?uid=<?=uniqid()?>"></script>
		<script type="text/javascript">
			var ENVIRONMENT				= '<?=ENVIRONMENT?>';
			window.SITE_URL				= '<?=site_url()?>';
			window.NAILS				= {}
			window.NAILS.URL			= '<?=NAILS_URL?>';
			window.NAILS.LANG			= {};
			window.NAILS.ASSETS_RELEASE = <?=NAILS_ASSETS_RELEASE?>;
		</script>

		<noscript>
			<style type="text/css">

				.js-only
				{
					display:none;
				}

			</style>
		</noscript>

		<!--	STYLES	-->
		<?php

			$this->asset->output();

		?>

		<!--	HTML5 shim, for IE6-8 support of HTML5 elements	-->
		<!--[if lt IE 9]>
			<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
		<![endif]-->

	</head>
	<body>

	<div class="container">

		<!--	HEADER	-->
		<div class="row" id="nails-default-header">
			<div class="sixteen columns">
				<h1><a href="<?=site_url()?>" class="brand"><?=APP_NAME?></a></h1>
			</div>
		</div>


		<!--	CONTENT	-->
		<div class="row" id="nails-default-content">
			<div class="sixteen columns">

				<?=isset( $page->title ) ? '<h2>' . $page->title . '</h2>' : NULL?>

				<!--	SYSTEM ALERTS	-->
				<?php if ( isset( $error ) && $error ) : ?>
					<div class="system-alert error">
						<div class="padder">
							<p>
								<?=$error?>
							</p>
						</div>
					</div>
				<?php endif; ?>

				<?php if ( isset( $success ) && $success ) : ?>
					<div class="system-alert success">
						<div class="padder">
							<p>
								<?=$success?>
							</p>
						</div>
					</div>
				<?php endif; ?>

				<?php if ( isset( $message ) && $message ) : ?>
					<div class="system-alert message">
						<div class="padder">
							<p>
								<?=$message?>
							</p>
						</div>
					</div>
				<?php endif; ?>

				<?php if ( isset( $notice ) && $notice ) : ?>
					<div class="system-alert notice">
						<div class="padder">
							<p>
								<?=$notice?>
							</p>
						</div>
					</div>
				<?php endif; ?>