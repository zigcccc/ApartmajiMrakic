<?php
	$category;
	$currentLanguage = pll_current_language();
	if ($currentLanguage === 'sl') {
		$category = 'apartma';
	}
	else {
		$category = 'apartment';
	}
	$args = array(
		'post_type' => 'page',
		'taxonomy' => 'category',
		'field' => 'slug',
		'term' => $category
	);
	$query = new WP_Query($args); 
?>
<?php if($query->posts): $feature_count = 0; ?>
<div id="portfolio" class="portfolio secondary-color-bg">
	<?php cpotheme_block('home_portfolio', 'portfolio-heading section-heading heading dark', 'container'); ?>
	<?php $columns = cpotheme_get_option('portfolio_columns'); if($columns == 0) $columns = 4; ?>
	<?php cpotheme_grid($query->posts, 'element', 'portfolio', $columns, array('class' => 'column-fit')); ?>
</div>
<?php endif; wp_reset_query(); ?>
