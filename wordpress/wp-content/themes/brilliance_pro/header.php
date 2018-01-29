<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>  
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

	<div class="outer" id="top">
		<?php do_action('cpotheme_before_wrapper'); ?>
		<div class="wrapper">
			<div id="topbar" class="topbar secondary-color-bg dark">
				<div class="container">
					<?php do_action('cpotheme_top'); ?>
					<div class="clear"></div>
				</div>
			</div>
			<header id="header" class="header <?php if( cpotheme_get_option('header_opaque') || cpotheme_show_title() ) echo ' header-opaque'; ?>" <?php cpotheme_show_header_image() ?>>
				<div class="header-wrapper secondary-color-bg">
					<div class="container">
						<?php do_action('cpotheme_header'); ?>
						<div class='clear'></div>
					</div>
				</div>
				<?php do_action('cpotheme_after_header_wrapper'); ?>
			</header>
			<?php do_action('cpotheme_before_main'); ?>
			<div class="clear"></div>