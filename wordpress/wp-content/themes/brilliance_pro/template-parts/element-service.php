<div class="service">
	<a class="service-icon primary-color" href="<?php the_permalink(); ?>">
		<?php cpotheme_icon(get_post_meta(get_the_ID(), 'service_icon', true)); ?>
	</a>
	<div class="service-body">
		<h3 class="service-title">
			<a href="<?php echo get_field('povezava_na_stran'); ?>"><?php the_title(); ?></a>
		</h3>
		<div class="service-content">
			<?php the_excerpt(); ?>
		</div>
		<a class="service-readmore" href="<?php echo get_field('povezava_na_stran'); ?>">
			<?php echo get_field('besedilo_povezave'); ?>
		</a>
		<?php cpotheme_edit(); ?>
	</div>
</div>