<div class="feature">
	<?php $feature_url = get_post_meta(get_the_ID(), 'feature_url', true); ?>
	
	<?php the_post_thumbnail('portfolio'); ?>
	<?php cpotheme_icon(get_post_meta(get_the_ID(), 'feature_icon', true), 'feature-icon primary-color'); ?>
	<h3 class="feature-title">
		<?php if($feature_url != '') echo '<a href="'.esc_url($feature_url).'">'; ?>
		<?php the_title(); ?>
		<?php if($feature_url != '') echo '</a>'; ?>
	</h3>
	<div class="feature-content"><?php the_content(); ?><?php cpotheme_edit(); ?></div>
</div>