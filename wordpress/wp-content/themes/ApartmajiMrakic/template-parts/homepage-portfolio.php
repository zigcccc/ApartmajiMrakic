<?php
	$args = array(
		'cat' => 'apartments',
		'post_type' => 'page'
	);
	$query = new WP_Query($args); 
?>
<?php if($query->posts): $feature_count = 0; ?>
<div id="portfolio" class="portfolio secondary-color-bg">
	<?php cpotheme_block('home_portfolio', 'portfolio-heading section-heading heading dark', 'container'); ?>
	<?php $columns = cpotheme_get_option('portfolio_columns'); if($columns == 0) $columns = 4; ?>
	<?php cpotheme_grid($query->posts, 'element', 'portfolio', $columns, array('class' => 'column-fit')); ?>
</div>
<?php endif; ?>
