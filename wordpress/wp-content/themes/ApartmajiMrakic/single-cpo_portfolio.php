<?php get_header(); ?>

<div id="main" class="main">
	<div class="container">

		<!-- PORTFOLIO CONTENT -->
		<section id="content" class="content">
			<?php do_action('cpotheme_before_content'); ?>
			<?php if(have_posts()) while(have_posts()): the_post(); ?>
			<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<div class="page-content">
					<?php the_content(); ?>
				</div>
				<?php cpotheme_post_pagination(); ?>
				<div class="clear"></div>
			</div>
			<?php comments_template('', true); ?>
			<?php endwhile; ?>			
			<?php do_action('cpotheme_after_content'); ?>
		</section>

		<!-- PORTFOLIO DETAILS -->
		<section id="portfolio-details" class="portfolio-details content">
			<?php if(have_rows('basic_info') && have_rows('extras')){
				get_template_part('template-parts/element', 'portfolio-details');
			} ?>
		</section>

		<!-- ROOMS DETAILS -->
		<section id="portfolio-rooms-details">
			<?php 
				if(have_rows('rooms')){
					get_template_part('template-parts/element', 'portfolio-rooms');
				}
			?>
		</section>

		<!-- PORTFOLIO IMAGES -->
		<?php if(get_field('galerija_apartmaja') != null): ?>
			<section id="portfolio-gallery">			
				<?php echo get_field('galerija_apartmaja'); ?>
			</section>
		<?php endif; ?>
		<?php //cpotheme_post_media(get_the_ID(), get_post_meta(get_the_ID(), 'portfolio_layout', true)); ?>
		<?php get_sidebar(); ?>
		<div class="clear"></div>
	</div>
	<?php get_template_part('template-parts/element', 'portfolio-related'); ?>
</div>

<?php get_footer(); ?>