<?php $feature_posts = new WP_Query('post_type=cpo_testimonial&posts_per_page=-1&order=ASC&orderby=menu_order'); ?>
<?php if($feature_posts->post_count > 0): $feature_count = 0; ?>
<?php wp_enqueue_script('cpotheme_cycle'); ?>
<div id="testimonials" class="testimonials">
	<div class="container">
		<?php cpotheme_block('home_testimonials', 'section-heading heading'); ?>
		<div class="testimonial-list cycle-slideshow" data-cycle-slides=".testimonial" data-cycle-auto-height="container" data-cycle-pager=".testimonial-pages" data-cycle-pager-template="" data-cycle-timeout="6000" data-cycle-speed="1000" data-cycle-fx="fade">
			<?php $testimonial_images = ''; ?>
			<?php $count = 0; ?>
			<?php while($feature_posts->have_posts()): $feature_posts->the_post(); ?>
			<?php ob_start(); ?>
			<div class="testimonial-page" id="testimonial-<?php echo $count; ?>" data-slide="<?php echo $count; ?>">
				<?php echo get_field('ocena_gosta'); //the_post_thumbnail(array(150,150)); ?>
			</div>
			<?php $testimonial_images .= ob_get_clean(); ?>
			<div class="testimonial" id="testimonial-<?php echo $count; ?>-content" data-slide="<?php echo $count; ?>">
				<div class="column col4 guest-review-container">
					<?php //var_dump(get_field('ocena_gosta')); ?>
					<span class="guest-review"><?php echo get_field('ocena_gosta'); ?></span>
					<?php //the_post_thumbnail('portfolio', array('class' => 'testimonial-image')); ?>
				</div>
				<div class="column col4x3 col-last">
					<div class="testimonial-content">
						<?php the_content(); ?>
					</div>
					<div class="testimonial-author">
						<h4 class="testimonial-name"><?php the_title(); ?></h4>
						<div class="testimonial-description"><?php echo get_post_meta(get_the_ID(), 'testimonial_description', true); ?></div>
						<?php cpotheme_edit(); ?>
					</div>
				</div>
			</div>
			<?php $count++; ?>
			<?php endwhile; ?>
		</div>
		<div class="testimonial-pages">
			<?php echo $testimonial_images; ?>
		</div>					
		<div class='clear'></div>
	</div>
</div>
<?php endif; ?>