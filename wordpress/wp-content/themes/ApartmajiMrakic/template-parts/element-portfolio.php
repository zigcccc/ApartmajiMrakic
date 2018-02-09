<div class="portfolio-item">
	<a class="portfolio-item-image" href="<?php the_permalink(); ?>">
		<div class="portfolio-item-overlay dark">
			<h3 class="portfolio-item-title">
				<?php the_title(); ?>
			</h3>
			<?php if(has_excerpt()): ?>
			<div class="portfolio-item-description">
				<?php the_excerpt(); ?>
				<?php //cpotheme_edit(); ?>
			</div>
			<?php endif; ?>
		</div>
		<?php the_post_thumbnail('portfolio', array('title' => '')); ?>
	</a>
</div>