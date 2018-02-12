<?php /* Template Name: Portfolio */ ?>
<?php get_header(); ?>

<div id="main" class="main">
	<div class="container">
		<section id="content" class="content">
			<?php do_action('cpotheme_before_content'); ?>
			
			<?php if(have_posts()) while(have_posts()): the_post(); ?>
			<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<div class="page-content">
					<?php the_content(); ?>
				</div>
			</div>
			<?php endwhile; ?>

			<?php do_action('cpotheme_after_content'); ?>
		</section>
		<?php get_sidebar(); ?>
		<div class="clear"></div>
	</div>
	
	<div class="container">
		<?php cpotheme_secondary_menu('cpo_portfolio_category', 'menu-portfolio'); ?>
	</div>
	
	<?php if(get_query_var('paged')) $current_page = get_query_var('paged'); else $current_page = 1; ?>	
	<?php $columns = cpotheme_get_option('portfolio_columns'); ?>
	<?php $post_number = $columns * 4; ?>
	<?php 
		$args = array(
			'cat' => 'apartments',
			'post_type' => 'page'
		);
		$query = new WP_Query($args); 
	?>
	<?php if($query->posts): $feature_count = 0; ?>
	<section id="portfolio" class="portfolio">
		<?php cpotheme_grid($query->posts, 'element', 'portfolio', $columns = 4, array('class' => 'column-fit')); ?>
	</section>
	
	<?php wp_reset_postdata(); ?>
	<?php endif; ?>
	
</div>

<?php get_footer(); ?>