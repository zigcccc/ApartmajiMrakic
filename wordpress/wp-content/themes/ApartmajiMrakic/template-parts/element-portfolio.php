<div class="portfolio-item">
	<a class="portfolio-item-image" href="<?php the_permalink(); ?>">
		<div class="portfolio-item-overlay dark">
			<h3 class="portfolio-item-title">
				<?php echo get_the_title(get_the_ID()); ?>
			</h3>
			<?php if (has_excerpt()): ?>
			<div class="portfolio-item-description">
				<?php the_excerpt(); ?>
				<?php //cpotheme_edit(); ?>
			</div>
			<?php endif; ?>
		</div>
		<?php $cond = get_theme_mod('show_example_apartments'); ?>
		<?php if (!$cond): ?>
			<?php 
				$image = get_field('apartment_main_image');
				if ($image) {
					echo wp_get_attachment_image( $image['ID'], 'portfolio' );
				}
			?>
		<?php else: ?>
			<?php the_post_thumbnail('portfolio', array('title' => '')); ?>
		<?php endif; ?>
	</a>
</div>