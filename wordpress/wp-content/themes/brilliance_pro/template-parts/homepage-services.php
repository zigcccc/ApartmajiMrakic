<?php $query = new WP_Query('post_type=cpo_service&order=ASC&orderby=menu_order&meta_key=service_featured&meta_value=1&numberposts=-1&posts_per_page=-1'); ?>
<?php if($query->posts): $feature_count = 0; ?>
<div id="services" class="services">
	<div class="container">
		<?php cpotheme_block('home_services', 'section-heading heading'); ?>
		<?php $columns = cpotheme_get_option('services_columns'); if($columns == 0) $columns = 3; ?>
		<?php cpotheme_grid($query->posts, 'element', 'service', $columns, array('class' => 'column-narrow column-early')); ?>
	</div>
</div>
<?php endif; ?>
